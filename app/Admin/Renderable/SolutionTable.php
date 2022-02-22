<?php

namespace App\Admin\Renderable;


use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use App\Models\Solution;

class SolutionTable extends LazyRenderable
{

    public function grid(): Grid
    {
        return Grid::make(Solution::with(['peripherals']), function (Grid $grid) {
            
            $grid->setActionClass(Grid\Displayers\Actions::class);

            $grid->model()->where('printers_id', $this->id);

            $grid->column('solutions.name', __('解决方案名'));
        
            $grid->column('adapter',__('适配平台'))->center()->badge('default')->width('80px');
            $grid->column('auth','互认证状态')->display(function($auth){
                switch ($auth){
                    case 1:
                        return '<i class="fa fa-check text-green"></i>';
                        break;
                    case 2:
                        return '<i class="fa fa-close text-red"  ></i>';
                        break;
                    default:
                        return '<i class="fa fa-close text-red"  ></i>';
                }
            });
            $grid->column('solutions.detail',__('适配方案'))->limit(500)->center();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();     
                $actions->disableView();
                $curStr = '<a href = "/admin/binds/'.$actions->row['id'].'">详情</a>';
                $actions->append($curStr);

            });

            $grid->paginate(5);
            $grid->disableRefreshButton();
            $grid->disableCreateButton();

        });
    }
}