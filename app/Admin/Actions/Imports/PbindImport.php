<?php

namespace App\Admin\Actions\Imports;

use App\Models\Brand;
use App\Models\Pbind;
use App\Models\Peripheral;
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

        foreach($rows as $row)
        {
            $curBrandId = Brand::where('name',$row['厂商名称'])->pluck('id')->first();
            if(empty($curBrandId))
            {
                $brandInsert = 
                [
                    'name' => $row['厂商名称'],
                    'alias' => '',
                    'manufactors_id' => '',
                    'created_at' => date('Y-M-D H:i:s'),
                    'updated_at' => date('Y-M-D H:i:s'),
                ];
                $curBrandId = DB::table('brands')->insertGetId($brandInsert);
            }
            

            $curPeripheralId = Peripheral::where('name',$row['产品名称'])->pluck('id')->first();
            if(empty($curPeripheralId))
            {
                $peripheralInsert = 
                [
                    'name' => $row['产品名称'],
                    'brands_id' => $curBrandId,
                    'types_id' => Type::where('name',$row['分类2'])->pluck('id')->first(),
                    'release_date' => '',
                    'eosl_date' => '',
                    'created_at' => date('Y-M-D H:i:s'),
                    'updated_at' => date('Y-M-D H:i:s'),
                ];
                $curPeripheralId = DB::table('peripherals')->insertGetId($peripheralInsert);
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