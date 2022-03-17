<?php

namespace App\Admin\Actions\Imports;

use App\Exceptions\RequiredNotFoundException;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Industry;
use App\Models\Manufactor;
use App\Models\Pbind;
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

        $IndustryArr = Industry::all()->pluck('name','id')->toArray();

        foreach($rows as $key => $row)
        {
            if
            ( 
                !($row['厂商']&&
                $row['品牌']&&
                $row['外设型号']&&
                $row['外设类型一']&&
                $row['外设类型二']&&
                $row['行业分类']&&
                $row['操作系统版本']&&
                $row['芯片']&&
                $row['架构']&&
                $row['引入来源']&&
                $row['当前适配状态']&&
                $row['当前细分适配状态']&&
                $row['当前适配状态责任人']&&
                $row['是否上传生态网站']&&
                $row['是否上架软件商店']&&
                $row['是否互认证'])
            ){
                throw new RequiredNotFoundException($key);
            }
            
            if($row['厂商'] != '')
            {
                $curManufactorId = Manufactor::where('name',$row['厂商'])->pluck('id')->first();
                if(empty($curManufactorId))
                {
                    $manufactorInsert = 
                    [
                        'name' => $row['厂商'],
                        'isconnected' => '0',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    $curManufactorId = DB::table('manufactors')->insertGetId($manufactorInsert);
                }
            }

            $curBrandId = Brand::where('name',$row['品牌'])->pluck('id')->first();
            if(empty($curBrandId))
            {
                $brandInsert = 
                [
                    'name' => $row['品牌'],
                    'alias' => '',
                    'manufactors_id' => $row['厂商'] == ''?'':$curManufactorId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $curBrandId = DB::table('brands')->insertGetId($brandInsert);
            }
            

            $curPeripheralId = Peripheral::where('name',$row['外设型号'])->pluck('id')->first();
            if(empty($curPeripheralId))
            {
                $peripheralInsert = 
                [
                    'name' => $row['外设型号'],
                    'manufactors_id' => $curManufactorId,
                    'brands_id' => $curBrandId,
                    'types_id' => Type::where('name',$row['外设类型二'])->pluck('id')->first(),
                    'release_date' => date('Y-m-d',($row['发布日期']-25569)*24*3600),
                    'eosl_date' => date('Y-m-d',($row['服务终止日期']-25569)*24*3600),
                    'comment' => $row['外设描述'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $curPeripheralId = DB::table('peripherals')->insertGetId($peripheralInsert);
            }

            $curIndustryId = '';
            foreach($IndustryArr as $id => $name)
            {
                if($name == $row['行业分类'])
                {
                    $curIndustryId = $id;
                }
            }

            $curPeripheralIndustry = 
                    PeripheralIndustry::where
                    ([
                        ['peripherals_id',$curPeripheralId],
                        ['industries_id',$curIndustryId]
                    ])->get();
            if($curPeripheralIndustry->isEmpty())
            {
                $peripheralIndustryInsert = 
                [
                    'peripherals_id' => $curPeripheralId,
                    'industries_id' => $curIndustryId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                DB::table('peripheral_industry')->insert($peripheralIndustryInsert);
            }

            $curSolutionId = Solution::where('name',$row['方案名称'])->pluck('id')->first();
            if(empty($curSolutionId))
            {
                $solutionInsert = 
                [
                    'name' => $row['方案名称'],
                    'details' => $row['方案下载地址'] ,
                    'source' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $curSolutionId = DB::table('solutions')->insertGetId($solutionInsert);
            }

            $pbindInsert =
            [
                'peripherals_id' => $curPeripheralId,
                'solutions_id' => $curSolutionId,
                'chips_id' => 
                    Chip::where([
                        ['name',$row['芯片']],
                        ['arch',$row['架构']]
                    ])->pluck('id')
                    ->first(),
                'releases_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
                'os_subversion' => $row['操作系统小版本']?:'',
                'statuses_id' => Status::where('name',$row['当前细分适配状态'])->pluck('id')->first(),
                'class' => $row['兼容等级'],
                'comment' => $row['备注'],
                'adapt_source' => $row['引入来源'],
                'adapted_before' => $this->bools($row['是否适配过国产CPU']),
                'admin_users_id' => AdminUser::where('name',$row['当前适配状态责任人'])->pluck('id')->first(),
                'adaption_type' => $row['适配类型'],
                'test_type' => $row['测试方式'],
                'kylineco' => $this->bools($row['是否上传生态网站']),
                'appstore' => $this->bools($row['是否上架软件商店']),
                'iscert' => $this->bools($row['是否互认证']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $pbindInsertUnique = 
            [
                'peripherals_id' => $curPeripheralId,
                'solutions_id' => $curSolutionId,
                'chips_id' => Chip::where('name',$row['芯片'])->pluck('id')->first(),
                'releases_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
            ];
            Rule::unique('pbinds')->where(function ($query) use ($pbindInsertUnique)
            {
                return $query->where($pbindInsertUnique);
            });
            DB::table('pbinds')->insert($pbindInsert);
  
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