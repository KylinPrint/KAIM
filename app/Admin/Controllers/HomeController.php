<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\DataAdd;
use App\Admin\Metrics\Examples;
use App\Http\Controllers\Controller;
use App\Models\Pbind;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\Dashboard;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('数据统计')
            ->body(function (Row $row) {
                $row->column(4, new DataAdd());
                // $row->column(6, function (Column $column) {
                //     $column->row(Dashboard::title());
                //     $column->row(new Examples\Tickets());
                // });

                // $row->column(6, function (Column $column) {
                //     $column->row(function (Row $row) {
                //         $row->column(6, new Examples\NewUsers());
                //         $row->column(6, new Examples\NewDevices());
                //     });

                //     $column->row(new Examples\Sessions());
                //     $column->row(new Examples\ProductOrders());
                // });
            })
            ->body($this->grid());
    }

    protected function grid()
    {
        //考虑用权限控制数据来源
        return Grid::make(Pbind::with(['peripherals','releases','chips','statuses','admin_users']), function (Grid $grid) {
            $grid->title('我的待办');
            
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);

            $grid->model()->where('admin_users_id', '=', Admin::user()->id);
            $grid->model()->orderBy('updated_at', 'desc');

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
