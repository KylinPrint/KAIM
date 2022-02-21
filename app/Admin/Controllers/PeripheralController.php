<?php

namespace App\Admin\Controllers;

use App\Models\Peripheral;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PeripheralController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Peripheral(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('brands_id');
            $grid->column('types_id');
            $grid->column('release_date');
            $grid->column('eosl_date');
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
        return Show::make($id, new Peripheral(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('brands_id');
            $show->field('types_id');
            $show->field('release_date');
            $show->field('eosl_date');
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
        return Form::make(new Peripheral(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('brands_id');
            $form->text('types_id');
            $form->text('release_date');
            $form->text('eosl_date');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
