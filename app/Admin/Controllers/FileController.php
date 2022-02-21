<?php

namespace App\Admin\Controllers;

use App\Models\File;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class FileController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new File(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('path');
            $grid->column('solutions_id');
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
        return Show::make($id, new File(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('path');
            $show->field('solutions_id');
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
        return Form::make(new File(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('path');
            $form->text('solutions_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
