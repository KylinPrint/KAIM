<?php

namespace App\Admin\Controllers;

use App\Models\SRequest;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SRequestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SRequest(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('source');
            $grid->column('manufactor_id');
            $grid->column('name');
            $grid->column('stype_id');
            $grid->column('industry');
            $grid->column('release_id');
            $grid->column('chip_id');
            $grid->column('project_name');
            $grid->column('amount');
            $grid->column('project_status');
            $grid->column('level');
            $grid->column('manufactor_contact');
            $grid->column('et');
            $grid->column('requester_name');
            $grid->column('requester_contact');
            $grid->column('status');
            $grid->column('bd');
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
        return Show::make($id, new SRequest(), function (Show $show) {
            $show->field('id');
            $show->field('source');
            $show->field('manufactor_id');
            $show->field('name');
            $show->field('stype_id');
            $show->field('industry');
            $show->field('release_id');
            $show->field('chip_id');
            $show->field('project_name');
            $show->field('amount');
            $show->field('project_status');
            $show->field('level');
            $show->field('manufactor_contact');
            $show->field('et');
            $show->field('requester_name');
            $show->field('requester_contact');
            $show->field('status');
            $show->field('bd');
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
        return Form::make(new SRequest(), function (Form $form) {
            $form->display('id');
            $form->text('source');
            $form->text('manufactor_id');
            $form->text('name');
            $form->text('stype_id');
            $form->text('industry');
            $form->text('release_id');
            $form->text('chip_id');
            $form->text('project_name');
            $form->text('amount');
            $form->text('project_status');
            $form->text('level');
            $form->text('manufactor_contact');
            $form->text('et');
            $form->text('requester_name');
            $form->text('requester_contact');
            $form->text('status');
            $form->text('bd');
            $form->text('comment');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
