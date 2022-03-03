<?php

namespace App\Admin\Actions\Exports;

use App\Models\BarcodeScanner;
use Dcat\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Brand;
use App\Models\Peripheral;
use App\Models\Type;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PHPUnit\Framework\Constraint\IsEmpty;
use function PHPUnit\Framework\isEmpty;

class SolutionMatchExport implements FromCollection, WithHeadings
{

    use Exportable;


    public function __construct($array,$file)
    {
        $time_start = $this->getmicrotime();
        $curMatchArr = $this->WWW($array);
        $time_end = $this->getmicrotime();
        $time = $time_end - $time_start;

        $this->data = $curMatchArr;

        $this->headings = [
            '品类',
            '厂商' ,
            '型号' ,
            '系统版本' ,
            '系统架构' ,
            '匹配型号结果' ,
        ];
        $this->file = $file;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings():array
    {
        return $this->headings;
    }

    public function export()
    {
        $this->download($this->file)->prepare(request())->send();
        exit;
    }

    public function getmicrotime()
    {
        list($usec,$sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

    public function WWW($array)
    {
        $i = 0;
        foreach($array as $curInput)
        {

            $curMatchArr[$i] = 
            [
                '分类1' => $curInput['分类1'],  //软件、外设
                '分类2' => $curInput['分类2'],  //具体分类  打印机、扫描仪
                '厂商' => $curInput['厂商'],
                '型号' => $curInput['型号'],
                '系统版本' => $curInput['系统版本'],
                '芯片' => $curInput['芯片'],
                '匹配型号结果' => '暂无该型号数据',
            ];

            if(empty($curInput['厂商'])){
                $curMatchArr[$i]['匹配型号结果'] = '请核实产品品牌';
                ++$i;
                continue;
            }

            $curBrandId = (Brand::where('name',$curInput['厂商'])->pluck('id')->first())?:(Brand::where('alias',$curInput['厂商'])->pluck('id')->first());
            $curTypeId = Type::where('name',$curInput['分类2'])->pluck('id')->first();
            
            if(empty($curBrandId)){
                $curMatchArr[$i]['匹配型号结果'] = '请核实产品品牌或添加新品牌';
                ++$i;
                continue;
            }

            preg_match('/\d+/',$curInput['型号'],$InputNum);

            if($curInput['分类1'] == '外设')
            {
                $curDeviceModelArr = Peripheral::where([
                    ['model','like','%'.$InputNum[0].'%'],
                    ['brands_id',$curBrandId],
                    ['types_id',$curTypeId],
                ])->pluck('model');

                if($curDeviceModelArr->isEmpty()){++$i;continue;}
                $curMatchArr[$i]['匹配型号结果'] = implode('/',$curDeviceModelArr->toArray());
                ++$i;
            }

            if($curInput['分类1'] == '软件')
            {
                //...
            }
            
        }

        return $curMatchArr;
    }
    
}