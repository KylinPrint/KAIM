<?php

namespace App\Admin\Actions\Exports;

use App\Admin\Actions\Exports\BaseExport;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\Type;
use Illuminate\Support\Fluent;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SbindExport extends BaseExport implements WithMapping, WithHeadings, FromCollection
{
    use Exportable;
    protected $fileName = '表格导出测试';
    protected $titles = [];

    public function __construct()
    {
        $this->fileName = $this->fileName.'_'.date('Y-M-D_H:i:s').'.xlsx';//拼接下载文件名称
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
        
        $SbindRow = new Fluent($row);
        $ids = $SbindRow->id;
 

        $ExportArr = array();

        $curSbindsArr = Sbind::with('releases','chips','softwares','statuses')->find($row['id']);
        $curSoftwareArr = Software::with('manufactors','stypes')->find($row['softwares_id']);

        $curParentTypeName = Type::where('id',$curSoftwareArr->stypes->parent)->pluck('name')->first();

        $ExportArr['产品ID'] = '';
        $ExportArr['厂商名称'] = $curSoftwareArr->manufactors->name;
        $ExportArr['产品名称'] = $curSbindsArr->softwares->name;
        $ExportArr['分类1'] = $curParentTypeName;
        $ExportArr['分类2'] = $curSoftwareArr->stypes->name;
        $ExportArr['适配系统'] = $curSbindsArr->releases->name;
        $ExportArr['芯片'] = $curSbindsArr->chips->name;
        $ExportArr['体系架构'] = $curSbindsArr->chips->arch;
        $ExportArr['兼容等级'] = $curSbindsArr->class?:'';
        $ExportArr['测试时间'] = '';  //暂无字段
        $ExportArr['适配状态'] = $curSbindsArr->statuses->name;
        $ExportArr['安装包名称'] = $row['softname'];
        $ExportArr['下载地址'] = $row['solution'];
        $ExportArr['产品描述'] = $curSoftwareArr->comment;
        $ExportArr['小版本号'] = $row['os_subversion'];
        $ExportArr['备注'] = $row['comment'];
        $ExportArr['是否计划适配产品'] = '';  //暂无字段
        $ExportArr['行业'] = $curSoftwareArr->industries;
        $ExportArr['适配类型'] = $row['adaption_type'];

        return $ExportArr;
    }

    public function bools($value){
        return $value == '是'?1:0;
    }

    public function getmicrotime()
    {
        list($usec,$sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
}