<?php

namespace App\Admin\Actions\Imports;

use App\Exceptions\RequiredNotFoundException;
use App\Models\AdminUser;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\SbindHistory;
use App\Models\Software;
use App\Models\Status;
use App\Models\Stype;
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
class SbindImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        set_time_limit(0);

        // unset($row[0]);  //去掉表头

        foreach($rows as $key => $row)
        {
            // if
            // ( 
            //     !($row['厂商']&&
            //     $row['品牌']&&
            //     $row['软件名称']&&
            //     $row['软件分类一']&&
            //     $row['软件分类二']&&
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

            if(!$row['软件名称']){continue;}  //TODO 上边写的异常抛出后不继续执行，待检查

            $curtime = date('Y-m-d H:i:s');

            $curManufactorId = Manufactor::where('name',trim($row['厂商名称']))->pluck('id')->first();
            if(empty($curManufactorId))
            {
                $manufactorInsert = 
                [
                    'name' => $row['厂商名称'],
                    'isconnected' => '',
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                $curManufactorId = DB::table('manufactors')->insertGetId($manufactorInsert);
            }
            

            $curSoftwareId = Software::where('name',trim($row['软件名称']))->pluck('id')->first();
            if(empty($curSoftwareId))
            {   
                $parentID = Stype::where('name',trim($row['软件分类一']))->pluck('id')->first();
                $softwareInsert = 
                [
                    'name' => $row['软件名称'],
                    'manufactors_id' => $curManufactorId,
                    'version' => $row['软件版本号'],
                    'stypes_id' => Stype::where([['parent',$parentID],['name',$row['软件分类二']]])->pluck('id')->first(),
                    'kernel_version' => $row['引用版本'],
                    'crossover_version' => $row['Crossover版本'],
                    'box86_version' => $row['Box86版本'],
                    'bd' => $row['生态负责人'],
                    'am' => $row['适配负责人'],
                    'tsm' => $row['技术支撑负责人'],
                    'comment' => $row['软件描述'],
                    'industries' => $row['行业'],
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                $curSoftwareId = DB::table('softwares')->insertGetId($softwareInsert);
            }

            $sbindInsert =
            [
                'os_subversion' => $row['操作系统小版本号']?:'',
                'adapt_source' => $row['引入来源'],
                'adapted_before' => $this->bools($row['是否适配过国产CPU']),
                'statuses_id' => Status::where('name',$row['当前细分适配状态'])->pluck('id')->first(),
                'user_name' =>$row['当前适配状态责任人'],
                'softname' => $row['安装包名称'],
                'solution' => $row['安装包下载地址'],
                'class' => $row['兼容等级'],
                'adaption_type' => $row['适配类型'],
                'test_type' => $row['测试方式'],
                'kylineco' => $this->bools($row['是否上传生态网站']),
                'appstore' => $this->bools($row['是否上架软件商店']),
                'iscert' => $row['是否互认证'],
                'test_report' => $this->bools($row['是否有测试报告']),
                'certificate_NO' => $row['证书编号'],
                'comment' => $row['备注'],
                'updated_at' => $curtime,
            ];
            
            $sbindInsertUnique = 
            [
                'softwares_id' => $curSoftwareId,
                'chips_id' => Chip::where([
                    ['name','like','%'.$row['芯片'].'%'],
                    ['arch',$row['架构']]
                ])->pluck('id')
                ->first(),
                'releases_id' => Release::where('name',$row['操作系统版本'])->pluck('id')->first(),
            ];

            $a = Sbind::updateOrCreate($sbindInsertUnique,$sbindInsert);

            $curSbindId = $a->id;
            $b = $a->wasRecentlyCreated;
            $c = $a->wasChanged();

            if($b)
            {
                $sbindhistory = 
                [
                    'sbind_id' => $curSbindId,
                    'status_old' => null,
                    'status_new' => $sbindInsert['statuses_id'],
                    'user_name' => $sbindInsert['user_name'],
                    'comment' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];

                DB::table('sbind_histories')->inset($sbindhistory);
            }

            if(!$b && $c)
            {
                $curHistoryId = SbindHistory::where('sbind_id',$curSbindId)->orderBy('id','DESC')->pluck('status_new')->first();
                
                $sbindhistory = 
                [
                    'sbind_id' => $curSbindId,
                    'status_old' => $curHistoryId,
                    'status_new' => $sbindInsert['statuses_id'],
                    'user_name' => $sbindInsert['user_name'],
                    'comment' => null,
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                DB::table('sbind_histories')->insert($sbindhistory);
                
            }
            
        }
        
    }

    public function bools($value){
        return $value == '是'?1:0;
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