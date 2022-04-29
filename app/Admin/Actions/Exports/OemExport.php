<?php

namespace App\Admin\Actions\Exports;

use App\Admin\Actions\Exports\BaseExport;
use App\Models\Oem;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Fluent;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use function PHPUnit\Framework\isEmpty;

class OemExport extends BaseExport implements WithMapping, WithHeadings, FromCollection
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
            '芯片品牌',
            '分类一',
            '分类二',
            '分类三',
            '产品行业',
            '适配系统',
            '适配类型',
            '体系结构',
            '兼容等级',
            '适配状态',
            '安装包名称',
            '下载地址',
            '产品描述',
            '小版本号',
            '备注',
            '证书编号',
            '申请适配时间',
            '适配完成时间',
            '上传时间',
            '是否上传测试报告',
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
        
        $OemRow = new Fluent($row);
        $ids = $OemRow->id;

        $ExportArr = array();
        $curOemArr = Oem::with('releases','chips','manufactors','statuses','types')->find($row['id']);

        $curParentTypeName = Type::where('id',$curOemArr->types->parent)->pluck('name')->first();

        $ExportArr['产品ID'] = '';
        $ExportArr['厂商名称'] = $curOemArr->manufactor->name;
        $ExportArr['产品名称'] = $row['name'];
        $ExportArr['芯片品牌'] = $curOemArr->chips->name;
        $ExportArr['分类一'] = '整机';
        $ExportArr['分类二'] = $curParentTypeName;
        $ExportArr['分类三'] = $curOemArr->types->name;
        $ExportArr['产品行业'] = $row['industries'];
        $ExportArr['适配系统'] = $curOemArr->releases->name;
        $ExportArr['适配类型'] = ''; //muji
        $ExportArr['体系结构'] =  $curOemArr->chips->arch;;
        $ExportArr['兼容等级'] =  $row['class'];
        $ExportArr['适配状态'] = $this->getParent($curOemArr->statuses->parent); ;
        $ExportArr['安装包名称'] = '';
        $ExportArr['下载地址'] = '';
        $ExportArr['产品描述'] = $row['details']?:'';
        $ExportArr['小版本号'] = $row['os_subversion'];
        $ExportArr['备注'] =  $row['comment'];
        $ExportArr['证书编号'] = $row['certificate_NO'];
        $ExportArr['申请适配时间'] = $row['start_time'];
        $ExportArr['适配完成时间'] = $row['complete_time'];
        $ExportArr['上传时间'] = $row['created_at'];
        $ExportArr['是否上传测试报告'] =$this->bools($row['test_report']);

        return $ExportArr;
    }

    public function bools($value){
        switch($value)
        {
            case 1:
                return '是';
            case 0:
                return '否';
            default :
                return '';
        }
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