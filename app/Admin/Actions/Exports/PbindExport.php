<?php

namespace App\Admin\Actions\Exports;

use App\Admin\Actions\Exports\BaseExport;
use App\Models\Bind;
use App\Models\Brand;
use App\Models\Industry;
use App\Models\Industry_Tag_Bind;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Project_Tag;
use App\Models\Principle_Tag;
use App\Models\Project_Tag_Bind;
use App\Models\Solution;
use App\Models\Type;
use Illuminate\Support\Fluent;
use Dcat\Admin\Grid\Exporters\AbstractExporter;
use Dcat\Admin\Http\Displayers\Extensions\Name;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use function PHPUnit\Framework\isEmpty;

class PbindExport extends BaseExport implements WithMapping, WithHeadings, FromCollection
{
    use Exportable;
    protected $fileName = '表格导出测试';
    protected $titles = [];

    public function __construct()
    {
        $this->fileName = $this->fileName.'_'.date('ymd_Hi').'.xlsx';//拼接下载文件名称

        $this->titles = 
        [   
            '产品ID',
            '厂商名称',
            '产品名称',
            '分类1',
            '分类2',
            '适配系统',
            '芯片',
            '体系架构',
            '兼容等级',
            '测试时间',
            '适配状态',
            '安装包名称',
            '下载地址',
            '产品描述',
            '小版本号',
            '备注',
            '是否计划适配产品',
            '行业',
            '适配类型',
        ];
        parent::__construct();
    }

    public function export()
    {
        // TODO: Implement export() method.
        $this->download($this->fileName)->prepare(request())->send();
        exit;
    }

    public function collection()
    {
        // TODO: Implement collection() method.

        return collect($this->buildData());
    }

    public function headings(): array
    {
        // TODO: Implement headings() method.
        return $this->titles();
    }

    public function map($row): array
    {

        // TODO: Implement map() method.
        
        $PbindRow = new Fluent($row);
        $ids = $PbindRow->id;

        $ExportArr = array();
        $curPbindsArr = Pbind::with('releases','chips','solutions','statuses')->find($row['id']);

        //这两条查询应该能写成一条
        $curPeripheralArr = Peripheral::with('brands','types')->find($row['peripherals_id']);
        $curPeripheralIndustryArr = 
            Peripheral::with('peripheral_industry')
            ->whereHas('peripheral_industry',function($query) use ($curPeripheralArr){
                $query->with('industries')->where('peripherals_id',$curPeripheralArr->id);
            })->get();
        $curIndustryArr = array();
        foreach($curPeripheralIndustryArr[0]->peripheral_industry as $value)
        {
            $curIndustry = $value->industries->name;
            array_push($curIndustryArr,$curIndustry);
        }
        $curIndustryStr = implode(',',$curIndustryArr);


        $curParentTypeName = Type::where('id',$curPeripheralArr->types->name)->pluck('name')->first();
        preg_match('/[0-9a-zA-Z]+/',$curPbindsArr->releases->name,$curSmallReleas);
        
        $ExportArr['产品ID'] = '';
        $ExportArr['厂商名称'] = $curPeripheralArr->brands->name;
        $ExportArr['产品名称'] = $curPeripheralArr->name;
        $ExportArr['分类1'] = $curParentTypeName;
        $ExportArr['分类2'] = $curPeripheralArr->types->name;
        $ExportArr['适配系统'] = $curPbindsArr->releases->name;
        $ExportArr['芯片'] = $curPbindsArr->chips->name;
        $ExportArr['体系架构'] = $curPbindsArr->chips->arch;
        $ExportArr['兼容等级'] = $curPbindsArr->class?$curPbindsArr->class:'';
        $ExportArr['测试时间'] = '';         //muji
        $ExportArr['适配状态'] = $curPbindsArr->statuses->name;
        $ExportArr['安装包名称'] = $curPbindsArr->solutions->name;
        $ExportArr['下载地址'] = $curPbindsArr->solutions->details;
        $ExportArr['产品描述'] = '';         //peripheral待增字段
        $ExportArr['小版本号'] = $curSmallReleas[0];
        $ExportArr['备注'] = $row['comment']?:'';
        $ExportArr['是否计划适配产品'] = '';  //muji
        $ExportArr['行业'] = $curIndustryStr;
        $ExportArr['适配类型'] = $curPbindsArr->solutions->source;


        return $ExportArr;
    }

    public function getmicrotime()
    {
        list($usec,$sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
}