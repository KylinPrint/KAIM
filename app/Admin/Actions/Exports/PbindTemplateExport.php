<?php

namespace App\Admin\Actions\Exports;

use App\Models\AdminUser;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Status;
use App\Models\Type;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use OwenIt\Auditing\Models\Audit;

class PbindTemplateExport implements FromCollection, ShouldAutoSize
{

    private $data;

    /**
 *  
 * InspectionItemPostExport constructor.
 */
    public function __construct($cururl)
    {
        // 
        $this->cururl = $cururl;
        $this->data = $this->createData();
    }

    /**
     *  
    * @return Collection
    */
    public function collection()
    {
        return collect($this->data);
    }

    /**
     *  业务代码 
    * @return array|string[][]
    */
    public function createData()
    {
        $headTitle = [
            '厂商',
            '品牌',
            '外设型号',
            '外设类型一',
            '外设类型二',
            '行业分类',
            '发布日期',
            '服务终止日期',
            '外设描述',
            '操作系统版本',
            '操作系统小版本',
            '芯片',
            '架构',
            '引入来源',
            '是否适配过国产CPU',
            '当前适配状态',
            '当前细分适配状态',
            '当前适配状态责任人',
            '方案名称',
            '方案下载地址',
            '兼容等级',
            '适配类型',
            '测试方式',
            '是否上传生态网站',
            '是否上架软件商店',
            '是否互认证',
            '是否有测试报告',
            '证书编号',
            '适配开始时间',
            '适配完成时间',
            '备注',
        ];
        $dataProces = [];
        $dataArr = [];

        $data = $this->szb($this->cururl)->with('releases','chips','statuses')->get();
        
        if (empty($data)) {
            return [$headTitle];
        }
        
        // 数据循环
        foreach ($data as $k => $v) {
            $curPeripheralArr = Peripheral::with('brands','types','manufactors')->find($v->peripherals_id);
            if($curPeripheralArr->brands->name && $curPeripheralArr->brands->name_en){
                $brand_name = $curPeripheralArr->brands->name.'('.$curPeripheralArr->brands->name_en.')';
            }else{
                $brand_name = $curPeripheralArr->brands->name?:$curPeripheralArr->brands->name_en;
            }

            $dataProces['厂商'] = $curPeripheralArr->manufactors->name;;
            $dataProces['品牌'] = $brand_name;
            $dataProces['外设型号'] = $curPeripheralArr->name;
            $dataProces['外设类型一']   = Type::where('id',$curPeripheralArr->types->parent)->pluck('name')->first();;
            $dataProces['外设类型二']   = $curPeripheralArr->types->name;
            $dataProces['行业分类'] = $curPeripheralArr->industries;
            $dataProces['发布日期'] = $curPeripheralArr->release_date;
            $dataProces['服务终止日期'] = $curPeripheralArr->eosl_date;
            $dataProces['外设描述'] = $curPeripheralArr->comment;
            $dataProces['操作系统版本'] = $v->releases->name;
            $dataProces['操作系统小版本'] = $v->os_subversion;
            $dataProces['芯片'] = $v->chips->name;
            $dataProces['架构'] = $v->chips->arch;
            $dataProces['引入来源'] = $v->adapt_source;
            $dataProces['是否适配过国产CPU'] = $this->bools($v->adapted_before);
            $dataProces['当前适配状态'] = $this->getParent($v->statuses->parent);;
            $dataProces['当前细分适配状态'] = $v->statuses->name;
            $dataProces['当前适配状态责任人'] = AdminUser::where('id',$v->admin_user_id)->pluck('name')->first();
            $dataProces['方案名称'] = $v->solution_name;
            $dataProces['方案下载地址'] = $v->solution;
            $dataProces['兼容等级'] = $v->class;
            $dataProces['适配类型'] = $v->adaption_type;
            $dataProces['测试方式'] = $v->test_type;
            $dataProces['是否上传生态网站'] = $this->bools($v->kylineco);
            $dataProces['是否上架软件商店'] = $this->bools($v->appstore);
            $dataProces['是否互认证']   = $this->bools($v->iscert);
            $dataProces['是否有测试报告']   = $this->bools($v->test_report);
            $dataProces['证书编号'] = $v->certificate_NO;
            $dataProces['适配开始时间'] = $v->start_time;
            $dataProces['适配完成时间'] = $v->complete_time;
            $dataProces['备注'] = $v->comment;

            $dataArr[] = $dataProces;
        }
        
        unset($data);
        
        return [$headTitle, $dataArr];
    }

