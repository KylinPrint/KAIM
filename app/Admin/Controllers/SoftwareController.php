<?php

namespace App\Admin\Controllers;

use App\Models\Software;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SoftwareController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Software(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('manufactors_id');
            $grid->column('version');
            $grid->column('types_id');
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
        return Show::make($id, new Software(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('manufactors_id');
            $show->field('version');
            $show->field('types_id');
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
        return Form::make(new Software(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('manufactors_id');
            $form->text('version');
            $form->text('types_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
