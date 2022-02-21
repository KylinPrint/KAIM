<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Value;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ValueController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Value(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('peripherals_id');
            $grid->column('sepcifications_id');
            $grid->column('value');
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
        return Show::make($id, new Value(), function (Show $show) {
            $show->field('id');
            $show->field('peripherals_id');
            $show->field('sepcifications_id');
            $show->field('value');
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
        return Form::make(new Value(), function (Form $form) {
            $form->display('id');
            $form->text('peripherals_id');
            $form->text('sepcifications_id');
            $form->text('value');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
