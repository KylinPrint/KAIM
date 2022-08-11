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
                $creat_stake = 0;
                $audit = [];
                foreach($p_bind_audit_cur_arr as $p_bind_audit_cur){
                    //过滤不带状态审计数据
                    //TODO 怎么能不跑只有创建数据的?

                    //判断状态变更数据
                    if( isset(($p_bind_audit_cur->new_values)['statuses_id']) && 
                        isset(($p_bind_audit_cur->old_values)['statuses_id']) &&
                        ($p_bind_audit_cur->new_values)['statuses_id'] != ($p_bind_audit_cur->old_values)['statuses_id']
                      ){
                        $audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;

                    }
                    //判断创建数据
                    if(isset(($p_bind_audit_cur->new_values)['statuses_id']) && 
                        !isset(($p_bind_audit_cur->old_values)['statuses_id']) &&
                        $creat_stake == 0
                    ){
                        $audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;
                        
                        $creat_stake ++;
                    }
                }
                if(count($audit) > 1){$time_statistics_arr[$p_bind_id] = $audit;}
                unset($audit);
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
        $status_id_4_1 = Status::where('name','适配成果已提交上架软件商店')->pluck('id')->first();
        $status_id_4_end = Status::where('name','适配成果已上架至软件商店')->pluck('id')->first();
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
        foreach($time_statistics_arr as $kk => $vv){
            //判断该数据是否存在终态
            $status_2_end_exist = array_key_exists($status_id_2_end,$vv) ?1:0;
            $status_4_end_exist = array_key_exists($status_id_4_end,$vv) ?1:0;
            $status_5_end_exist = array_key_exists($status_id_5_end,$vv) ?1:0;
            
            $i = 0;

            $status_1_start_time = null;
            $status_1_start_stake = 0;

            $status_2_start_time = null;

            $status_3_start_time = null;
            $status_3_start_stake = 0;

            $status_4_start_time = null;

            $status_5_start_time = null;

            foreach($vv as $k => $v){
                //1.适配复测排期耗时
                if($status_1_start_time && $status_1_start_stake == 0){
                    $time_statistics['status_1_sum'] += $v->diffInHours($status_1_start_time, true);
                    $time_statistics['status_1_count'] ++;
                    $status_1_start_stake ++;
                }
                elseif(!$status_1_start_time && $k == $status_id_1 && $i < count($vv)-1){
                    $status_1_start_time = $v;
                }
                //2.获取流程2中最小时间
                if($status_2_end_exist){
                    if( 
                        $k == $status_id_2_1 ||
                        $k == $status_id_2_2 ||
                        $k == $status_id_2_3 ||
                        $k == $status_id_2_4 ||
                        $k == $status_id_2_5 ||
                        $k == $status_id_2_6  
                    ){
                        if(is_null($status_2_start_time)){
                            $status_2_start_time = $v;
                        }
                        elseif($status_2_start_time && $v->lt($status_2_start_time))
                        {
                            $status_2_start_time = $v;
                        }
                    } 
                }
                //3.系统问题处理耗时
                if($status_3_start_time && $status_3_start_stake == 0){
                    $time_statistics['status_3_sum'] += $v->diffInHours($status_3_start_time, true);
                    $time_statistics['status_3_count'] ++;
                    $status_3_start_stake ++;
                }
                elseif(!$status_3_start_time && $k == $status_id_3 && $i < count($vv)-1){
                    $status_3_start_time = $v;
                }
                //4.上架耗时
                if($status_4_end_exist){
                    if( 
                        $k == $status_id_4_1
                    ){
                        if(is_null($status_4_start_time)){
                            $status_4_start_time = $v;
                        }
                        elseif($status_4_start_time && $v->lt($status_4_start_time))
                        {
                            $status_4_start_time = $v;
                        }
                    } 
                }
                //5.获取流程5中最小时间
                if($status_5_end_exist){
                    if( 
                        $k == $status_id_5_1 ||
                        $k == $status_id_5_2 ||
                        $k == $status_id_5_3 ||
                        $k == $status_id_5_end
                    ){
                        if(is_null($status_5_start_time)){
                            $status_5_start_time = $v;
                        }
                        elseif($status_5_start_time && $v->lt($status_5_start_time))
                        {
                            $status_5_start_time = $v;
                        }
                    } 
                }
            }
            //适配复测耗时
            if($status_2_end_exist && $status_2_start_time){
                $time_statistics['status_2_sum'] += $vv[ $status_id_2_end]->diffInHours($status_2_start_time,true);
                $time_statistics['status_2_count'] ++;
            }
            //
            if($status_4_end_exist && $status_4_start_time){
                $time_statistics['status_4_sum'] += $vv[ $status_id_4_end]->diffInHours($status_4_start_time,true);
                $time_statistics['status_4_count'] ++;
            }
            //证书制作及归档耗时
            if($status_5_end_exist && $status_5_start_time){
                $time_statistics['status_5_sum'] += $vv[ $status_id_5_end]->diffInHours($status_5_start_time,true);
                $time_statistics['status_5_count'] ++;
            }
            unset($status_2_end_exist);unset($status_4_end_exist);unset($status_5_end_exist);
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
                    if(isset(($p_request_audit_cur->new_values)['status']) && !isset(($p_request_audit_cur->old_values)['status'])){
                        $audit[($p_request_audit_cur->new_values)['status']] =  $p_request_audit_cur->updated_at;
                    }
                    if(isset(($p_request_audit_cur->new_values)['status']) && isset(($p_request_audit_cur->old_values)['status'])){
                        $audit[($p_request_audit_cur->new_values)['status']] =  $p_request_audit_cur->updated_at;
                    }   
                }
                //算时间  三个流程耗时
                //TODO 有数据存在第一条审计状态不是'已提交',mgj
                if(isset($audit) && count($audit) > 1 && array_key_first($audit) == '已提交'){
                    $cur_start = current($audit);
                    $processing = 1;$processed = 1;$fail_process = 1;
                    foreach($audit as $k => $v){
                        if($k == '已提交'){continue;} //这句有点蠢,看怎么优化

                        if($k == '处理中' && $processing == 1){
                            $cur_time_statistics['processing_time'] = $v->diffInHours($cur_start, true);
                            $processing = 0;
                        }
                        elseif($k == '已解决' && $processed == 1){
                            $cur_time_statistics['processed_time'] = $v->diffInHours($cur_start, true);
                            $processed = 0;
                        }
                        elseif($k == '无法处理' && $fail_process == 1){
                            $cur_time_statistics['fail_process_time'] = $v->diffInHours($cur_start, true);
                            $fail_process = 0;
                        }
                        //TODO 内存溢出点,看多重循环怎么用yeild
                        $time_statistics_arr[$p_request_id] = $cur_time_statistics; ;
                    }
                }
                unset($audit);
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