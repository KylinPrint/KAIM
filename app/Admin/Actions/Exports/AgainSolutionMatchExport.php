<?php

namespace App\Admin\Actions\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Solution;
use App\Models\Printer;
use App\Models\Bind;
use App\Models\Brand;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Type;

use function PHPUnit\Framework\isEmpty;

class AgainSolutionMatchExport implements FromCollection, WithHeadings
{

    use Exportable;


    public function __construct($array,$file)
    {
        $time_start = $this->getmicrotime();
        $curMatchArr = $this->WTF($array);
        $time_end = $this->getmicrotime();
        $time = $time_end - $time_start;

        $this->data = $curMatchArr;

        $this->headings = [
            '厂商' ,
            '型号' ,
            '系统版本' ,
            '系统架构' ,
            '解决方案名' ,
            '解决方案详情' ,
            '适配状态'
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

    // public function export()
    // {
    //     $this->download($this->file)->prepare(request())->send();
    //     exit;
    // }

    public function getmicrotime()
    {
        list($usec,$sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }


    public function WTF($array){
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
                '解决方案名' => '暂无适配方案',
                '解决方案详情' => null,
                '适配状态' => '未适配',
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

            if($curInput['分类1'] == '外设')
            {
                $curPeripheralId = Peripheral::where([
                    ['model','=',$curInput['型号']],
                    ['brands_id',$curBrandId],
                    ['types_id',$curTypeId],])
                ->pluck('id')
                ->first();
    
                if($curPeripheralId)
                {
                    $curPbindArr = Pbind::where([
                        ['peripherals_id','=',$curPeripheralId],
                        ['releases_id',$curInput['系统版本']],
                        ['chips_id',$curInput['芯片']],])
                    ->with('peripherals','releases','chips','solutions','statuses')
                    ->get();
                    
                    //暂未加适配状态
                    if($curPbindArr->count())
                    {
                        foreach($curPbindArr as $curPbind)
                        {
                            if($curMatchArr[$i]['解决方案名'] == '暂无适配方案')
                            {
                                $curMatchArr[$i]['解决方案名'] = $curPbind->solution;
                                $curMatchArr[$i]['解决方案详情'] = $curPbind->solution;
                            }
                            else
                            {
                                $curMatchArr[$i]['解决方案名'] = '/'.$curPbind->solution;
                                $curMatchArr[$i]['解决方案详情'] = '/'.$curPbind->solution;
                            }
                        }
                    }
                }
                else
                {
                    $curMatchArr[$i]['解决方案详情'] = '暂无该型号记录,或核实型号后重新上传';
                }      
                ++$i;
            }
            
            if($curInput['分类1'] == '外设')
            {
                //...
            }

        }
        return $curMatchArr;
    }
}