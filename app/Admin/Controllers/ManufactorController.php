<?php

namespace App\Admin\Controllers;

use App\Admin\Utils\ContextMenuWash;
use App\Models\Manufactor;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ManufactorController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        // 恶人还需恶人磨
        ContextMenuWash::wash();

        return Grid::make(new Manufactor(), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('isconnected')->display(function ($isconnected) {
                if     ($isconnected == '1') { return '是'; }
                elseif ($isconnected == '0') { return '否'; }
            });
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
        
            $grid->quickSearch('name');
            $grid->selector(function (Grid\Tools\Selector $selector) {
                $selector->selectOne('isconnected', '是否建联', [0 => '否',1 => '是',]);
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Manufactor(), function (Show $show) {
            // $show->field('id');
            $show->field('name');
            $show->field('isconnected');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Manufactor(), function (Form $form) {
            // $form->display('id');
            $form->text('name')->required();
            $form->select('isconnected')->options([0 => '否',1 => '是']);
        });
    }
}
