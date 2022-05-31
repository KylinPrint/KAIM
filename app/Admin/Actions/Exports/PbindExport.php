<?php

namespace App\Admin\Actions\Exports;

use Dcat\Admin\Grid;
use App\Admin\Actions\Exports\BaseExport;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Type;
use Dcat\Admin\Grid\Exporters\AbstractExporter;
use Illuminate\Support\Fluent;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use function PHPUnit\Framework\isEmpty;

class PbindExport extends AbstractExporter implements WithMapping, WithHeadings, FromQuery
{
    use Exportable;
    public $fileName = '表格导出测试';
    protected $titles = [];

    public function __construct()
    {
        $this->fileName = $this->fileName.'_'.date('ymd_Hi').'.xlsx';//拼接下载文件名称

        $this->titles = 
        [   
            '产品ID',
            '厂商名称',
            '品牌名称',
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
        // $this->download($this->fileName)->prepare(request())->send();
        $this->queue($this->fileName)->prepare(request())->send();    
        exit;
    }

    protected function getChunkSize()
    {
        $a = config('excel.exports.chunk_size', 1000);
        return $a;
    }


    // public function getQuery()
    // {
    //     $model = $this->getGridModel();
    //     $page = null;
    //     $perPage = null;

    //     // current page
    //     if ($this->scope === Grid\Exporter::SCOPE_CURRENT_PAGE) {
    //         $page = $model->getCurrentPage();
    //         $perPage = $model->getPerPage();
    //     }

    //     $model->usePaginate(false);

    //     if ($page && $this->scope !== Grid\Exporter::SCOPE_SELECTED_ROWS) {
    //         $perPage = $perPage ?: $this->getChunkSize();
    //         $model->forPage($page, $perPage);
    //     }

    //     $grid = new \App\Admin\Rewrites\Grid($this->grid);
    //     $query = $grid->processFilter2($grid);
    //     $model->reset();

    //     return new \App\Admin\Rewrites\Builder($query);

    // }

    // public function query()
    // {
    //     return $this->getQuery();
    // }

    // public function query()
    // {
    //     return Pbind::query();
    // }

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
        $curPbindsArr = Pbind::with('releases','chips','statuses')->find($row['id']);
        
        $curPeripheralArr = Peripheral::with('brands','types','manufactors')->find($row['peripherals_id']);

        $curParentTypeName = Type::where('id',$curPeripheralArr->types->parent)->pluck('name')->first();
        
        if($curPeripheralArr->brands->name && $curPeripheralArr->brands->name_en){
            $brand_name = $curPeripheralArr->brands->name.'('.$curPeripheralArr->brands->name_en.')';
        }else{
            $brand_name = $curPeripheralArr->brands->name?:$curPeripheralArr->brands->name_en;
        }

        $ExportArr['产品ID'] = '';
        $ExportArr['厂商名称'] = $curPeripheralArr->manufactors->name;
        $ExportArr['品牌名称'] = $brand_name;
        $ExportArr['产品名称'] = $curPeripheralArr->name;
        $ExportArr['分类1'] = $curParentTypeName;
        $ExportArr['分类2'] = $curPeripheralArr->types->name;
        $ExportArr['适配系统'] = $curPbindsArr->releases->name;
        $ExportArr['芯片'] = $curPbindsArr->chips->name;
        $ExportArr['体系架构'] = $curPbindsArr->chips->arch;
        $ExportArr['兼容等级'] = $row['class'];
        $ExportArr['测试时间'] = $curPbindsArr->start_time?:'';
        $ExportArr['适配状态'] = $this->getParent($curPbindsArr->statuses->parent);
        $ExportArr['安装包名称'] = $row['solution_name'];
        $ExportArr['下载地址'] = $row['solution'];
        $ExportArr['产品描述'] = $curPeripheralArr->comment?:'';
        $ExportArr['小版本号'] = $row['os_subversion'];
        $ExportArr['备注'] = $row['comment'];
        $ExportArr['是否计划适配产品'] = '';
        $ExportArr['行业'] = $curPeripheralArr->industries;
        $ExportArr['适配类型'] = $curPbindsArr->adaption_type ;


        return $ExportArr;
    }

    public function getParent($sid)
    {
        switch($sid)
        {
            case 1:
                return '未适配';
            case 2:
                return '适配中';
            case 3:
                return '已适配'; 
            case 4:
                return '待验证';
            case 5:
                return '适配暂停';   
        }
    }
}