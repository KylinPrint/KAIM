<?php

namespace App\Admin\Actions\Imports;

use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\PbindHistory;
use App\Models\Peripheral;
use App\Models\Release;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
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

            $brand_name = '';
            $brand_name_en = '';

            preg_match('/[\x7f-\xff]+/',$row['品牌'],$input_brand_name);
            preg_match('/[a-zA-Z]+/',$row['品牌'],$input_brand_name_en);

            if(isEmpty($input_brand_name[0]) && empty($input_brand_name_en[0]))
            {
                $brand_name = $input_brand_name[0];
                $brand_name_en = '';
                $curBrandId = Brand::where('name','like','%'.$brand_name.'%')->pluck('id')->first();
            }
            elseif(empty($input_brand_name[0]) && isEmpty($input_brand_name_en[0]))
            {
                $brand_name = '';
                $brand_name_en = $input_brand_name_en[0];
                $curBrandId = Brand::where('name_en','like','%'.$brand_name_en.'%')->pluck('id')->first();
            }
            elseif(isEmpty($input_brand_name[0]) && isEmpty($input_brand_name_en[0]))
            {
                $brand_name = $input_brand_name[0];
                $brand_name_en = $input_brand_name_en[0];
                $curBrandId = Brand::where([
                    ['name','like','%'.$brand_name.'%'],
                    ['name_en','like','%'.$brand_name_en.'%']
                ])->pluck('id')->first();
            }

            
            if(empty($curBrandId))
            {
                $brandInsert = 
                [
                    'name' => $brand_name,
                    'name_en' => $brand_name_en,
                    'alias' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                $curBrandId = DB::table('brands')->insertGetId($brandInsert);
            }

            $curPeripheralId = Peripheral::where('name',$row['外设型号'])->pluck('id')->first();
            if(empty($curPeripheralId))
            {
                $parentID = Type::where('name',$row['外设类型一'])->pluck('id')->first();
                $peripheralInsert = 
                [
                    'name' => $row['外设型号'],
                    'manufactors_id' => isset($curManufactorId) ? $curManufactorId : null,
                    'brands_id' => $curBrandId,
                    'types_id' => Type::where([['parent',$parentID],['name',$row['外设类型二']]])->pluck('id')->first(),
                    'release_date' => $row['发布日期'] ? date('Y-m-d',($row['发布日期']-25569)*24*3600):null,
                    'eosl_date' => $row['服务终止日期'] ? date('Y-m-d',($row['服务终止日期']-25569)*24*3600):null,
                    'industries' => $row['行业分类'],
                    'comment' => $row['外设描述'],
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                $curPeripheralId = DB::table('peripherals')->insertGetId($peripheralInsert);
            }

            $pbindInsert =
            [
                'os_subversion' => $row['操作系统小版本']?:'',
                'statuses_id' => Status::where('name',$row['当前细分适配状态'])->pluck('id')->first(),
                'class' => $row['兼容等级'],
                'solution_name' => $row['方案名称'],
                'solution' => $row['方案下载地址'],
                'comment' => $row['备注'],
                'adapt_source' => $row['引入来源'],
                'adapted_before' => $this->bools($row['是否适配过国产CPU']),
                'user_name' => $row['当前适配状态责任人'],
                'adaption_type' => $row['适配类型'],
                'test_type' => $row['测试方式'],
                'kylineco' => $this->bools($row['是否上传生态网站']),
                'appstore' => $this->bools($row['是否上架软件商店']),
                'iscert' => $this->bools($row['是否互认证']),
                'start_time' => $row['适配开始时间'] ? date('Y-m-d',($row['适配开始时间']-25569)*24*3600):null,
                'complete_time' => $row['适配完成时间'] ? date('Y-m-d',($row['适配完成时间']-25569)*24*3600):null,
                'updated_at' => $curtime,
            ];
            $pbindInsertUnique = 
            [
                'peripherals_id' => $curPeripheralId,
                'chips_id' => Chip::where('name','like','%'.$row['芯片'].'%')->pluck('id')->first(),
                'releases_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
            ];
            
            $a = Pbind::updateOrCreate($pbindInsertUnique,$pbindInsert);
     
            $curPbindId = $a->id;
            $b = $a->wasRecentlyCreated;
            $c = $a->wasChanged();

            //新增数据
            if($b)
            {
                $pbindhistory = 
                [
                    'pbind_id' => $curPbindId,
                    'status_old' => null,
                    'status_new' => $pbindInsert['statuses_id'],
                    'user_name' => $pbindInsert['user_name'],
                    'comment' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                DB::table('pbind_histories')->insert($pbindhistory);
            }

            //更新数据
            if(!$b && $c)
            {
                $curHistoryId = PbindHistory::where('pbind_id',$curPbindId)->orderBy('id','DESC')->pluck('status_new')->first();
                
                $pbindhistory = 
                [
                'pbind_id' => $curPbindId,
                'status_old' => $curHistoryId,
                'status_new' => $pbindInsert['statuses_id'],
                'user_name' => $pbindInsert['user_name'],
                'comment' => null,
                'created_at' => $curtime,
                'updated_at' => $curtime,
                ];

                DB::table('pbind_histories')->insert($pbindhistory);
                
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