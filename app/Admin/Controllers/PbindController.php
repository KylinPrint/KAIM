<?php

namespace App\Admin\Controllers;

use App\Models\Pbind;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PbindController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Pbind(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('peripherals_id');
            $grid->column('releases_id');
            $grid->column('chips_id');
            $grid->column('solutions_id');
            $grid->column('status_id');
            $grid->column('class');
            $grid->column('comment');
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
        return Show::make($id, new Pbind(), function (Show $show) {
            $show->field('id');
            $show->field('peripherals_id');
            $show->field('releases_id');
            $show->field('chips_id');
            $show->field('solutions_id');
            $show->field('status_id');
            $show->field('class');
            $show->field('comment');
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
        return Form::make(new Pbind(), function (Form $form) {
            $form->display('id');
            $form->text('peripherals_id');
            $form->text('releases_id');
            $form->text('chips_id');
            $form->text('solutions_id');
            $form->text('status_id');
            $form->text('class');
            $form->text('comment');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
