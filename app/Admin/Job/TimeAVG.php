<?php

namespace App\Admin\Job;

use App\Models\Pbind;
use App\Models\PRequest;
use App\Models\Status;
use Illuminate\Support\Facades\Cache;

class TimeAVG {

    protected $P_bind_time;
    protected $p_request_time;

    function __invoke(){
        $this->P_bind_time = $this->P_bind_status_time_every_data();
        $this->P_bind_status_time_avg(1);
        $this->P_bind_status_time_avg(7);
        $this->P_bind_status_time_avg(30);
        $this->P_bind_status_time_avg(365);

        $this->p_request_time = $this->P_request_status_time_every_data();
        $this->P_request_status_time_avg(1);
        $this->P_request_status_time_avg(7);
        $this->P_request_status_time_avg(30);
        $this->P_request_status_time_avg(365);
    }

    public function P_bind_status_time_every_data(){

        $p_bind_ids = Pbind::whereNot('statuses_id',null)->pluck('id');

        $now      = now();
        $WeekAgo  = now()->subWeek();
        $MonthAgo = now()->subMonth();
        $YearAgo  = now()->subYear();
        //所有适配数据id
        foreach($p_bind_ids as $p_bind_id){
            //单条适配数据审计
            //TODO 这一条消耗较大,看怎么搞到循环外去
            $p_bind_audit_cur_arr = Pbind::find($p_bind_id)->audits()->get();

            //审计是否至少有两条
            if ($p_bind_audit_cur_arr->count() > 1) {
                //每条审计处理
                $creat_stake = 0;
                $audit = [];$week_audit = [];$month_audit = [];$year_audit = [];
                foreach($p_bind_audit_cur_arr as $p_bind_audit_cur){
                    //过滤不带状态审计数据

                    //判断'状态变更'数据
                    if( isset(($p_bind_audit_cur->new_values)['statuses_id']) && 
                        isset(($p_bind_audit_cur->old_values)['statuses_id']) &&
                        ($p_bind_audit_cur->new_values)['statuses_id'] != ($p_bind_audit_cur->old_values)['statuses_id']
                      ){
                        //将数据放到各个时间段
                        if(($p_bind_audit_cur->updated_at)->between($now,$WeekAgo)){
                            $week_audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;
                        }
                        if(($p_bind_audit_cur->updated_at)->between($now,$MonthAgo)){
                            $month_audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;
                        }
                        if(($p_bind_audit_cur->updated_at)->between($now,$YearAgo)){
                            $year_audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;
                        }
                        $audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;

                    }
                    //判断'创建'数据
                    if(isset(($p_bind_audit_cur->new_values)['statuses_id']) && 
                        !isset(($p_bind_audit_cur->old_values)['statuses_id']) &&
                        $creat_stake == 0
                    ){
                        //将数据放到各个时间段
                        if(($p_bind_audit_cur->updated_at)->between($now,$WeekAgo)){
                            $week_audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;
                        }
                        if(($p_bind_audit_cur->updated_at)->between($now,$MonthAgo)){
                            $month_audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;
                        }
                        if(($p_bind_audit_cur->updated_at)->between($now,$YearAgo)){
                            $year_audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;
                        }
                        $audit[($p_bind_audit_cur->new_values)['statuses_id']] = $p_bind_audit_cur->updated_at;
                        
                        $creat_stake ++;
                    }
                }
                //数据的状态是否存在变更,
                if(count($audit) > 1){$time_statistics_arr[1][$p_bind_id] = $audit;}
                if(count($week_audit) > 1){$time_statistics_arr[7][$p_bind_id] = $audit;}
                if(count($month_audit) > 1){$time_statistics_arr[30][$p_bind_id] = $audit;}
                if(count($year_audit) > 1){$time_statistics_arr[365][$p_bind_id] = $audit;}
                unset($audit);unset($week_audit);unset($month_audit);unset($year_audit);
            }
        }
        return $time_statistics_arr;
    }

    public function P_bind_status_time_avg($limit){
        //涉及状态id
        $time_statistics_arr = $this->P_bind_time[$limit];

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
                //如果存在情况1的开始状态数据,且是开始状态后的第一条数据,记录时间差
                if($status_1_start_time && $status_1_start_stake == 0){
                    $time_statistics['status_1_sum'] += $v->diffInHours($status_1_start_time, true);
                    $time_statistics['status_1_count'] ++;
                    $status_1_start_stake ++;
                }
                //判断是否是情况1的开始状态,且该数据起码有1条以上的状态数据,即发生过状态变更
                elseif(!$status_1_start_time && $k == $status_id_1 && $i < count($vv)-1){
                    $status_1_start_time = $v;
                }
                //2.如果存在情况2的终态数据,获取流程2中最小时间,在该小循环结束后计算与终态的时间差
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
                //3.系统问题处理耗时,处理同1
                if($status_3_start_time && $status_3_start_stake == 0){
                    $time_statistics['status_3_sum'] += $v->diffInHours($status_3_start_time, true);
                    $time_statistics['status_3_count'] ++;
                    $status_3_start_stake ++;
                }
                elseif(!$status_3_start_time && $k == $status_id_3 && $i < count($vv)-1){
                    $status_3_start_time = $v;
                }
                //4.上架耗时,处理同2
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
                //5.获取流程5中最小时间,处理同2
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
            //适配复测耗时,2
            if($status_2_end_exist && $status_2_start_time){
                $time_statistics['status_2_sum'] += $vv[ $status_id_2_end]->diffInHours($status_2_start_time,true);
                $time_statistics['status_2_count'] ++;
            }
            //4
            if($status_4_end_exist && $status_4_start_time){
                $time_statistics['status_4_sum'] += $vv[ $status_id_4_end]->diffInHours($status_4_start_time,true);
                $time_statistics['status_4_count'] ++;
            }
            //证书制作及归档耗时,5
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
            $time_statistics_avg['status_1_avg'] = round($time_statistics['status_1_sum']   / $time_statistics['status_1_count']);
        }
        if($time_statistics['status_2_count']){
            $time_statistics_avg['status_2_avg'] = round($time_statistics['status_2_sum']   / $time_statistics['status_2_count']);
        }
        if($time_statistics['status_3_count']){
            $time_statistics_avg['status_3_avg'] = round($time_statistics['status_3_sum']   / $time_statistics['status_3_count']);
        }
        if($time_statistics['status_4_count']){
            $time_statistics_avg['status_4_avg'] = round($time_statistics['status_4_sum']   / $time_statistics['status_4_count']);
        }
        if($time_statistics['status_5_count']){
            $time_statistics_avg['status_5_avg'] = round($time_statistics['status_5_sum']   / $time_statistics['status_5_count']);
        }
        
