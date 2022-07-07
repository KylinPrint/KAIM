<?php

namespace App\Admin\Actions\Exports;

use App\Models\AdminUser;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\Status;
use App\Models\Stype;
use Dcat\Admin\Admin;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use OwenIt\Auditing\Models\Audit;

class SbindTemplateExport implements FromCollection, ShouldAutoSize
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
            '软件名称',
            '软件版本号',
            '包名',
            '软件分类一',
            '软件分类二',
            '行业分类',
            '内核引用',
            '引用版本',
            'Crossover版本',
            'Box86版本',
            '生态负责人',
            '适配负责人',
            '技术支撑负责人',
            '软件描述',
            '操作系统版本',
            '操作系统小版本',
            '芯片',
            '架构',
            '引入来源',
            '是否适配过国产CPU',
            '当前适配状态',
            '当前细分适配状态',
            '当前适配状态责任人',
            '安装包名称',
            '安装包下载地址',
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
            $curSoftwareArr = Software::with('stypes','manufactors')->find($v->softwares_id);

            $dataProces['厂商'] = $curSoftwareArr->manufactors->name;
            $dataProces['软件名称'] = $curSoftwareArr->name;
            $dataProces['软件版本号'] = $curSoftwareArr->version;
            $dataProces['包名'] = $curSoftwareArr->package_name;
            $dataProces['软件分类一'] = Stype::where('id',$curSoftwareArr->stypes->parent)->pluck('name')->first();
            $dataProces['软件分类二'] = $curSoftwareArr->stypes->name;
            $dataProces['行业分类'] = $curSoftwareArr->industries;;
            $dataProces['内核引用'] = $curSoftwareArr->kernel_version ? '是' : '否';
            $dataProces['引用版本'] = $curSoftwareArr->kernel_version;
            $dataProces['Crossover版本'] = $curSoftwareArr->crossover_version;
            $dataProces['Box86版本'] = $curSoftwareArr->box86_version;
            $dataProces['生态负责人'] = $curSoftwareArr->bd;
            $dataProces['适配负责人'] = $curSoftwareArr->am;
            $dataProces['技术支撑负责人'] = $curSoftwareArr->tsm;
            $dataProces['软件描述'] = $curSoftwareArr->comment;
            $dataProces['操作系统版本'] = $v->releases->name;
            $dataProces['操作系统小版本'] = $v->os_subversion;
            $dataProces['芯片'] = $v->chips->name;
            $dataProces['架构'] = $v->chips->arch;
            $dataProces['引入来源'] = $v->adapt_source;
            $dataProces['是否适配过国产CPU'] = $this->bools($v->adapted_before);
            $dataProces['当前适配状态'] =  $this->getParent($v->statuses->parent);
            $dataProces['当前细分适配状态'] = $v->statuses->name;
            $dataProces['当前适配状态责任人'] = AdminUser::where('id',$v->admin_user_id)->pluck('name')->first();
            $dataProces['安装包名称'] = $v->solution_name;
            $dataProces['安装包下载地址'] = $v->solution;
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

        $data = Sbind::with('chips');

        if(isset($curFilter['sname'])){
            $a = $curFilter['sname'];
            $data = $data->whereHas('softwares', function ($query) use ($a){
                $query->where('name', 'like','%'.$a.'%');
            });
        }

        if(isset($curFilter['manufactor'])){
            $a = $curFilter['manufactor'];
            $data = $data->whereHas('peripherals', function ($query) use ($a){
                $query->whereHas('manufactors', function ($query) use ($a){
                    $query->where('name', 'like',"%{$a}%")
                        ->orWhere('name_en','like',"%{$a}%");
                });
            });
        }

        if(isset($curFilter['solution'])){
            $data = $data->where('solution','like','%'.$curFilter['solution'].'%');
        }

        if(isset($curFilter['sbind'])){
            $a = $curFilter['sbind'];
            $data = $data->whereHas('softwares', function ($query) use ($a){
                $query->whereHas('stypes', function ($query) use ($a){
                    if(Stype::where('id',$a)->pluck('parent')->first() != 0){$query->where('id', $a);}
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
