<?php

namespace App\Admin\Actions\Imports;

use App\Exceptions\RequiredNotFoundException;
use App\Models\AdminUser;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\Status;
use App\Models\Stype;
use App\Models\Type;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

use function PHPUnit\Framework\isEmpty;

HeadingRowFormatter::default('none');
class SbindUpdate implements ToCollection, WithHeadingRow, WithValidation
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
                '*.厂商'        => [
                    'required'
                ],
                '*.软件名称'    => [
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

            ],
        ); 

        $validator->errors()->first();
        $validator->validate();

        foreach($rows as $key => $row)
        {
            $curtime = date('Y-m-d H:i:s');

            $curManufactorId = Manufactor::where('name',trim($row['厂商']))->pluck('id')->first();

            if(empty($row['软件版本号'])){
                $curSoftwareId = Software::where([
                    ['name',trim($row['软件名称'])],
                    ['manufactors_id',$curManufactorId],
                    ['version',null]
                ])->pluck('id')->first();
            }else{
                $curSoftwareId = Software::where([
                    ['name',trim($row['软件名称'])],
                    ['manufactors_id',$curManufactorId],
                    ['version',trim($row['软件版本号'])]
                ])->pluck('id')->first();
            }

            $sbindInsertCache =
            [
                'os_subversion' => trim($row['操作系统小版本']),
                'adapt_source' => trim($row['引入来源']),
                'adapted_before' => $this->bools(trim($row['是否适配过国产CPU'])),
                'statuses_id' => Status::where('name',trim($row['当前细分适配状态']))->pluck('id')->first(),
                'admin_user_id' => AdminUser::where('name',trim($row['当前适配状态责任人']))->pluck('id')->first() ,
                'solution_name' => $row['安装包名称'],
                'solution' => $row['安装包下载地址'],
                'class' => $row['兼容等级'],
                'adaption_type' => $row['适配类型'],
                'test_type' => $row['测试方式'],
                'kylineco' => $this->bools($row['是否上传生态网站']),
                'appstore' => $this->bools($row['是否上架软件商店']),
                'iscert' => $this->bools($row['是否互认证']),
                'test_report' => $this->bools($row['是否有测试报告']),
                'certificate_NO' => $row['证书编号'],
                'comment' => $row['备注'],
                'updated_at' => $curtime,
            ];

            foreach($sbindInsertCache as $k => $v){
                if(isset($v)){
                    $sbindInsert[$k] = $v;
                }
            };
            
            $sbindInsertUnique = 
            [
                'softwares_id' => $curSoftwareId,
                'chips_id' => Chip::where('name','like','%'.trim($row['芯片']).'%')->pluck('id')->first(),
                'releases_id' => Release::where('name',trim($row['操作系统版本']))->pluck('id')->first(),
            ];

            $a = Sbind::updateOrCreate($sbindInsertUnique,$sbindInsert);

            // $curSbind = Sbind::find($a->id);
            // $b = $a->wasRecentlyCreated;
            // $c = $a->wasChanged();

            // //新增数据
            // if($b)
            // {
            //    $curSbind->solution_name = $row['安装包名称'];
            //    $curSbind->solution = $row['安装包下载地址'];
            //    $curSbind->save();
            // }

            // //更新数据
            // if(!$b && $c)
            // {
            //     $curSbind->solution_name = $curSbind->solution_name.';'.$row['安装包名称'];
            //     $curSbind->solution = $curSbind->solution.';'.$row['安装包下载地址'];
            //     $curSbind->save();
            // }
            
        }
        
    }

    public function bools($value){
        if($value == '是'){return 1;}
        elseif($value == '否'){return 0;}
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