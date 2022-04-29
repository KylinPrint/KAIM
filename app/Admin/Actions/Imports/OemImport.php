<?php

namespace App\Admin\Actions\Imports;

use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Oem;
use App\Models\OemHistory;
use App\Models\Release;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;


HeadingRowFormatter::default('none');
class OemImport implements ToCollection, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function collection(Collection $rows)
    {
        set_time_limit(0);

        // unset($rows[0]);  //去掉表头


        foreach($rows as $key => $row)
        {
            // if
            // ( 
            //     !($row['厂商']&&
            //     $row['品牌']&&
            //     $row['外设型号']&&
            //     $row['外设类型一']&&
            //     $row['外设类型二']&&
            //     $row['行业分类']&&
            //     $row['操作系统版本']&&
            //     $row['芯片']&&
            //     $row['架构']&&
            //     $row['引入来源']&&
            //     $row['当前适配状态']&&
            //     $row['当前细分适配状态']&&
            //     $row['当前适配状态责任人']&&
            //     $row['是否上传生态网站']&&
            //     $row['是否上架软件商店']&&
            //     $row['是否互认证'])
            // ){
            //     throw new RequiredNotFoundException($key);
            // }

            if(!$row['整机型号']){continue;}  //TODO 上边写的异常抛出后不继续执行，待检查

            $curtime = date('Y-m-d H:i:s');
            
            if($row['厂商'] != '')
            {
                $curManufactorId = Manufactor::where('name',$row['厂商'])->pluck('id')->first();
                if(!$curManufactorId)
                {
                    $manufactorInsert = 
                    [
                        'name' => $row['厂商'],
                        'isconnected' => '0',
                        'created_at' => $curtime,
                        'updated_at' => $curtime,
                    ];
                    $curManufactorId = DB::table('manufactors')->insertGetId($manufactorInsert);
                }
            }

            $oemInsert =
            [
                'type_id' => Type::where('name',$row['整机类型二'])->pluck('id')->first(),
                'source' => $row['引入来源'],
                'details' => $row['产品描述'],
                'os_subversion' => $row['操作系统小版本号'],
                'status_id' => Status::where('name',$row['当前适配状态'])->pluck('id')->first(),
                'user_name' => $row['当前适配状态责任人'],
                'class' => $row['兼容等级'],
                'test_type' => $row['测试方式'],
                'kylineco' => $this->bools($row['是否上传生态网站']),
                'iscert' =>  $this->bools($row['是否互认证']), 
                'test_report' => $this->bools($row['是否有测试报告']),
                'certificate_NO' => $row['证书编号'],
                'adaption_type' => $row['适配类型'],
                'industries' => $row['涉及行业'],
                'patch' => $row['补丁包连接'],
                'start_time' => $row['适配开始时间'] ? date('Y-m-d',($row['适配开始时间']-25569)*24*3600):null,
                'complete_time' => $row['适配完成时间'] ? date('Y-m-d',($row['适配完成时间']-25569)*24*3600):null,
                'motherboard' => $row['主板品牌及型号'],
                'gpu' => $row['gpu品牌及型号'],
                'graphic_card' => $row['显卡品牌及型号'],
                'ai_card' => $row['Ai加速卡品牌及型号'],
                'network' => $row['网卡品牌及型号'],
                'memory' => $row['内存品牌及型号'],
                'raid' => $row['RAID卡品牌及型号'],
                'hba' => $row['HBA卡品牌及型号'],
                'hard_disk' => $row['硬盘品牌及型号'],
                'firmware' => $row['固件品牌及型号'],
                'sound_card' => $row['声卡品牌及型号'],
                'parallel' => $row['并口卡品牌及型号'],
                'serial' => $row['串口卡品牌及型号'],
                'isolation_card' => $row['隔离卡品牌及型号'],
                'other_card' => $row['其它板卡品牌及型号'],
                'comment' => $row['备注'],
            ];
            $oemInsertUnique = 
            [
                'manufactor_id' => $curManufactorId,
                'name' => $row['整机型号'],
                'release_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
                'chip_id' => Chip::where('name','like','%'.$row['芯片'].'%')->pluck('id')->first(),
            ];
            
            $a = Oem::updateOrCreate($oemInsertUnique,$oemInsert);
     
            $curOemId = $a->id;
            $b = $a->wasRecentlyCreated;
            $c = $a->wasChanged();

            //新增数据
            if($b)
            {
                $oemhistory = 
                [
                    'oem_id' => $curOemId,
                    'status_old' => null,
                    'status_new' => $oemInsert['statuses_id'],
                    'user_name' => $oemInsert['user_name'],
                    'comment' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                DB::table('oem_histories')->insert($oemhistory);
            }

            //更新数据
            if(!$b && $c)
            {
                $curHistoryId = OemHistory::where('oem_id',$curOemId)->orderBy('id','DESC')->pluck('status_new')->first();
                
                $oemhistory = 
                [
                'oem_id' => $curOemId,
                'status_old' => $curHistoryId,
                'status_new' => $oemInsert['statuses_id'],
                'user_name' => $oemInsert['user_name'],
                'comment' => null,
                'created_at' => $curtime,
                'updated_at' => $curtime,
                ];

                DB::table('oem_histories')->insert($oemhistory);
                
            }
           
        }
        
    }

    public function bools($value)
    {
        return $value =='是' ? 1 : 0;
    }

}