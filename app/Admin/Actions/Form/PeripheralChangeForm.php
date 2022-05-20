<?php

namespace App\Admin\Actions\Form;

use App\Admin\Actions\Imports\PeripheralChangeImport;
use App\Models\Brand;
use App\Models\Manufactor;
use App\Models\Peripheral;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use function PHPUnit\Framework\isEmpty;

ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '10M');

class PeripheralChangeForm extends Form
{
    public function handle(array $input)
    {
        try {
            // 提取上传文件内容
            $array = (Excel::toArray(new PeripheralChangeImport, storage_path('app/public/'.$input['file'])))[0];
            
            // 删除上传的临时文件
            Storage::disk('public')->delete($input['file']);

            foreach($array as $value){
                
                // 找原厂商id
                $curManufactorid = Manufactor::where('name',$value['原厂商'])->pluck('id')->first();
                
                // 找原品牌id
                
                if (preg_match('/\(|\（/', $value['原品牌'])) {
                    // 拆分中英文
                    preg_match('/(.+(?=\(|\（))/', trim($value['原品牌']), $brand_name);
                    preg_match('/(?<=\(|\（).+?(?=\)|\）)/', trim($value['原品牌']), $brand_name_en);
                    // 找
                    $curBrandid = Brand::where('name','like','%'.$brand_name[0].'%')
                                ->orWhere('name_en','%'.$brand_name_en[0].'%')
                                ->pluck('id')->first();
                } else {
                    // 抓中文
                    if (preg_match('/[\x7f-\xff]/', $value['原品牌'])) {
                        $brand_name = trim($value['原品牌']);
                        // 找
                        $curBrandid = Brand::where('name','like','%'.$brand_name.'%')->pluck('id')->first();
                    } else {
                        $brand_name_en = trim($value['原品牌']);
                        // 找
                        $curBrandid = Brand::where('name_en','%'.$brand_name_en.'%')->pluck('id')->first();
                    }
                }
                if(empty($curBrandid)){
                    return $this->response()->error('未找到原外设型号'.$value['原外设型号'].'的原品牌'.$value['原品牌'])->refresh();
                }
                
                // 找原外设id
                //      有厂商
                if(isEmpty($curManufactorid)){
                    $curPeripheralid = Peripheral::where([
                        ['name', $value['原外设型号']],
                        ['brands_id', $curBrandid],
                        ['manufactors_id', $curManufactorid]])
                        ->pluck('id')->first();
                }
                //      无厂商
                else{
                    $curPeripheralid = Peripheral::where([
                        ['name', $value['原外设型号']],
                        ['brands_id', $curBrandid]])
                        ->pluck('id')->first();
                }
                // 没找到
                if(empty($curPeripheralid)){
                    return $this->response()->error('未找到原外设型号'.$value['原外设型号'])->refresh();
                }
                // 找到了
                $curPeripheral = Peripheral::with('brands','manufactors')->find($curPeripheralid);

                // 修改品牌
                if(strlen(trim($value['修改品牌']))){
                    if (preg_match('/\(|\（/', $value['修改品牌'])) {
                        // 拆分中英文
                        preg_match('/(.+(?=\(|\（))/', trim($value['修改品牌']), $brand_name);
                        preg_match('/(?<=\(|\（).+?(?=\)|\）)/', trim($value['修改品牌']), $brand_name_en);
    
                        // 看下要改的库里有没有
                        $changeBrandid = Brand::where([
                            ['name',$brand_name[0]],
                            ['name_en',$brand_name_en[0]]
                        ])->pluck('id')->first();
                        
                        // 没有就改品牌名
                        if(empty($changeBrandid)){
                            if($curPeripheral->brands->name.$curPeripheral->brands->name_en != $brand_name[0].$brand_name_en[0]){
                                $curPeripheral->brands->name    = $brand_name[0];
                                $curPeripheral->brands->name_en = $brand_name_en[0];
                                $curPeripheral->brands->save();
                            }
                        // 有就该外设的品牌id
                        }else{
                            $curPeripheral->brands_id =  $changeBrandid;
                        }
                        
                    } else {
                        // 抓中文
                        if (preg_match('/[\x7f-\xff]/', $value['修改品牌'])) {
                            $brand_name = trim($value['修改品牌']);
    
                            $changeBrandid = Brand::where(['name',$brand_name])->pluck('id')->first();
    
                            // 看下要改的库里有没有 逻辑同上
                            if(empty($changeBrandid)){
                                if($curPeripheral->brands->name != $brand_name){
                                    $curPeripheral->brands->name    = $brand_name;
                                    $curPeripheral->brands->save();
                                }
                            }else{
                                $curPeripheral->brands_id =  $changeBrandid;
                            }
                            
                        } else {
                            $brand_name_en = trim($value['修改品牌']);
    
                            // 看下要改的库里有没有 逻辑同上
                            $changeBrandid = Brand::where(['name_en',$brand_name_en])->pluck('id')->first();
                            
                            if(empty($changeBrandid)){
                                if($curPeripheral->brands->name_en != $brand_name_en){
                                    $curPeripheral->brands->name_en    = $brand_name_en;
                                    $curPeripheral->brands->save();
                                }
                            }else{
                                $curPeripheral->brands_id =  $changeBrandid;
                            }     
                        }
                    }
                }   
                

                // 修改厂商
                if(strlen(trim($value['修改厂商']))){
                    if($curPeripheral->manufactors->name != $value['修改厂商'])
                    {
                        // 看下要改的库里有没有 逻辑同上
                        $changeManufactorid = Manufactor::where('name',$value['修改厂商'])->pluck('id')->first();
                        if(empty($changeManufactorid)){
                            $curPeripheral->manufactors->name = $value['修改厂商'];
                            $curPeripheral->manufactors->save();
                        }else{
                            $curPeripheral->manufactors_id =  $changeManufactorid;
                        }
                        
                    }
                }
                
                
                //修改外设名
                if(strlen(trim($value['修改外设型号']))){
                    if($curPeripheral->name != $value['修改外设型号'])
                    {
                        $curPeripheral->name = $value['修改外设型号'];
                    }
                }
                

                $curPeripheral->save();
                
            }
            
            return $this->response()->success('数据导入成功')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function form()
    {
        $this->file('file', '上传数据(Excel)')
            ->autoUpload()
            ->rules('required', ['required' => '文件不能为空'])
            ->move('admin/upload')
            ->help('<a href="/template/pc_import.xlsx" target="_blank">点击此处</a>下载导入模板');
        $this->disableResetButton();
    }

}