    /**
     * @return object
     */
    public function szb($cururl){
        
        $curUrl = urldecode($cururl);

        $curFilterOrigin = explode('&',$curUrl);
        $curFilter = [];

        foreach($curFilterOrigin as $v){
            $value = substr($v, strpos($v, "=") + 1);
            $key = substr($v, 0, strrpos($v, "="));
            if($value){
                $curFilter[$key] =  $value;
            }
        }

        $data = Pbind::with('chips');

        if(isset($curFilter['pname'])){
            $a = $curFilter['pname'];
            $data = $data->whereHas('peripherals', function ($query) use ($a){
                $query->where('name', 'like','%'.$a.'%');
            });
        }

        if(isset($curFilter['brand'])){
            $a = $curFilter['brand'];
            $data = $data->whereHas('peripherals', function ($query) use ($a){
                $query->whereHas('brands', function ($query) use ($a){
                    $query->where('name', 'like',"%{$a}%")
                        ->orWhere('name_en','like',"%{$a}%");
                });
            });
        }

        if(isset($curFilter['manufactor'])){
            $a = $curFilter['manufactor'];
            $data = $data->whereHas('peripherals', function ($query) use ($a){
                $query->whereHas('manufactors', function ($query) use ($a){
                    $query->where('name', 'like',"%{$a}%");
                });
            });
        }

        if(isset($curFilter['solution'])){
            $data = $data->where('solution','like','%'.$curFilter['solution'].'%');
        }

        if(isset($curFilter['pbind'])){
            $a = $curFilter['pbind'];
            $data = $data->whereHas('peripherals', function ($query) use ($a){
                $query->whereHas('types', function ($query) use ($a){
                    if(Type::where('id',$a)->pluck('parent')->first() != 0){$query->where('id', $a);}
                    elseif($a == 0){}
                    else{$query->where('parent', $a);}
                });
            });
        }

        if(isset($curFilter['releases[id]'])){
            $a = explode(',',$curFilter['releases[id]']);
            $data = $data->whereHas('releases', function ($query) use ($a){
                $query->whereIn('id',$a);
            });
        }

        if(isset($curFilter['chips[id]'])){
            $a = explode(',',$curFilter['chips[id]']);
            $data = $data->whereHas('chips', function ($query) use ($a){
                $query->whereIn('id',$a);
            });
        }

        if(isset($curFilter['status'])){
            $a = $curFilter['status'];
            $data = $data->whereHas('statuses', function ($query) use ($a){
                if(Status::where('id',$a)->pluck('parent')->first() != 0){$query->where('id', $a);}
                elseif($a == 0){}
                else{$query->where('parent', $a);}
            });
        }

        if(isset($curFilter['adaption_type'])){
            $data = $data->where('adaption_type',$curFilter['adaption_type']);
        }

        if(isset($curFilter['created_at[start]'])){
            $start = $curFilter['created_at[start]'];
            $data = $data->whereDate('created_at', '>=', $start);
        }

        if(isset($curFilter['created_at[end]'])){
            $start = $curFilter['created_at[end]'];
            $data = $data->whereDate('created_at', '<=', $start);
        }

        if(isset($curFilter['updated_at[start]'])){
            $start = $curFilter['updated_at[start]'];
            $data = $data->whereDate('updated_at', '>=', $start);
        }

        if(isset($curFilter['updated_at[end]'])){
            $start = $curFilter['updated_at[end]'];
            $data = $data->whereDate('updated_at', '<=', $start);
        }


        if(isset($curFilter['related'])){
            $a = $curFilter['related'];
            if($a == 1) {
                $data = $data->created();
            } 
            elseif($a == 2) {  
                $data = $data->related();
            }
            elseif($a == 3) {
                $data = $data->todo();
            }
        }

        if(isset($curFilter['appstore'])){
            $data = $data->where('appstore',$curFilter['appstore']);
        }

        if(isset($curFilter['iscert'])){
            $data = $data->where('iscert',$curFilter['iscert']);
        }

        return $data;

    }

    public function bools($v){
        if($v == 1){return '是';}
        elseif($v == 0){return '否';}
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
