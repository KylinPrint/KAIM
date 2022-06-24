<?php

namespace App\Admin\Actions\Imports;

use App\Exceptions\RequiredNotFoundException;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\PRequest;
use Illuminate\Support\Facades\Validator;
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

        $a = $rows->toArray();
        $b = [];
        array_walk(
            $a, 
            function ($value,$key) use (&$b)
            {
                $b['第'. $key+2 .'行'] = $value;
            }
        );

        $validator = Validator::make($b,
            [
                '*.品牌名称'        => [
                    'required'
                ],
                '*.厂商名称'        => [
                    'required'
                ],
                '*.产品名称'    => [
                    'required'
                ],
                '*.涉及行业'    => [
                    'required'
                ],
                '*.操作系统版本' => [
                    'bail',
                    'required',
                    Rule::in(array_column(Release::select('name')->get()->toArray(),'name')),
                ],
                '*.芯片'        => [
                    'bail',
                    'required',
                    Rule::in(array_column(Chip::select('name')->get()->toArray(),'name')),
                ],
                '*.需求来源'    => [
                    'bail',
                    'required',
                    Rule::in(config('kaim.adapt_source'))
                ],
                '*.分类二'  => [
                    'bail',
                    'required',
                    Rule::in(Type::where('parent',0)->pluck('name')->toArray()),
                ],
                '*.分类三'  => [
                    'bail',
                    'required',
                    Rule::in(Type::whereNot('parent',0)->pluck('name')->toArray()),
                ],
                '*.紧急程度'=> [
                    'required',
                ],
                '*.期望完成日期'=> [
                    'bail',
                    'required',
                    'numeric',
                    'between:40000,50000'
                ],
                '*.需求提出人联系方式'=> [
                    'required',
                ],
                '*.需求提出人'=> [
                    'required',
                ],
                '*.需求接收人'=> [
                    'bail',
                    'required',
                    Rule::in(array_column(AdminUser::select('name')->get()->toArray(),'name')),
                ],
                '*.操作系统小版本号'=> [
                    'required',
                ],
                '*.项目名称'=> [
                    'required',
                ],
                '*.涉及数量'=> [
                    'required',
                ],
                '*.项目状态'=> [
                    'required',
                ],
                '*.厂商联系方式'=> [
                    'required',
                ],
            ],
            [
                '*.需求接收人.in' => ':attribute 的用户未注册.',
                '*.期望完成日期.numeric' => ':attribute 的日期不正确,请确认单元格格式.',
            ]
        ); 

        $validator->errors()->first();
        $validator->validate();

        foreach($rows as $key => $row)
        {
            $parentID = Type::where('name',trim($row['分类二']))->pluck('id')->first();
            $PRequestInsert =
            [
                'source' => $row['需求来源'],
                'type_id' => Type::where([['parent',$parentID],['name',trim($row['分类三'])]])->pluck('id')->first(),
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
                'chip_id' => Chip::where('name',trim($row['芯片']))->pluck('id')->first(),
                'release_id' => Release::where('name',trim($row['操作系统版本']))->pluck('id')->first(),
            ];
            

            $a = PRequest::updateOrCreate($pRequestInsertUnique,$PRequestInsert);
  
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