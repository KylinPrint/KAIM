<?php

namespace App\Admin\Actions\Imports;

use App\Exceptions\RequiredNotFoundException;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
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
class PRequestImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function collection(Collection $rows)
    {
        set_time_limit(0);

        unset($rows[0]);  //去掉表头


        foreach($rows as $key => $row)
        {
            if
            ( 
                !($row['需求来源']&&
                $row['厂商名称']&&
                $row['品牌名称']&&
                $row['产品名称']&&
                $row['外设分类']&&
                $row['涉及行业']&&
                $row['操作系统版本']&&
                $row['芯片']&&
                $row['紧急程度']&&
                $row['期望完成日期']&&
                $row['需求提出人']&&
                $row['需求提出人联系方式']&&
                $row['生态负责人'])
            ){
                throw new RequiredNotFoundException($key);
            }
            
            if($row['厂商名称'] != '')
            {
                $curManufactorId = Manufactor::where('name',$row['厂商名称'])->pluck('id')->first();
                if(empty($curManufactorId))
                {
                    $manufactorInsert = 
                    [
                        'name' => $row['厂商名称'],
                        'isconnected' => '0',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    $curManufactorId = DB::table('manufactors')->insertGetId($manufactorInsert);
                }
            }

            $curBrandId = Brand::where('name',$row['品牌名称'])->pluck('id')->first();
            if(empty($curBrandId))
            {
                $brandInsert = 
                [
                    'name' => $row['品牌名称'],
                    'alias' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $curBrandId = DB::table('brands')->insertGetId($brandInsert);
            }
            

            $curPeripheralId = Peripheral::where('name',$row['产品名称'])->pluck('id')->first();
            if(empty($curPeripheralId))
            {
                $peripheralInsert = 
                [
                    'name' => $row['产品名称'],
                    'manufactors_id' => $curManufactorId,
                    'brands_id' => $curBrandId,
                    'types_id' => Type::where('name',$row['外设分类'])->pluck('id')->first(),
                    'release_date' => '',
                    'eosl_date' => '',
                    'industries' => $row['涉及行业'],
                    'comment' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $curPeripheralId = DB::table('peripherals')->insertGetId($peripheralInsert);
            }

            $pbindInsert =
            [
                
            ];
            
            $pbindInsertUnique = 
            [
                'peripherals_id' => $curPeripheralId,
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