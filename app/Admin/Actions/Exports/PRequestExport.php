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
            '处理历史',
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
        $curHistoryStr = '处理人 修改前状态 修改后状态 变更时间            状态变更说明';
        $i = 1;

        $curPRquest = PRequest::with('type', 'release', 'chip', 'bd','pbinds')->find($row['id']);
        $curHistoryArr = PRequestHistory::where('p_request_id',$row['id'])->get()->toArray();

        if($curHistoryArr){
            foreach($curHistoryArr as $curHistory){
                
                if($i == 1){
                    if(!$curHistory['status_old']){
                        $status_old = '      ';
                    }else{
                        $status_old = $curHistory['status_old'];
                    }
                    $curHistoryStr = $curHistoryStr.chr(10).$curHistory['user_name'].' '.$status_old.'     '.$curHistory['status_new'].'    '.$curHistory['updated_at'].' '.$curHistory['comment'];
                }else{
                    $curHistoryStr = $curHistoryStr.chr(10).$curHistory['user_name'].' '.$curHistory['status_old'].'     '.$curHistory['status_new'].'    '.$curHistory['updated_at'].' '.$curHistory['comment'];
                }
                $i ++;
            } 
        }

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
        $CacheArr['处理历史'] = $curHistoryStr;
        $CacheArr['生态负责人'] = $curPRquest->bd->name;
        $CacheArr['备注'] = $curPRquest->comment;

        
        return $CacheArr;
    }

    public function getmicrotime()
    {
        list($usec,$sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

}