<?php

namespace App\Admin\Actions\Imports;

use App\Exceptions\RequiredNotFoundException;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Peripheral;
use App\Models\PRequest;
use App\Models\PRequestHistory;
use App\Models\Release;
use App\Models\Status;
use App\Models\Type;
use Dcat\Admin\Admin;
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

            $parentID = Type::where('name',$row['分类二'])->pluck('id')->first();
            $PRequestInsert =
            [
                'source' => $row['需求来源'],
                'type_id' => Type::where([['parent',$parentID],['name',$row['分类三']]])->pluck('id')->first(),
                'industry' => $row['涉及行业'],
                'os_subversion' => $row['操作系统小版本号'],
                'project_name' => $row['项目名称'],
                'amount' => $row['涉及数量'],
                'project_status' => $row['项目状态'],
                'level' => $row['紧急程度'],
                'manufactor_contact' => $row['厂商联系方式'],
                'et' => date('Y-m-d',($row['期望完成日期']-25569)*24*3600),
                'creator' => Admin::user()->id,
                'requester_name' => $row['需求提出人'],
                'requester_contact' => $row['需求提出人联系方式'],
                'status' => '已提交',
                'bd_id' => AdminUser::where('name',$row['需求接收人'])->pluck('id')->first(),
                'comment' => $row['备注'],
            ];
            
            
            $pRequestInsertUnique = 
            [   
                'manufactor' => $row['厂商名称'],
                'brand' => $row['品牌名称'], 
                'name' => $row['产品名称'], 
                'chip_id' => Chip::where('name',$row['芯片'])->pluck('id')->first(),
                'release_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
            ];
            

            $a = PRequest::updateOrCreate($pRequestInsertUnique,$PRequestInsert);
            $curPrequestId = $a->id;
            $b = $a->wasRecentlyCreated;
            $c = $a->wasChanged();

            $curtime = date('Y-m-d H:i:s');
     
            //新增数据
            if($b)
            {
                $pRequesthistory = 
                [
                    'p_request_id' => $curPrequestId,
                    'status_old' => null,
                    'status_new' => '已提交',
                    'user_name' => Admin::user()->name,
                    'comment' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                DB::table('p_request_histories')->insert($pRequesthistory);
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
            'prequestid' => Rule::unique('prequests', 'prequestid'), 
        ];
    }

    public function customValidationMessages()
    {
        return [
            'prequestid.unique' => '导入存在重复数据',
        ];
    }
}