<?php

namespace App\Admin\Job;

use App\Models\Pbind;
use App\Models\PRequest;
use App\Models\Status;
use Illuminate\Support\Facades\Cache;

class TimeAVG {
    function __invoke(){
        $this->P_bind_status_time_avg();
        $this->P_request_status_time_avg();
    }

    public function P_bind_status_time_every_data(){

        $p_bind_ids = Pbind::whereNot('statuses_id',null)->pluck('id');
        //所有适配数据id
        foreach($p_bind_ids as $p_bind_id){
            //单条适配数据审计
            //TODO 这一条消耗较大,看怎么搞到循环外去
            $p_bind_audit_cur_arr = Pbind::find($p_bind_id)->audits()->get();

            //审计是否为空
            if ($p_bind_audit_cur_arr->count()) {
                //每条审计处理
                $arr = [];
                foreach($p_bind_audit_cur_arr as $p_bind_audit_cur){
                    //过滤不带状态审计数据
                    if(isset(($p_bind_audit_cur->new_values)['statuses_id']) && isset(($p_bind_audit_cur->old_values)['statuses_id'])){
                        $audit['new_value_status_id'] = ($p_bind_audit_cur->new_values)['statuses_id'];
                        $audit['old_value_status_id'] = ($p_bind_audit_cur->old_values)['statuses_id'];
                        $audit['updated_at'] = $p_bind_audit_cur->updated_at;

                        $arr[] = $audit;
                    }
                    elseif(isset(($p_bind_audit_cur->new_values)['statuses_id']) && !isset(($p_bind_audit_cur->old_values)['statuses_id'])){
                        $audit['new_value_status_id'] = ($p_bind_audit_cur->new_values)['statuses_id'];
                        $audit['old_value_status_id'] = '';
                        $audit['updated_at'] = $p_bind_audit_cur->updated_at;

                        $arr[] = $audit;
                    }
 
                }
                //算时间  各个状态耗时
                if(isset($arr) && count($arr) > 1){
                    //把这条数据的第一条状态数据拿出来,按道理即是创建时的数据
                    $cur_status = [
                        'status'     => $arr[0]['new_value_status_id'],
                        'updated_at' => $arr[0]['updated_at']
                    ];
                    for($i = 0; $i <= count($arr) - 1; $i++){
                        //如果这条状态数据的旧状态和当前数据的新状态一直,且自身新旧状态有变化,判断为发生了状态变化
                        if( 
                            $cur_status['status'] == $arr[$i]['old_value_status_id'] &&
                            $arr[$i]['old_value_status_id'] != $arr[$i]['new_value_status_id']
                        ){
                            $cur_time_statistics['status_id'] = $cur_status['status'];
                            $cur_time_statistics['time'] = $arr[$i]['updated_at']->diffInHours($cur_status['updated_at'], true);
                            $cur_status = [
                                'status'     => $arr[$i]['new_value_status_id'],
                                'updated_at' => $arr[$i]['updated_at']
                            ];  
                            //TODO 内存溢出点,看多重循环怎么用yeild                     
                            $time_statistics_arr[$p_bind_id][] = $cur_time_statistics;   

                        }
                    }
                }
            }
        }
        return $time_statistics_arr;
    }

