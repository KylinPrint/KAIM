<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pbind;
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
        return Grid::make(Pbind::with(['peripherals','releases','chips','statuses','admin_users']), function (Grid $grid) {
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);

            $grid->model()->where('admin_users_id', '=', Admin::user()->id);
            $grid->model()->orderBy('updated_at');

            $grid->column('peripherals.name',__('外设型号'));
            $grid->column('releases.name',__('操作系统版本'));
            $grid->column('chips.name',__('芯片'));
            $grid->column('statuses.name',__('适配状态'));
            $grid->updated_at()->sortable();

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
        });
    }
}
