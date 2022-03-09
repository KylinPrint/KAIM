<?php

namespace App\Admin\Actions\Exports;

use App\Admin\Actions\Exports\BaseExport;
use App\Models\Sbind;
use Illuminate\Support\Fluent;
use Dcat\Admin\Grid\Exporters\AbstractExporter;
use Dcat\Admin\Http\Displayers\Extensions\Name;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use function PHPUnit\Framework\isEmpty;

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
            '软件名',
            '操作系统',
            '操作系统小版本号',
            '芯片',
            '引入来源',
            '是否适配过国产CPU',
            '当前适配状态',
            '当前适配状态责任人',
            '软件包名',
            '适配方案',
            '兼容等级',
            '适配类型',
            '测试方式',
            '是否上传生态网站',
            '是否上架软件商店',
            '是否互认证',
            '备注',
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

        $curSbindsArr = Sbind::with('releases','chips','softwares','statuses','admin_users')->find($row['id']);

        $ExportArr['产品ID'] = '';
        $ExportArr['软件名'] = $curSbindsArr->softwares->name;
        $ExportArr['操作系统'] = $curSbindsArr->releases->name;
        $ExportArr['操作系统小版本号'] = $row['os_subversion'];
        $ExportArr['芯片'] = $curSbindsArr->chips->name;
        $ExportArr['引入来源'] = $row['引入来源'];
        $ExportArr['是否适配过国产CPU'] = $this->bools($row['adapted_before']);
        $ExportArr['当前适配状态'] = $curSbindsArr->statuses->name;
        $ExportArr['当前适配状态责任人'] = $curSbindsArr->admin_users->username;
        $ExportArr['软件包名'] = $row['softname'];
        $ExportArr['适配方案'] = $row['solution'];
        $ExportArr['兼容等级'] = $row['class'];
        $ExportArr['适配类型'] = $row['adaption_type'];
        $ExportArr['测试方式'] = $row['test_type'];
        $ExportArr['是否上传生态网站'] = $this->bools($row['kylineco']);
        $ExportArr['是否上架软件商店'] = $this->bools($row['appstore']);
        $ExportArr['是否互认证'] = $this->bools($row['iscert']);
        $ExportArr['备注'] = $this->bools($row['comment']);

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