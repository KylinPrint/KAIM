<?php

namespace App\Admin\Actions\Imports;

use App\Exceptions\RequiredNotFoundException;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Industry;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\PbindHistory;
use App\Models\Peripheral;
use App\Models\PeripheralIndustry;
use App\Models\Release;
use App\Models\Solution;
use App\Models\Status;
use App\Models\Type;
use Carbon\Carbon;
use Dcat\Admin\Admin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

use function PHPUnit\Framework\isEmpty;

HeadingRowFormatter::default('none');
class PbindImport implements ToCollection, WithHeadingRow, WithValidation
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

            if(!$row['外设型号']){continue;}  //TODO 上边写的异常抛出后不继续执行，待检查

            $curtime = date('Y-m-d H:i:s');
            
            if($row['厂商'] != '')
            {
                $curManufactorId = Manufactor::where('name',$row['厂商'])->pluck('id')->first();
                if(empty($curManufactorId))
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

            $curBrandId = Brand::where('name','like','%'.$row['品牌'].'%')->pluck('id')->first();
            if(empty($curBrandId))
            {
                $brandInsert = 
                [
                    'name' => $row['品牌'],
                    'alias' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                $curBrandId = DB::table('brands')->insertGetId($brandInsert);
            }
            

            $curPeripheralId = Peripheral::where('name',$row['外设型号'])->pluck('id')->first();
            if(empty($curPeripheralId))
            {
                $peripheralInsert = 
                [
                    'name' => $row['外设型号'],
                    'manufactors_id' => isset($curManufactorId) ? $curManufactorId : null,
                    'brands_id' => $curBrandId,
                    'types_id' => Type::where('name',$row['外设类型二'])->pluck('id')->first(),
                    'release_date' => date('Y-m-d',($row['发布日期']-25569)*24*3600),
                    'eosl_date' => date('Y-m-d',($row['服务终止日期']-25569)*24*3600),
                    'industries' => $row['行业分类'],
                    'comment' => $row['外设描述'],
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                $curPeripheralId = DB::table('peripherals')->insertGetId($peripheralInsert);
            }

            $pbindInsert =
            [
                'peripherals_id' => $curPeripheralId,
                'chips_id' => 
                    Chip::where([
                        ['name',$row['芯片']],
                        ['arch','like','%'.$row['架构'].'%']
                    ])->pluck('id')
                    ->first(),
                'releases_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
                'os_subversion' => $row['操作系统小版本']?:'',
                'statuses_id' => Status::where('name',$row['当前细分适配状态'])->pluck('id')->first(),
                'class' => $row['兼容等级'],
                'solution_name' => $row['方案名称'],
                'solution' => $row['方案下载地址'],
                'comment' => $row['备注'],
                'adapt_source' => $row['引入来源'],
                'adapted_before' => $this->bools($row['是否适配过国产CPU']),
                'admin_users_id' => AdminUser::where('name',$row['当前适配状态责任人'])->pluck('id')->first(),
                'adaption_type' => $row['适配类型'],
                'test_type' => $row['测试方式'],
                'kylineco' => $this->bools($row['是否上传生态网站']),
                'appstore' => $this->bools($row['是否上架软件商店']),
                'iscert' => $this->bools($row['是否互认证']),
                'created_at' => $curtime,
                'updated_at' => $curtime,
            ];
            // $pbindInsertUnique = 
            // [
            //     'peripherals_id' => $curPeripheralId,
            //     'chips_id' => Chip::where('name',$row['芯片'])->pluck('id')->first(),
            //     'releases_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
            // ];
            
            $a = Pbind::updateOrCreate(
                [
                    'peripherals_id' => $curPeripheralId,
                    'chips_id' => Chip::where('name',$row['芯片'])->pluck('id')->first(),
                    'releases_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
                ],
                [
                    'os_subversion' => $row['操作系统小版本']?:'',
                    'statuses_id' => $pbindInsert['statuses_id'],
                    'class' => $pbindInsert['class'],
                    'solution_name' => $pbindInsert['solution_name'],
                    'solution' => $pbindInsert['solution'],
                    'comment' => $pbindInsert['comment'],
                    'adapt_source' => $pbindInsert['adapt_source'],
                    'adapted_before' => $pbindInsert['adapted_before'],
                    'admin_users_id' => $pbindInsert['admin_users_id'],
                    'adaption_type' => $pbindInsert['adaption_type'],
                    'test_type' => $pbindInsert['test_type'],
                    'kylineco' => $pbindInsert['kylineco'],
                    'appstore' => $pbindInsert['appstore'],
                    'iscert' => $pbindInsert['iscert'],
                    'updated_at' => $curtime,
                ]
            );


            //暂时未添加更新判断
            $curPbindId = $a->id;
            $b = $a->wasRecentlyCreated;
            $c = $a->wasChanged();

            if($b)
            {
                $pbindhistory = 
                [
                    'pbind_id' => $curPbindId,
                    'status_old' => null,
                    'status_new' => $pbindInsert['statuses_id'],
                    'admin_users_id' => $pbindInsert['admin_users_id'],
                    'comment' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                DB::table('pbind_histories')->insert($pbindhistory);
            }
            if(!$b && $c)
            {
                $curHistoryId = PbindHistory::where('pbind_id',$curPbindId)->orderBy('id','DESC')->pluck('status_new')->first();
                if($curHistoryId != $pbindInsert['statuses_id'])
                {
                    $pbindhistory = 
                [
                    'pbind_id' => $curPbindId,
                    'status_old' => $curHistoryId,
                    'status_new' => $pbindInsert['statuses_id'],
                    'admin_users_id' => $pbindInsert['admin_users_id'],
                    'comment' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                DB::table('pbind_histories')->insert($pbindhistory);
                }
            }
           
        }
        
    }

    public function bools($value)
    {
        return $value =='是' ? 1 : 0;
    }


    public function rules(): array
    {
        return [
            'pbindid' => Rule::unique('pbinds', 'pbindid'), 
        ];
    }

    public function customValidationMessages()
    {
        return [
            'pbindid.unique' => '导入存在重复数据',
        ];
    }
}