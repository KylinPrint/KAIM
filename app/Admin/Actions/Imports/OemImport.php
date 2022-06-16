<?php

namespace App\Admin\Actions\Imports;

use App\Models\AdminUser;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Oem;
use App\Models\Otype;
use App\Models\Release;
use App\Models\Status;
use Dcat\Admin\Http\JsonResponse;
use Exception;
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
            $curtime = date('Y-m-d H:i:s');
            
            $curManufactorId = Manufactor::where('name',trim($row['厂商']))->pluck('id')->first();
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
            
            if(Otype::where('name',trim($row['整机类型二']))->pluck('id')->first()){
                $OtypeParentID = Otype::where('name',trim($row['整机类型一']))->pluck('id')->first();
                $OtypeID = Otype::where([['parent',$OtypeParentID],['name',trim($row['整机类型二'])]])->pluck('id')->first();
            }else{
                $OtypeID = Otype::where('name',trim($row['整机类型一']))->pluck('id')->first();
            }
            $oemInsert =
            [
                'otypes_id' => $OtypeID,
                'source' => $row['引入来源'],
                'details' => $row['产品描述'],
                'os_subversion' => $row['操作系统小版本'],
                'status_id' => Status::where('name',trim($row['当前细分适配状态']))->pluck('id')->first()?:Status::where('name',trim($row['当前适配状态']))->pluck('id')->first(),
                'admin_user_id' => AdminUser::where('name',trim($row['当前适配状态责任人']))->pluck('id')->first() ,
                'class' => $row['兼容等级'],
                'test_type' => $row['测试方式'],
                'industries' => $row['涉及行业'],
                'kylineco' => $this->bools($row['是否上传生态网站']),
                'iscert' =>  $this->bools($row['是否互认证']), 
                'test_report' => $this->bools($row['是否有测试报告']),
                'certificate_NO' => $row['证书编号'],
                'adaption_type' => $row['适配类型'],
                'industries' => $row['涉及行业'],
                'patch' => $row['补丁包链接'],
                'start_time' => $row['适配开始时间'] ? date('Y-m-d',($row['适配开始时间']-25569)*24*3600):null,
                'complete_time' => $row['适配完成时间'] ? date('Y-m-d',($row['适配完成时间']-25569)*24*3600):null,
                'motherboard' => $row['主板品牌及型号'],
                'gpu' => $row['GPU品牌及型号'],
                'graphic_card' => $row['显卡品牌及型号'],
                'ai_card' => $row['AI加速卡品牌及型号'],
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
                'other_card' => $row['其他板卡配件品牌及型号'],
                'comment' => $row['备注'],
            ];
            $oemInsertUnique = 
            [
                'manufactors_id' => $curManufactorId,
                'name' => $row['整机型号'],
                'releases_id' => Release::where('name',trim($row['操作系统版本']))->pluck('id')->first(),
                'chips_id' => Chip::where('name','like','%'.trim($row['芯片']).'%')->pluck('id')->first(),
            ];
            
            Oem::updateOrCreate($oemInsertUnique,$oemInsert);
           
        }
        
    }

    public function bools($value)
    {
        return $value =='是' ? 1 : 0;
    }

}