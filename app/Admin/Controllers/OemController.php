<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\OemExport;
use App\Admin\Actions\Modal\OemModal;
use App\Models\Oem;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class OemController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Oem(), function (Grid $grid) {

            $grid->tools(function  (Grid\Tools  $tools)  { 
                if(Admin::user()->can('oems-import'))
                {
                    $tools->append(new OemModal()); 
                }
            });

            if(Admin::user()->can('oems-export'))
            {
                $grid->export(new OemExport());
            }

            $grid->column('id')->sortable();
            $grid->column('manufactor_id');
            $grid->column('name');
            $grid->column('type_id');
            $grid->column('source');
            $grid->column('details');
            $grid->column('release_id');
            $grid->column('os_subversion');
            $grid->column('chip_id');
            $grid->column('status_id');
            $grid->column('user_name');
            $grid->column('class');
            $grid->column('test_type');
            $grid->column('kylineco');
            $grid->column('iscert');
            $grid->column('patch');
            $grid->column('start_time');
            $grid->column('complete_time');
            $grid->column('motherboard');
            $grid->column('gpu');
            $grid->column('graphic_card');
            $grid->column('ai_card');
            $grid->column('network');
            $grid->column('memory');
            $grid->column('raid');
            $grid->column('hba');
            $grid->column('hard_disk');
            $grid->column('firmware');
            $grid->column('sound_card');
            $grid->column('parallel');
            $grid->column('serial');
            $grid->column('isolation_card');
            $grid->column('other_card');
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
        return Show::make($id, new Oem(), function (Show $show) {
            $show->field('id');
            $show->field('manufactor_id');
            $show->field('name');
            $show->field('type_id');
            $show->field('source');
            $show->field('details');
            $show->field('release_id');
            $show->field('os_subversion');
            $show->field('chip_id');
            $show->field('status_id');
            $show->field('user_name');
            $show->field('class');
            $show->field('test_type');
            $show->field('kylineco');
            $show->field('iscert');
            $show->field('patch');
            $show->field('start_time');
            $show->field('complete_time');
            $show->field('motherboard');
            $show->field('gpu');
            $show->field('graphic_card');
            $show->field('ai_card');
            $show->field('network');
            $show->field('memory');
            $show->field('raid');
            $show->field('hba');
            $show->field('hard_disk');
            $show->field('firmware');
            $show->field('sound_card');
            $show->field('parallel');
            $show->field('serial');
            $show->field('isolation_card');
            $show->field('other_card');
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
        return Form::make(new Oem(), function (Form $form) {
            $form->display('id');
            $form->text('manufactor_id');
            $form->text('name');
            $form->text('type_id');
            $form->text('source');
            $form->text('details');
            $form->text('release_id');
            $form->text('os_subversion');
            $form->text('chip_id');
            $form->text('status_id');
            $form->text('user_name');
            $form->text('class');
            $form->text('test_type');
            $form->text('kylineco');
            $form->text('iscert');
            $form->text('patch');
            $form->text('start_time');
            $form->text('complete_time');
            $form->text('motherboard');
            $form->text('gpu');
            $form->text('graphic_card');
            $form->text('ai_card');
            $form->text('network');
            $form->text('memory');
            $form->text('raid');
            $form->text('hba');
            $form->text('hard_disk');
            $form->text('firmware');
            $form->text('sound_card');
            $form->text('parallel');
            $form->text('serial');
            $form->text('isolation_card');
            $form->text('other_card');
            $form->text('comment');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
