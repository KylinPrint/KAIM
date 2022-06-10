<?php

namespace App\Admin\Actions\Imports;

use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\PbindHistory;
use App\Models\Peripheral;
use App\Models\Release;
use App\Models\Specification;
use App\Models\Status;
use App\Models\Type;
use App\Models\Value;
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
            
            // 厂商
            if($row['厂商'] != '')
            {
                $curManufactorId = Manufactor::where('name',trim($row['厂商']))->pluck('id')->first();
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

            // 品牌  如果抓到括号,默认中英文都有,且括号外中文,括号内英文
            if(preg_match('/\(|\（/',$row['品牌'])){

                // 抓品牌的中文和英文
                preg_match('/(.+(?=\(|\（))/',trim($row['品牌']),$input_brand_name);
                preg_match('/(?<=\(|\（).+?(?=\)|\）)/',trim($row['品牌']),$input_brand_name_en);

                $brand_name = $input_brand_name[0];
                $brand_name_en = $input_brand_name_en[0];
                $curBrandId = Brand::where('name','like','%'.$brand_name.'%')
                ->orWhere('name_en','like','%'.$brand_name_en.'%')
                ->pluck('id')->first();

                if(!$curBrandId){
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
            }else{
                if(preg_match('/[\x7f-\xff]/',$row['品牌'])){
                    $curBrandId = Brand::where('name','like','%'.trim($row['品牌']).'%')->pluck('id')->first();

                    if(!$curBrandId){
                        $brandInsert = 
                        [
                            'name' => $row['品牌'],
                            'name_en' => null,
                            'alias' => null,
                            'created_at' => $curtime,
                            'updated_at' => $curtime,
                        ];
                        $curBrandId = DB::table('brands')->insertGetId($brandInsert);
                    }
                }else{
                   
                    $curBrandId = Brand::where('name_en','like','%'.trim($row['品牌']).'%')->pluck('id')->first();

                    if(!$curBrandId){
                        $brandInsert = 
                        [
                            'name' => null,
                            'name_en' => $row['品牌'],
                            'alias' => null,
                            'created_at' => $curtime,
                            'updated_at' => $curtime,
                        ];
                        $curBrandId = DB::table('brands')->insertGetId($brandInsert);
                    }
                }
            }

            // 外设
            $curPeripheralId = Peripheral::where('name',trim($row['外设型号']))->pluck('id')->first();
            if(empty($curPeripheralId))
            {
                $parentID = Type::where('name',$row['外设类型一'])->pluck('id')->first();
                $peripheralInsert = 
                [
                    'name' => $row['外设型号'],
                    'manufactors_id' => isset($curManufactorId) ? $curManufactorId : null,
                    'brands_id' => $curBrandId,
                    'types_id' => Type::where([['parent',$parentID],['name',trim($row['外设类型二'])]])->pluck('id')->first(),
                    'release_date' => $row['发布日期'] ? date('Y-m-d',($row['发布日期']-25569)*24*3600):null,
                    'eosl_date' => $row['服务终止日期'] ? date('Y-m-d',($row['服务终止日期']-25569)*24*3600):null,
                    'industries' => $row['行业分类'],
                    'comment' => $row['外设描述'],
                    'created_at' => $curtime,
                    'updated_at' => $curtime,
                ];
                $curPeripheralId = DB::table('peripherals')->insertGetId($peripheralInsert);
            }

            //处理参数
            foreach($row as $k => $v){
                //定位到固定列最后一列
                if($k == '备注'){
                    $tag = 0;
                }
                if(isset($tag)){
                    //进入非固定列          
                    if($tag != 0){
                        if(empty($v)){break;}
                        //找有没有这个参数名,没有就加

                        $parentID = Type::where('name',$row['外设类型一'])->pluck('id')->first();
                        $types_id = Type::where([['parent',$parentID],['name',trim($row['外设类型二'])]])->pluck('id')->first();

                        $specificationId = Specification::where([['name',str_replace('*','',$k)],['types_id',$types_id]])->pluck('id')->first();

                        if(empty($specificationId)){

                            
                            $isrequired = strpos($k,'*')?0:1;

                            // 导入的默认都是文本类型  开摆
                            $specificationInsert = 
                            [
                                'name'       => str_replace('*','',$k),
                                'types_id'   => $types_id,
                                'isrequired' => $isrequired,
                                'field'      => 0,
                                'created_at' => $curtime,
                                'updated_at' => $curtime,
                            ];

                            $specificationId = DB::table('specifications')->insertGetId($specificationInsert);
                        }
                        
                        $valueInsertCache = [
                            'value' => $v,
                        ];
                        $valueInsertUnique = [
                            'peripherals_id'    => $curPeripheralId,
                            'specifications_id' => $specificationId,
                        ];

                        Value::updateOrCreate($valueInsertUnique,$valueInsertCache);
                    }
                    //准备进入非固定列
                    elseif($tag == 0){$tag ++ ;}
                }
            }
            // 循环结束没有释放  怪
            unset($tag);

            $pbindInsertCache =
            [
                'os_subversion' => $row['操作系统小版本'],
                'statuses_id' => Status::where('name',$row['当前细分适配状态'])->pluck('id')->first(),
                'class' => $row['兼容等级'],
                'solution_name' => $row['方案名称'],
                'solution' => $row['方案下载地址'],
                'comment' => $row['备注'],
                'adapt_source' => $row['引入来源'],
                'adapted_before' => $this->bools($row['是否适配过国产CPU']),
                'admin_user_id' => AdminUser::where('name',trim($row['当前适配状态责任人']))->pluck('id')->first() ,
                'adaption_type' => $row['适配类型'],
                'test_type' => $row['测试方式'],
                'kylineco' => $this->bools($row['是否上传生态网站']),
                'appstore' => $this->bools($row['是否上架软件商店']),
                'iscert' => $this->bools($row['是否互认证']),
                'test_report' => $this->bools($row['是否有测试报告']),
                'certificate_NO' => $row['证书编号'],
                'start_time' => $row['适配开始时间'] ? date('Y-m-d',($row['适配开始时间']-25569)*24*3600):null,
                'complete_time' => $row['适配完成时间'] ? date('Y-m-d',($row['适配完成时间']-25569)*24*3600):null,
            ];

            foreach($pbindInsertCache as $k => $v){
                if(isset($v)){
                    $pbindInsert[$k] = $v;
                }
            };

            $pbindInsertUnique = 
            [
                'peripherals_id' => $curPeripheralId,
                'chips_id' => Chip::where('name','like','%'.trim($row['芯片']).'%')->pluck('id')->first(),
                'releases_id' => Release::where('name',trim($row['操作系统版本']))->pluck('id')->first(),
            ];
            
            $a = Pbind::updateOrCreate($pbindInsertUnique,$pbindInsert);
     
            // $curPbindId = $a->id;
            // $cc = $a->solution;
            // $b = $a->wasRecentlyCreated;
            // $c = $a->wasChanged();

            // //新增数据
            // if($b)
            // {
            //     $pbindhistory = 
            //     [
            //         'pbind_id' => $curPbindId,
            //         'status_old' => null,
            //         'status_new' => $pbindInsert['statuses_id'],
            //         'user_name' => Admin::user()->name,
            //         'comment' => null,
            //         'created_at' => $curtime,
            //         'updated_at' => $curtime,
            //     ];
            //     DB::table('pbind_histories')->insert($pbindhistory);
            // }

            // //更新数据
            // if(!$b && $c)
            // {
            //     $curHistoryId = PbindHistory::where('pbind_id',$curPbindId)->orderBy('id','DESC')->pluck('status_new')->first();
                
            //     $pbindhistory = 
            //     [
            //     'pbind_id' => $curPbindId,
            //     'status_old' => $curHistoryId,
            //     'status_new' => $pbindInsert['statuses_id']??$curHistoryId,
            //     'user_name' => Admin::user()->name,
            //     'comment' => null,
            //     'created_at' => $curtime,
            //     'updated_at' => $curtime,
            //     ];

            //     DB::table('pbind_histories')->insert($pbindhistory);
                
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