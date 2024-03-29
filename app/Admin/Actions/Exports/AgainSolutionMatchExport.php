<?php

namespace App\Admin\Actions\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\Stype;
use App\Models\Type;

use function PHPUnit\Framework\isEmpty;

class AgainSolutionMatchExport implements FromCollection, WithHeadings
{

    use Exportable;


    public function __construct($array,$file)
    {
        $time_start = $this->getmicrotime();
        $curMatchArr = $this->WTF($array);
        $time_end = $this->getmicrotime();
        $time = $time_end - $time_start;

        $this->data = $curMatchArr;

        $this->headings = [
            '分类1',
            '分类2',
            '厂商',
            '品牌',
            '产品名称' ,
            '系统版本' ,
            '系统架构' ,
            '解决方案名' ,
            '解决方案详情' ,
            '适配状态',
        ];
        $this->file = $file;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings():array
    {
        return $this->headings;
    }

    // public function export()
    // {
    //     $this->download($this->file)->prepare(request())->send();
    //     exit;
    // }

    public function getmicrotime()
    {
        list($usec,$sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }


    public function WTF($array){
        $i = 0;
        foreach($array as $curInput)
        {   
            $curMatchArr[$i] = 
            [
                '分类1' => $curInput['分类1'],  //软件、外设
                '分类2' => $curInput['分类2'],  //具体分类  打印机、扫描仪
                '厂商' => $curInput['厂商'],
                '品牌' => $curInput['品牌'],
                '产品名称' => $curInput['产品名称'],
                '系统版本' => $curInput['系统版本'],
                '芯片' => $curInput['芯片'],
                '解决方案名' => '暂无适配方案',
                '解决方案详情' => null,
                '适配状态' => '未适配',
            ];

            

            if($curInput['分类1'] == '外设')
            {

                if(empty($curInput['品牌'])){
                    $curMatchArr[$i]['解决方案详情'] = '请填写产品品牌';
                    ++$i;
                    continue;
                }
    
                $curBrandId = (Brand::where('name','like','%'.$curInput['品牌'].'%')->pluck('id')->first())?:(Brand::where('alias',$curInput['品牌'])->pluck('id')->first());
                
                
                if(empty($curBrandId)){
                    $curMatchArr[$i]['解决方案详情'] = '请核实产品品牌或添加新品牌';
                    ++$i;
                    continue;
                }

                $curPeripheralId = Peripheral::where([
                    ['name','=',$curInput['产品名称']],
                    ['brands_id',$curBrandId],])
                ->pluck('id')
                ->first();

                $curReleaseId = Release::where('name',$curInput['系统版本'])->pluck('id')->first();
                $curChipId = Chip::where('name',$curInput['芯片'])->pluck('id')->first();
    
                if($curPeripheralId)
                {
                    $curPbind = Pbind::where([
                        ['peripherals_id','=',$curPeripheralId],
                        ['releases_id',$curReleaseId],
                        ['chips_id',$curChipId],])
                    ->with('peripherals','releases','chips','statuses')
                    ->first();           
                    
                    if($curPbind)
                    { 
                        $curPeripheral = Peripheral::with('types')->find($curPeripheralId);

                        $curMatchArr[$i]['分类2'] = $curPeripheral->types->name;
                        $curMatchArr[$i]['解决方案名'] = $curPbind->solution_name;
                        $curMatchArr[$i]['解决方案详情'] = $curPbind->solution;
                        $curPstatus_parent = $curPbind->statuses->parent;
                        $curPstatus_id = $curPbind->statuses->id;
                        $curPstatus_sid = $curPstatus_parent == 0 ? $curPstatus_id : $curPstatus_parent;
                        $curMatchArr[$i]['适配状态'] = $this->getParent($curPstatus_sid);
                    }
                }

                if($curMatchArr[$i]['解决方案名'] == '暂无适配方案')
                {
                    $curPbind = Pbind::where([
                        ['peripherals_id','=',$curPeripheralId],
                        ['solution_name','like','%'.'系统集成'.'%'],])
                    ->with('peripherals','releases','chips','statuses')
                    ->first();

                    if($curPbind)
                    {
                        $curPeripheral = Peripheral::with('types')->find($curPeripheralId);

                        $curMatchArr[$i]['分类2'] = $curPeripheral->types->name;
                        $curMatchArr[$i]['解决方案名'] = $curPbind->solution_name;
                        $curMatchArr[$i]['解决方案详情'] = '已匹配'.' '.$curPbind->releases->name.' '.$curPbind->chips->name.' 上系统集成方案：'.$curPbind->solution;
                        $curMatchArr[$i]['适配状态'] = '待验证';
                    }
                    else
                    {
                        $curMatchArr[$i]['解决方案详情'] = '暂无该型号记录,或核实型号后重新上传';
                    }
                    
                }      
                ++$i;
            }
            
            if($curInput['分类1'] == '软件')
            {
                if(empty($curInput['厂商'])){
                    $curMatchArr[$i]['匹配型号结果'] = '请填写软件厂商';
                    ++$i;
                    continue;
                }
    
                $curManufactorId = (Manufactor::where('name',$curInput['厂商'])->pluck('id')->first());
                $curStypeId = Stype::where('name',$curInput['分类2'])->pluck('id')->first();
                
                if(empty($curManufactorId)){
                    $curMatchArr[$i]['匹配型号结果'] = '请核实软件厂商或添加新厂商';
                    ++$i;
                    continue;
                }

                $curSoftwareId = Software::where([
                    ['name','=',$curInput['产品名称']],
                    ['manufactors_id',$curManufactorId],
                    ['stypes_id',$curStypeId],])
                ->pluck('id')
                ->first();

                $curReleaseId = Release::where('name',$curInput['系统版本'])->pluck('id')->first();
                $curChipId = Chip::where('name',$curInput['芯片'])->pluck('id')->first();
    
                if($curSoftwareId)
                {
                    $curSbind = Sbind::where([
                        ['softwares_id','=',$curSoftwareId],
                        ['releases_id',$curReleaseId],
                        ['chips_id',$curChipId],])
                    ->with('softwares','releases','chips','statuses')
                    ->first();

                    $curSoftware = Software::with('stypes')->find($curSoftwareId);
                    
                    if($curSbind)
                    {
                        $curMatchArr[$i]['分类2'] = $curSoftware->stypes->name;
                        $curMatchArr[$i]['解决方案名'] = $curSbind->solution_name;
                        $curMatchArr[$i]['解决方案详情'] = $curSbind->solution;
                        $curSstatus_parent = $curSbind->statuses->parent;
                        $curSstatus_id = $curSbind->statuses->id;
                        $curSstatus_sid = $curSstatus_parent == 0 ? $curSstatus_id : $curSstatus_parent;
                        $curMatchArr[$i]['适配状态'] = $this->getParent($curSstatus_sid);
                    }
                }
                else
                {
                    $curMatchArr[$i]['解决方案详情'] = '暂无该型号记录,或核实型号后重新上传';
                }      
                ++$i;
            }

        }
        return $curMatchArr;
    }

    public function getParent($sid)
    {
        switch($sid)
        {
            case 1:
                return '未适配';
            case 2:
                return '适配中';
            case 3:
                return '已适配'; 
            case 4:
                return '待验证';
            case 5:
                return '适配暂停';   
        }
    }
}