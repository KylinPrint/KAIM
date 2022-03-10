<?php

namespace App\Admin\Actions\Imports;

use App\Models\Brand;
use App\Models\Industry;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\PeripheralIndustry;
use App\Models\Solution;
use App\Models\Type;
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

        unset($row[0]);  //去掉表头

        $IndustryArr = Industry::all()->pluck('name','id')->toArray();

        foreach($rows as $row)
        {
            if($row['厂商'] != '')
            {
                $curManufactorId = Manufactor::where('name',$row['厂商']->pluck('id')->first());
                if(empty($curManufactorId))
                {
                    $manufactorInsert = 
                    [
                        'name' => $row['厂商'],
                        'isconnected' => $row['是否建联'] == '是'?1:0,
                        'created_at' => date('Y-M-D H:i:s'),
                        'updated_at' => date('Y-M-D H:i:s'),
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
                    'created_at' => date('Y-M-D H:i:s'),
                    'updated_at' => date('Y-M-D H:i:s'),
                ];
                $curBrandId = DB::table('brands')->insertGetId($brandInsert);
            }
            

            $curPeripheralId = Peripheral::where('name',$row['外设型号'])->pluck('id')->first();
            if(empty($curPeripheralId))
            {
                $peripheralInsert = 
                [
                    'name' => $row['外设型号'],
                    'brands_id' => $curBrandId,
                    'types_id' => Type::where('name',$row['外设类型二'])->pluck('id')->first(),
                    'release_date' => $row['发布日期'],
                    'eosl_date' => $row['服务终止日期'],
                    'comment' => $row['外设描述'],
                    'created_at' => date('Y-M-D H:i:s'),
                    'updated_at' => date('Y-M-D H:i:s'),
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
                    'created_at' => date('Y-M-D H:i:s'),
                    'updated_at' => date('Y-M-D H:i:s'),
                ];
                DB::table('peripheral_industry')->insert($peripheralIndustryInsert);
            }

            $curSolutionId = Solution::where('name',$row['安装包名称'])->pluck('id')->first();
            if(empty($curSolutionId))
            {
                $solutionInsert = 
                [
                    'name' => $row['安装包名称'],
                    'details' => $row['下载地址'] ,
                    'source' => '',
                    'created_at' => date('Y-M-D H:i:s'),
                    'updated_at' => date('Y-M-D H:i:s'),
                ];
                $curSolutionId = DB::table('solutions')->insertGetId($solutionInsert);
            }

            $pbindInsert =
            [
                'peripherals_id' => $curPeripheralId,
                'solutions_id' => $curSolutionId,
                'chips_id' => $row['芯片'],
                'releases_id' => $row['适配系统'],
                'status_id' => $row['适配状态'],
                'class' => $row['兼容等级'],
                'commment' => $row['备注'],
                'created_at' => date('Y-M-D H:i:s'),
                'updated_at' => date('Y-M-D H:i:s'),
            ];
            $pbindInsertUnique = 
            [
                'peripherals_id' => $curPeripheralId,
                'solutions_id' => $curSolutionId,
                'chips_id' => $row['芯片'],
                'releases_id' => $row['适配系统'],
            ];
            Rule::unique('pbinds')->where(function ($query) use ($pbindInsertUnique)
            {
                return $query->where($pbindInsertUnique);
            });
            DB::table('pbinds')->insert($pbindInsert);
  
        }
        
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