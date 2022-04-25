<?php

namespace App\Admin\Actions\Imports;

use App\Exceptions\RequiredNotFoundException;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Peripheral;
use App\Models\PRequest;
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

        // unset($rows[0]);  去掉表头


        foreach($rows as $key => $row)
        {
            // if
            // ( 
            //     !($row['需求来源']&&
            //     $row['厂商名称']&&
            //     $row['品牌名称']&&
            //     $row['产品名称']&&
            //     $row['分类三']&&
            //     $row['涉及行业']&&
            //     $row['操作系统版本']&&
            //     $row['芯片']&&
            //     $row['紧急程度']&&
            //     $row['期望完成日期']&&
            //     $row['需求提出人']&&
            //     $row['需求提出人联系方式']&&
            //     $row['需求接收人'])
            // ){
            //     throw new RequiredNotFoundException($key);
            // }

            if(!$row['需求来源']){continue;}  //TODO 上边写的异常抛出后不继续执行，待检查


            $PRequestInsert =
            [
                'source' => $row['需求来源'],
                'type_id' => Type::where('name',$row['分类三'])->pluck('id')->first(),
                'industry' => $row['涉及行业'],
                'os_subversion' => $row['操作系统小版本号'],
                'project_name' => $row['项目名称'],
                'amount' => $row['涉及数量'],
                'project_status' => $row['项目状态'],
                'level' => $row['紧急程度'],
                'manufactor_contact' => $row['厂商联系方式'],
                'et' => date('Y-m-d',($row['期望完成日期']-25569)*24*3600),
                'requester_name' => $row['需求提出人'],
                'requester_contact' => $row['需求提出人联系方式'],
                'status' => '已提交',
                'bd_id' => AdminUser::where('name',$row['需求接收人'])->pluck('id')->first(),
                'comment' => $row['备注'],
            ];
            
            
            $prequestInsertUnique = 
            [   
                'manufactor' => $row['厂商名称'],
                'brand' => $row['品牌名称'], 
                'name' => $row['产品名称'], 
                'chip_id' => Chip::where('name',$row['芯片'])->pluck('id')->first(),
                'release_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
            ];
            

            $a = PRequest::updateOrCreate($prequestInsertUnique,$PRequestInsert);
     
            // $curPbindId = $a->id;
            // $b = $a->wasRecentlyCreated;
            // $c = $a->wasChanged();
  
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