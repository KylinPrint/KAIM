<?php

namespace App\Admin\Actions\Exports;

use App\Admin\Actions\Exports\BaseExport;
use App\Models\AdminUser;
use App\Models\PRequest;
use App\Models\PRequestHistory;
use Illuminate\Support\Fluent;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PRequestExport extends BaseExport implements WithMapping, WithHeadings, FromCollection
{
    use Exportable;
    protected $fileName = '表格导出测试';
    protected $titles = [];

    public function __construct()
    {
        $this->fileName = $this->fileName.'_'.date('Y-m-d_H:i:s').'.xlsx';//拼接下载文件名称
        $this->titles = 
        [   
            '需求来源' ,
            '厂商名称' ,
            '品牌名称' ,
            '产品名称' ,
            '外设类型' ,
            '涉及行业' ,
            '操作系统版本' ,
            '操作系统小版本号',
            '芯片' ,
            '项目名称' ,
            '涉及数量' ,
            '项目状态' ,
            '紧急程度' ,
            '厂商联系方式' ,
            '期望完成日期' ,
            '需求提出人' ,
            '需求提出人联系方式' ,
            '处理状态' ,
            '处理人',
            '修改前状态',
            '修改后状态',
            '状态变更说明',
            '更新时间',
            '生态负责人' ,
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
        
        $SbindRow = new Fluent($row);
        $ids = $SbindRow->id;
 

        $CacheArr = array();
        $ExportArr = array();
        $i = 1;

        $curPRquest = PRequest::with('type', 'release', 'chip', 'bd','pbinds')->find($row['id']);
        $curHistoryArr = PRequestHistory::where('p_request_id',$row['id'])->get()->toArray();

        $CacheArr['需求来源'] = $curPRquest->source;
        $CacheArr['厂商名称'] = $curPRquest->manufactor;
        $CacheArr['品牌名称'] = $curPRquest->brand;
        $CacheArr['产品名称'] = $curPRquest->name;
        $CacheArr['外设类型'] = $curPRquest->type->name;
        $CacheArr['涉及行业'] = $curPRquest->industry;
        $CacheArr['操作系统版本'] = $curPRquest->release->name;
        $CacheArr['操作系统小版本号'] = $curPRquest->os_subversion;
        $CacheArr['芯片'] = $curPRquest->chip->name;
        $CacheArr['项目名称'] = $curPRquest->project_name;
        $CacheArr['涉及数量'] = $curPRquest->amount;  
        $CacheArr['项目状态'] = $curPRquest->project_status;
        $CacheArr['紧急程度'] = $curPRquest->level;      
        $CacheArr['厂商联系方式'] = $curPRquest->manufactor_contact;
        $CacheArr['期望完成日期'] = $curPRquest->et;
        $CacheArr['需求提出人'] = $curPRquest->requester_name;
        $CacheArr['需求提出人联系方式'] = $curPRquest->requester_contact;
        $CacheArr['处理状态'] = $curPRquest->status;
        $CacheArr['生态负责人'] = $curPRquest->bd->name;
        $CacheArr['备注'] = $curPRquest->comment;

        if($curHistoryArr)
        {
            foreach($curHistoryArr as $curHistory){
                $ExportArr[$i]['需求来源'] = $CacheArr['需求来源'];
                $ExportArr[$i]['厂商名称'] = $CacheArr['厂商名称'];
                $ExportArr[$i]['品牌名称'] = $CacheArr['品牌名称'];
                $ExportArr[$i]['产品名称'] = $CacheArr['产品名称'];
                $ExportArr[$i]['外设类型'] = $CacheArr['外设类型'];
                $ExportArr[$i]['涉及行业'] = $CacheArr['涉及行业'];
                $ExportArr[$i]['操作系统版本'] = $CacheArr['操作系统版本'];
                $ExportArr[$i]['操作系统小版本号'] = $CacheArr['操作系统小版本号'];
                $ExportArr[$i]['芯片'] = $CacheArr['芯片'];
                $ExportArr[$i]['项目名称'] = $CacheArr['项目名称'];
                $ExportArr[$i]['涉及数量'] = $CacheArr['涉及数量'];  
                $ExportArr[$i]['项目状态'] = $CacheArr['项目状态'];
                $ExportArr[$i]['紧急程度'] = $CacheArr['紧急程度'];      
                $ExportArr[$i]['厂商联系方式'] = $CacheArr['厂商联系方式'];
                $ExportArr[$i]['期望完成日期'] = $CacheArr['期望完成日期'];
                $ExportArr[$i]['需求提出人'] = $CacheArr['需求提出人'];
                $ExportArr[$i]['需求提出人联系方式'] = $CacheArr['需求提出人联系方式'];
                $ExportArr[$i]['处理状态'] = $CacheArr['处理状态'];
    
                $ExportArr[$i]['处理人'] = AdminUser::where('id',$curHistory['operator'])->pluck('name')->first();
                $ExportArr[$i]['修改前状态'] = $curHistory['status_old'];
                $ExportArr[$i]['修改后状态'] = $curHistory['status_new'];
                $ExportArr[$i]['状态变更说明'] = $curHistory['comment'];
                $ExportArr[$i]['更新时间'] = $curHistory['updated_at'];
    
                $ExportArr[$i]['生态负责人'] = $CacheArr['生态负责人'];
                $ExportArr[$i]['备注'] = $CacheArr['备注'];
    
                $i++;
            }
        }
        else{
            $ExportArr[$i]['需求来源'] = $CacheArr['需求来源'];
                $ExportArr[$i]['厂商名称'] = $CacheArr['厂商名称'];
                $ExportArr[$i]['品牌名称'] = $CacheArr['品牌名称'];
                $ExportArr[$i]['产品名称'] = $CacheArr['产品名称'];
                $ExportArr[$i]['外设类型'] = $CacheArr['外设类型'];
                $ExportArr[$i]['涉及行业'] = $CacheArr['涉及行业'];
                $ExportArr[$i]['操作系统版本'] = $CacheArr['操作系统版本'];
                $ExportArr[$i]['操作系统小版本号'] = $CacheArr['操作系统小版本号'];
                $ExportArr[$i]['芯片'] = $CacheArr['芯片'];
                $ExportArr[$i]['项目名称'] = $CacheArr['项目名称'];
                $ExportArr[$i]['涉及数量'] = $CacheArr['涉及数量'];  
                $ExportArr[$i]['项目状态'] = $CacheArr['项目状态'];
                $ExportArr[$i]['紧急程度'] = $CacheArr['紧急程度'];      
                $ExportArr[$i]['厂商联系方式'] = $CacheArr['厂商联系方式'];
                $ExportArr[$i]['期望完成日期'] = $CacheArr['期望完成日期'];
                $ExportArr[$i]['需求提出人'] = $CacheArr['需求提出人'];
                $ExportArr[$i]['需求提出人联系方式'] = $CacheArr['需求提出人联系方式'];
                $ExportArr[$i]['处理状态'] = $CacheArr['处理状态'];
    
                $ExportArr[$i]['处理人'] = '';
                $ExportArr[$i]['修改前状态'] = '';
                $ExportArr[$i]['修改后状态'] = '';
                $ExportArr[$i]['状态变更说明'] = '';
                $ExportArr[$i]['更新时间'] = '';
    
                $ExportArr[$i]['生态负责人'] = $CacheArr['生态负责人'];
                $ExportArr[$i]['备注'] = $CacheArr['备注'];

                $i++;
        }

        $a = $ExportArr;
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