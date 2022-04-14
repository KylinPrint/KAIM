<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\PRequest;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\SRequest;
use App\Models\Status;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('我的待办')
            ->body($this->grid())
            ->breadcrumb('我的待办');
    }

    protected function grid()
    {
        //考虑用权限控制数据来源
        return new Grid(null, function (Grid $grid) {
            $grid->model()->setData($this->data());
            $grid->model()->orderBy('updated_at');

            $grid->column('type');
            $grid->column('manufactor');
            $grid->column('name');
            $grid->column('release');
            $grid->column('chip');
            $grid->column('status');
            $grid->updated_at()->sortable();
            $grid->column('action')->display(function () {
                if     ($this->type == "软件适配") { $type = 'sbinds'; }
                elseif ($this->type == "外设适配") { $type = 'pbinds'; }
                elseif ($this->type == "软件需求") { $type = 'srequests'; }
                elseif ($this->type == "外设需求") { $type = 'prequests'; }
                $href = admin_url($type.'/'.$this->id);
                return "
                    <button data-action='refresh' class='btn btn-primary btn-outline'>
                        <a href='$href'> 去处理</a>
                    </button>
                ";
            });

            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableBatchActions();
            $grid->disablePagination();
        });
    }

    protected function data()
    {
        $data = [];

        $sbinds = Sbind::select('id', 'softwares_id',   'releases_id', 'chips_id', 'statuses_id', 'updated_at')->where('admin_users_id', Admin::user()->id)->get()->toarray();
        $pbinds = Pbind::select('id', 'peripherals_id', 'releases_id', 'chips_id', 'statuses_id', 'updated_at')->where('admin_users_id', Admin::user()->id)->get()->toarray();
        $srequests = SRequest::select('id', 'manufactor', 'name', 'release_id', 'chip_id', 'status', 'updated_at')->where('bd_id', Admin::user()->id)->get()->toarray();
        $prequests = PRequest::select('id', 'manufactor', 'name', 'release_id', 'chip_id', 'status', 'updated_at')->where('bd_id', Admin::user()->id)->get()->toarray();

        foreach ($sbinds as $sbind) {
            $software = Software::find($sbind['softwares_id']);
            $data[] = [
                'id' => $sbind['id'],
                'type' => '软件适配',
                'manufactor' => Manufactor::find($software->value('manufactors_id'))->value('name'),
                'name' => $software->value('name'),
                'release' => Release::find($sbind['releases_id'])->value('name'),
                'chip' => Chip::find($sbind['chips_id'])->value('name'),
                'status' => Status::find($sbind['statuses_id'])->value('name'),
                'updated_at' => $sbind['updated_at'],
            ];
        }

        foreach ($pbinds as $pbind) {
            $peripheral = Peripheral::find($pbind['peripherals_id']);
            $data[] = [
                'id' => $pbind['id'],
                'type' => '外设适配',
                // 外设的厂商键实际值为品牌
                'manufactor' => Brand::find($peripheral->value('brands_id'))->value('name'),
                'name' => $peripheral->value('name'),
                'release' => Release::find($pbind['releases_id'])->value('name'),
                'chip' => Chip::find($pbind['chips_id'])->value('name'),
                'status' => Status::find($pbind['statuses_id'])->value('name'),
                'updated_at' => $pbind['updated_at'],
            ];
        }

        foreach ($srequests as $srequest) {
            $data[] = [
                'id' => $srequest['id'],
                'type' => '软件需求',
                'manufactor' => $srequest['manufactor'],
                'name' => $srequest['name'],
                'release' => Release::find($srequest['release_id'])->value('name'),
                'chip' => Chip::find($srequest['chip_id'])->value('name'),
                'status' => $srequest['status'],
                'updated_at' => $srequest['updated_at'],
            ];
        }

        foreach ($prequests as $prequest) {
            $data[] = [
                'id' => $prequest['id'],
                'type' => '外设需求',
                'manufactor' => $prequest['manufactor'],
                'name' => $prequest['name'],
                'release' => Release::find($prequest['release_id'])->value('name'),
                'chip' => Chip::find($prequest['chip_id'])->value('name'),
                'status' => $prequest['status'],
                'updated_at' => $prequest['updated_at'],
            ];
        }
        
        return $data;
    }
}