    public function P_bind_status_time_avg(){
        //涉及状态id
        $time_statistics_arr = $this->P_bind_status_time_every_data();

        $status_id_1 = Status::where('name','排期待适配测试')->pluck('id')->first();
        $status_id_2_1 = Status::where('name','适配测试中—远程测试')->pluck('id')->first();
        $status_id_2_2 = Status::where('name','适配测试中—出差测试')->pluck('id')->first();
        $status_id_2_3 = Status::where('name','适配测试中—视频复测')->pluck('id')->first();
        $status_id_2_4 = Status::where('name','适配测试中—麒麟内测')->pluck('id')->first();
        $status_id_2_5 = Status::where('name','适配问题定位分析中')->pluck('id')->first();
        $status_id_2_6 = Status::where('name','问题复测中')->pluck('id')->first();
        $status_id_2_end = Status::where('name','测试报告确认中')->pluck('id')->first();
        $status_id_3 = Status::where('name','问题修复中—系统问题')->pluck('id')->first();
        $status_id_4_1_end = Status::where('name','适配成果已提交上架软件商店')->pluck('id')->first();
        $status_id_4_2_end = Status::where('name','适配成果已上架至软件商店')->pluck('id')->first();
        $status_id_5_1 = Status::where('name','互认证证书制作中')->pluck('id')->first();
        $status_id_5_2 = Status::where('name','证书邮寄中（后期）')->pluck('id')->first();
        $status_id_5_3 = Status::where('name','证书归档中（后期）')->pluck('id')->first();
        $status_id_5_end = Status::where('name','证书已归档')->pluck('id')->first();

        $time_statistics = 
        [
            'status_1_sum'    => 0 ,'status_1_count'   => 0,
            'status_2_sum'    => 0 ,'status_2_count'   => 0,
            'status_3_sum'    => 0 ,'status_3_count'   => 0,
            'status_4_sum'    => 0 ,'status_4_count'   => 0,
            'status_5_sum'    => 0 ,'status_5_count'   => 0,
        ];
        //TODO 这个双层循环待优化
        foreach($time_statistics_arr as $vv){
            //判断该数据是否存在情况2或5的终态
            $status_2_end_exist = in_array($status_id_2_end,array_column($vv,'status_id'))?1:0;
            $status_5_end_exist = in_array($status_id_5_end,array_column($vv,'status_id'))?1:0;
            foreach($vv as $v){
                //1.适配复测排期耗时
                if($v['status_id'] == $status_id_1){
                    $time_statistics['status_1_sum'] += $v['time'];
                    ++ $time_statistics['status_1_count'];
                }
                //2.适配复测耗时
                if($status_2_end_exist){
                    if( 
                        $v['status_id'] == $status_id_2_1 ||
                        $v['status_id'] == $status_id_2_2 ||
                        $v['status_id'] == $status_id_2_3 ||
                        $v['status_id'] == $status_id_2_4 ||
                        $v['status_id'] == $status_id_2_5 ||
                        $v['status_id'] == $status_id_2_6 ||
                        $v['status_id'] == $status_id_2_end 
                    ){
                        $time_statistics['status_2_sum'] += $v['time'];
                        if($v['status_id'] == $status_id_2_end){
                            ++ $time_statistics['status_2_count'];
                        }
                    } 
                }
                //3.系统问题处理耗时
                if($v['status_id'] == $status_id_3){
                    $time_statistics['status_3_sum'] += $v['time'];
                    ++ $time_statistics['status_3_count'];
                }
                //4.上架耗时
                if($v['status_id'] == $status_id_4_1_end || $v['status_id'] == $status_id_4_2_end){
                    $time_statistics['status_4_sum'] += $v['time'];
                    ++ $time_statistics['status_4_count'];
                }
                //5.证书制作及归档耗时
                if($status_5_end_exist){
                    if( 
                        $v['status_id'] == $status_id_5_1 ||
                        $v['status_id'] == $status_id_5_2 ||
                        $v['status_id'] == $status_id_5_3 ||
                        $v['status_id'] == $status_id_5_end
                    ){
                        $time_statistics['status_5_sum'] += $v['time'];
                        if($v['status_id'] == $status_id_5_end){
                            ++ $time_statistics['status_5_count'];
                        }
                    } 
                }
            }
            unset($status_2_end_exist);unset($status_5_end_exist);
        }

        //算平均耗时
        $time_statistics_avg = [
            'status_1_avg' => 0 ,
            'status_2_avg' => 0 ,
            'status_3_avg' => 0 ,
            'status_4_avg' => 0 ,
            'status_5_avg' => 0
        ];
        if($time_statistics['status_1_count']){
            $time_statistics_avg['status_1_avg'] = $time_statistics['status_1_sum']   / $time_statistics['status_1_count'];
        }
        if($time_statistics['status_2_count']){
            $time_statistics_avg['status_2_avg'] = $time_statistics['status_2_sum']   / $time_statistics['status_2_count'];
        }
        if($time_statistics['status_3_count']){
            $time_statistics_avg['status_3_avg'] = $time_statistics['status_3_sum']   / $time_statistics['status_3_count'];
        }
        if($time_statistics['status_4_count']){
            $time_statistics_avg['status_4_avg'] = $time_statistics['status_4_sum']   / $time_statistics['status_4_count'];
        }
        if($time_statistics['status_5_count']){
            $time_statistics_avg['status_5_avg'] = $time_statistics['status_5_sum']   / $time_statistics['status_5_count'];
        }

        Cache::add('p_bind_time_avg',$time_statistics_avg,now()->addDays(1));
    }

