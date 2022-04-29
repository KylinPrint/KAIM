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
            '整机类型一',
            '整机类型二',
            '引入来源',
            '产品描述',
            '操作系统版本',
            '操作系统小版本',
            '芯片',
            '架构',
            '当前适配状态',
            '当前细分适配状态',
            '当前适配状态责任人',
            '兼容等级',
            '测试方式',
            '是否上传生态网站',
            '是否互认证',
            '补丁包链接',
            '适配开始时间',
            '适配完成时间',
            '主板品牌及型号',
            'GPU品牌及型号',
            '显卡品牌及型号',
            'AI加速卡品牌及型号',
            '网卡品牌及型号',
            '内存品牌及型号',
            'RAID卡品牌及型号',
            'HBA卡品牌及型号',
            '硬盘品牌及型号',
            '固件品牌及型号',
            '声卡品牌及型号',
            '并口卡品牌及型号',
            '串口卡品牌及型号',
            '隔离卡品牌及型号',
            '其他板卡配件品牌及型号',
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
        
        $OemRow = new Fluent($row);
        $ids = $OemRow->id;

        $ExportArr = array();
        $curOemArr = Oem::with('releases','chips','manufactors','statuses','types')->find($row['id']);

        $curParentTypeName = Type::where('id',$curOemArr->types->parent)->pluck('name')->first();

        
        $ExportArr['产品ID'] = '';
        $ExportArr['厂商名称'] = $curOemArr->manufactor->name;
        $ExportArr['产品名称'] = $curOemArr->name;
        $ExportArr['整机类型一'] = $curParentTypeName;
        $ExportArr['整机类型二'] = $curOemArr->types->name;
        $ExportArr['引入来源'] = $row['source'];
        $ExportArr['产品描述'] = $row['details']?:'';
        $ExportArr['操作系统版本'] = $curOemArr->releases->name;
        $ExportArr['操作系统小版本'] = $row['os_subversion'];
        $ExportArr['芯片'] = $curOemArr->chips->name;
        $curOemArr['架构'] = $curOemArr->chips->arch;
        $curOemArr['当前适配状态'] = $this->getParent($curOemArr->statuses->parent);
        $curOemArr['当前细分适配状态'] = $curOemArr->statuses->name;
        $curOemArr['当前适配状态责任人'] = $row['user_name'];
        $curOemArr['兼容等级'] = $row['class'];
        $curOemArr['测试方式'] = $row['test_type'];
        $curOemArr['是否上传生态网站'] = $this->bools($row['kylineco']);
        $curOemArr['是否互认证'] = $this->bools($row['iscert']);
        $curOemArr['补丁包连接'] = $row['patch']?:'';
        $curOemArr['适配开始时间'] = $row['start_time'];
        $curOemArr['适配完成时间'] = $row['complete_time'];
        $curOemArr['主板品牌及型号'] = $row['motherboard'];
        $curOemArr['GPU品牌及型号'] = $row['gpu'];
        $curOemArr['显卡品牌及型号'] = $row['graphic_card'];
        $curOemArr['AI加速卡品牌及型号'] = $row['ai_card'];
        $curOemArr['网卡品牌及型号'] = $row['network'];
        $curOemArr['内存品牌及型号'] = $row['memory'];
        $curOemArr['RAID卡品牌及型号'] = $row['raid'];
        $curOemArr['HBA卡品牌及型号'] = $row['hba'];
        $curOemArr['硬盘品牌及型号'] = $row['hard_disk'];
        $curOemArr['固件品牌及型号'] = $row['firmware'];
        $curOemArr['声卡品牌及型号'] = $row['sound_card'];
        $curOemArr['并口卡品牌及型号'] = $row['parallel'];
        $curOemArr['串口卡品牌及型号'] = $row['serial'];
        $curOemArr['隔离卡品牌及型号'] = $row['isolation_card'];
        $curOemArr['其他板卡配件品牌及型号'] = $row['other_card'];
        $curOemArr['备注'] = $row['comment'];

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