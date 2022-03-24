<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\Sbind;
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
            ->body($this->pGrid())
            ->body($this->sGrid())
            ->breadcrumb('我的待办');
    }

    protected function pGrid()
    {
        //考虑用权限控制数据来源
        return Grid::make(Pbind::with(['peripherals','releases','chips','statuses','admin_users']), function (Grid $grid) {
            $grid->title('外设');
            $grid->model()->where('admin_users_id', '=', Admin::user()->id);
            $grid->model()->orderBy('updated_at');

            $grid->column('peripherals.name',__('外设型号'));
            $grid->column('releases.name',__('操作系统版本'));
            $grid->column('chips.name',__('芯片'));
            $grid->column('statuses.parent', __('当前适配状态'))->display(function ($parent) {
                return Status::where('id', $parent)->pluck('name')->first();
            });
            $grid->column('statuses.name', __('当前细分适配状态'));
            $grid->updated_at()->sortable();

            $grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->disableActions();
            $grid->paginate(5);
        });
    }
    
    protected function sGrid()
    {
        //考虑用权限控制数据来源
        return Grid::make(Sbind::with('softwares','releases','chips','admin_users','statuses'), function (Grid $grid) {
            $grid->title('软件');
            $grid->model()->where('admin_users_id', '=', Admin::user()->id);
            $grid->model()->orderBy('updated_at');

            $grid->column('softwares.manufactors_id',__('厂商名称'))->display(function ($manufactors) {
                return Manufactor::where('id',$manufactors)->pluck('name')->first();
            });
            $grid->column('softwares.name',__('软件名'))->width('15%');
            $grid->column('releases.name',__('操作系统版本'))->width('15%');
            $grid->column('chips.name',__('芯片'));
            $grid->column('statuses.parent', __('当前适配状态'))->display(function ($parent) {
                return Status::where('id', $parent)->pluck('name')->first();
            });
            $grid->column('statuses.name', __('当前细分适配状态'));
            $grid->updated_at()->sortable();

            $grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->disableActions();
            $grid->paginate(5);
        });
    }
}
