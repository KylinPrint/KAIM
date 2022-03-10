<?php

namespace App\Admin\Controllers;

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
        return Grid::make(new Manufactor(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('isconnected')->display(function ($isconnected) {
                if     ($isconnected == '0')  { return '否'; }
                elseif ($isconnected == '1') { return '是'; }
                else                             { return '否'; }
            });
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
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
            $show->field('id');
            $show->field('name');
            $show->field('isconnected');
            $show->field('created_at');
            $show->field('updated_at');
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
            $form->display('id');
            $form->text('name');
            $form->select('isconnected')->options([0 => '否',1 => '是']);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