        $cache_name = 'p_bind_time_avg_'.$limit;

        Cache::add($cache_name,$time_statistics_avg,now()->addDays(1));
    }

    public function P_request_status_time_every_data(){
        $p_request_ids = PRequest::whereNot('status',null)->pluck('id');

        $now      = now();
        $WeekAgo  = now()->subWeek();
        $MonthAgo = now()->subMonth();
        $YearAgo  = now()->subYear();

        //所有需求数据id
        foreach($p_request_ids as $p_request_id){
            //单条需求数据审计
            //TODO 这一条消耗较大,看怎么搞到循环外去
            $p_request_audit_cur_arr = PRequest::find($p_request_id)->audits()->get();

            $audit = [];
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

                if(isset($audit) && count($audit) > 1 && isset($audit['已提交'])){
                    $cur_start = $audit['已提交'];
                    $processing = 1;$processed = 1;$fail_process = 1;
                    foreach($audit as $k => $v){
                        $cur_time_statistics = ['processing_time' => 0,'processed_time' => 0,'fail_process_time' => 0];
                        $cur_week_statistics = ['processing_time' => 0,'processed_time' => 0,'fail_process_time' => 0];
                        $cur_month_statistics = ['processing_time' => 0,'processed_time' => 0,'fail_process_time' => 0];
                        $cur_year_statistics = ['processing_time' => 0,'processed_time' => 0,'fail_process_time' => 0];
                        if($k == '已提交'){continue;} 

                        if($k == '处理中' && $processing == 1){
                            if($cur_start->between($now,$WeekAgo)){
                                $cur_week_statistics['processing_time'] = $v->diffInHours($cur_start, true);
                            }
                            if($cur_start->between($now,$MonthAgo)){
                                $cur_month_statistics['processing_time'] = $v->diffInHours($cur_start, true);
                            }
                            if($cur_start->between($now,$YearAgo)){
                                $cur_year_statistics['processing_time'] = $v->diffInHours($cur_start, true);
                            }
                            $cur_time_statistics['processing_time'] = $v->diffInHours($cur_start, true);
                            $processing = 0;
                        }
                        elseif($k == '已解决' && $processed == 1){
                            if($cur_start->between($now,$WeekAgo)){
                                $cur_week_statistics['processed_time'] = $v->diffInHours($cur_start, true);
                            }
                            if($cur_start->between($now,$MonthAgo)){
                                $cur_month_statistics['processed_time'] = $v->diffInHours($cur_start, true);
                            }
                            if($cur_start->between($now,$YearAgo)){
                                $cur_year_statistics['processed_time'] = $v->diffInHours($cur_start, true);
                            }
                            $cur_time_statistics['processed_time'] = $v->diffInHours($cur_start, true);
                            $processed = 0;
                        }
                        elseif($k == '无法处理' && $fail_process == 1){
                            if($cur_start->between($now,$WeekAgo)){
                                $cur_week_statistics['fail_process_time'] = $v->diffInHours($cur_start, true);
                            }
                            if($cur_start->between($now,$MonthAgo)){
                                $cur_month_statistics['fail_process_time'] = $v->diffInHours($cur_start, true);
                            }
                            if($cur_start->between($now,$YearAgo)){
                                $cur_year_statistics['fail_process_time'] = $v->diffInHours($cur_start, true);
                            }
                            $cur_time_statistics['fail_process_time'] = $v->diffInHours($cur_start, true);
                            $fail_process = 0;
                        }
                        //TODO 内存溢出点,看多重循环怎么用yeild
                        $time_statistics_arr[1][$p_request_id] = $cur_time_statistics;
                        $time_statistics_arr[7][$p_request_id] = $cur_week_statistics;
                        $time_statistics_arr[30][$p_request_id] = $cur_month_statistics;
                        $time_statistics_arr[365][$p_request_id] = $cur_year_statistics;

                        unset($cur_time_statistics);unset($cur_week_statistics);unset($cur_month_statistics);unset($cur_year_statistics);
                    }
                }
                unset($audit);
            }
        }
        return $time_statistics_arr;
    }

    public function P_request_status_time_avg($limit){
        $time_statistics_arr = ($this->p_request_time)[$limit];
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
            $time_statistics_avg['processing_avg'] = round($time_statistics['processing_sum']/$time_statistics['processing_count']);
        }
        if($time_statistics['processed_count']){
            $time_statistics_avg['processed_avg'] = round($time_statistics['processed_sum']/$time_statistics['processed_count']);
        }
        if($time_statistics['fail_process_count']){
            $time_statistics_avg['fail_process_avg'] = round($time_statistics['fail_process_sum']/$time_statistics['fail_process_count']);
        }

        $cache_name = 'p_request_time_avg_'.$limit;
        Cache::add($cache_name,$time_statistics_avg,now()->addDays(1));
    }

}