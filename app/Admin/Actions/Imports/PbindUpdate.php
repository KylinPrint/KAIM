<?php

namespace App\Admin\Actions\Imports;

use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Release;
use App\Models\Specification;
use App\Models\Status;
use App\Models\Type;
use App\Models\Value;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;


HeadingRowFormatter::default('none');
class PbindUpdate implements ToCollection, WithHeadingRow
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
                '*.品牌'        => [
                    'required'
                ],
                '*.外设型号'    => [
                    'bail',
                    'required',
                    'exists:peripherals,name'
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
            
            // 厂商
            if($row['厂商'] != '')
            {
                $curManufactorId = Manufactor::where('name',trim($row['厂商']))->pluck('id')->first();
                if(empty($curManufactorId))
                {
                    $manufactorInsert = 
                    [
                        'name' => trim($row['厂商']),
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

                $brand_name = trim($input_brand_name[0]);
                $brand_name_en = trim($input_brand_name_en[0]);
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
                            'name' => trim($row['品牌']),
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
                            'name_en' => trim($row['品牌']),
                            'alias' => null,
                            'created_at' => $curtime,
                            'updated_at' => $curtime,
                        ];
                        $curBrandId = DB::table('brands')->insertGetId($brandInsert);
                    }
                }
            }

            // 外设
            $curPeripheralId = Peripheral::where([
                ['name' , trim($row['外设型号'])],
                ['brands_id' , $curBrandId],
                ['manufactors_id' , $curManufactorId]])
            ->pluck('id')->first();

            // if(empty($curPeripheralId))
            // {
            //     $parentID = Type::where('name',$row['外设类型一'])->pluck('id')->first();
            //     $peripheralInsert = 
            //     [
            //         'name' => trim($row['外设型号']),
            //         'manufactors_id' => isset($curManufactorId) ? $curManufactorId : null,
            //         'brands_id' => $curBrandId,
            //         'types_id' => Type::where([['parent',$parentID],['name',trim($row['外设类型二'])]])->pluck('id')->first(),
            //         'release_date' => $row['发布日期'] ? date('Y-m-d',($row['发布日期']-25569)*24*3600):null,
            //         'eosl_date' => $row['服务终止日期'] ? date('Y-m-d',($row['服务终止日期']-25569)*24*3600):null,
            //         'industries' => $row['行业分类'],
            //         'comment' => $row['外设描述'],
            //         'created_at' => $curtime,
            //         'updated_at' => $curtime,
            //     ];
            //     $curPeripheralId = DB::table('peripherals')->insertGetId($peripheralInsert);
            // }

            //处理参数
            foreach($row as $k => $v){
                //定位到固定列最后一列
                if($k == '备注'){
                    $tag = 0;
                }
                if(isset($tag)){
                    //进入非固定列          
                    if($tag != 0){
                        if(empty($v)){continue;}
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
            // 循环结束没有释放?
            unset($tag);

            $pbindInsertCache =
            [
                'os_subversion' => trim($row['操作系统小版本']),
                'statuses_id' => Status::where('name',trim($row['当前细分适配状态']))->pluck('id')->first(),
                'class' => trim($row['兼容等级']),
                'solution_name' => $row['方案名称'],
                'solution' => $row['方案下载地址'],
                'comment' => trim($row['备注']),
                'adapt_source' => trim($row['引入来源']),
                'adapted_before' => $this->bools(trim($row['是否适配过国产CPU'])),
                'admin_user_id' => AdminUser::where('name',trim($row['当前适配状态责任人']))->pluck('id')->first() ,
                'adaption_type' => trim($row['适配类型']),
                'test_type' => trim($row['测试方式']),
                'kylineco' => $this->bools(trim($row['是否上传生态网站'])),
                'appstore' => $this->bools(trim($row['是否上架软件商店'])),
                'iscert' => $this->bools(trim($row['是否互认证'])),
                'test_report' => $this->bools(trim($row['是否有测试报告'])),
                'certificate_NO' => trim($row['证书编号']),
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
            
            // $curPbind = Pbind::find($a->id);
            // $b = $a->wasRecentlyCreated;
            // $c = $a->wasChanged();

            // //新增数据
            // if($b)
            // {
            //    $curPbind->solution_name = $row['方案名称'];
            //    $curPbind->solution = $row['方案下载地址'];
            //    $curPbind->save();
            // }

            // //更新数据
            // if(!$b && $c)
            // {
            //     $curPbind->solution_name = $curPbind->solution_name.';'.$row['方案名称'];
            //     $curPbind->solution = $curPbind->solution.';'.$row['方案下载地址'];
            //     $curPbind->save();
            // }
           
        }
        
    }

    public function bools($value){
        if($value == '是'){return 1;}
        elseif($value == '否'){return 0;}
    }

}