    public function P_request_status_time_every_data(){
        $p_request_ids = PRequest::whereNot('status',null)->pluck('id');
        //所有需求数据id
        foreach($p_request_ids as $p_request_id){
            //单条需求数据审计
            //TODO 这一条消耗较大,看怎么搞到循环外去
            $p_request_audit_cur_arr = PRequest::find($p_request_id)->audits()->get();

            if ($p_request_audit_cur_arr->count()) {
                foreach($p_request_audit_cur_arr as $p_request_audit_cur){
                    //过滤不带状态审计数据
                    if(isset(($p_request_audit_cur->new_values)['status']) && isset(($p_request_audit_cur->old_values)['status'])){
                        $audit['new_value_status'] = ($p_request_audit_cur->new_values)['status'];
                        $audit['old_value_status'] = ($p_request_audit_cur->old_values)['status'];
                        $audit['updated_at'] = $p_request_audit_cur->updated_at;

                        $arr[] = $audit;
                    }
                    if(isset(($p_request_audit_cur->new_values)['status']) && !isset(($p_request_audit_cur->old_values)['status'])){
                        $audit['new_value_status'] = ($p_request_audit_cur->new_values)['status'];
                        $audit['old_value_status'] = '';
                        $audit['updated_at'] = $p_request_audit_cur->updated_at;

                        $arr[] = $audit;
                    }

                }
                //算时间  三个流程耗时
                //TODO 有数据存在第一条审计状态不是'已提交',mgj
                if(isset($arr) && count($arr) > 1 && $arr[0]['new_value_status'] == '已提交'){
                    $cur_start = $arr[0]['updated_at'];
                    $processing = 1;$processed = 1;$fail_process = 1;
                    foreach($arr as $v){
                        if($v['new_value_status'] == '已提交'){continue;} //这句有点蠢,看怎么优化

                        if($v['new_value_status'] == '处理中' && $processing == 1){
                            $cur_time_statistics['processing_time'] = $v['updated_at']->diffInHours($cur_start, true);
                            $processing = 0;
                        }
                        elseif($v['new_value_status'] == '已解决' && $processed == 1){
                            $cur_time_statistics['processed_time'] = $v['updated_at']->diffInHours($cur_start, true);
                            $processed = 0;
                        }
                        elseif($v['new_value_status'] == '无法处理' && $fail_process == 1){
                            $cur_time_statistics['fail_process_time'] = $v['updated_at']->diffInHours($cur_start, true);
                            $fail_process = 0;
                        }
                        //TODO 内存溢出点,看多重循环怎么用yeild
                        $time_statistics_arr[$p_request_id] = $cur_time_statistics; ;
                    }
                }
                unset($arr);
            }
        }
        return $time_statistics_arr;
    }

    public function P_request_status_time_avg(){
        $time_statistics_arr = $this->P_request_status_time_every_data();
        //求平均值
        $time_statistics = 
        [
            'processing_sum'    => 0 ,'processing_count'   => 0,
            'processed_sum'     => 0 ,'processed_count'    => 0,
            'fail_process_sum'  => 0 ,'fail_process_count' => 0,
        ];
        foreach($time_statistics_arr as $value){
            if(isset($value['processing_time'])){
                $time_statistics['processing_sum'] += $value['processing_time'];
                ++$time_statistics['processing_count'];
            }
            if(isset($value['processed_time'])){
                $time_statistics['processed_sum'] += $value['processed_time'];
                ++$time_statistics['processed_count'];
            }
            if(isset($value['fail_process_time'])){
                $time_statistics['fail_process_sum'] += $value['fail_process_time'];
                ++$time_statistics['fail_process_count'];
            }
        }
        $time_statistics_avg = ['processing_avg' => 0 , 'processed_avg' => 0 ,'fail_process_avg' => 0];
        if($time_statistics['processing_count']){
            $time_statistics_avg['processing_avg'] = $time_statistics['processing_sum']   / $time_statistics['processing_count'];
        }
        if($time_statistics['processed_count']){
            $time_statistics_avg['processed_avg'] = $time_statistics['processed_sum']    / $time_statistics['processed_count'];
        }
        if($time_statistics['fail_process_count']){
            $time_statistics_avg['fail_process_avg'] = $time_statistics['fail_process_sum'] / $time_statistics['fail_process_count'];
        }

        Cache::add('p_request_time_avg',$time_statistics_avg,now()->addDays(1));
    }

}