<?php

namespace App\Admin\Controllers;

use App\Models\Chip;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\Status;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SbindController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Sbind::with(['softwares','releases','chips','solutions','statuses']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('softwares.name');
            $grid->column('releases.name');
            $grid->column('chips.name');
            $grid->column('statuses.name');
            $grid->column('softname');
            $grid->column('crossover');
            $grid->column('box86');
            $grid->column('appstore');
            $grid->column('filename');
            $grid->column('source');
            $grid->column('kernel_version');
            $grid->column('kernel_test');
            $grid->column('apptype');
            $grid->column('class');
            $grid->column('kylineco');
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
        return Show::make($id, Sbind::with(['softwares','releases','chips','solutions','statuses']), function (Show $show) {
            $show->field('id');
            $show->field('softwares.name');
            $show->field('releases.name');
            $show->field('chips.name');
            $show->field('statuses.name');
            $show->field('softname');
            $show->field('crossover');
            $show->field('box86');
            $show->field('appstore');
            $show->field('filename');
            $show->field('source');
            $show->field('kernel_version');
            $show->field('kernel_test');
            $show->field('apptype');
            $show->field('class');
            $show->field('kylineco');
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
        return Form::make(Sbind::with(['softwares','releases','chips','solutions','statuses']), function (Form $form) {
            $form->display('id');
            $form->select('softwares_id',__('型号'))->options(Software::all()->pluck('name','id'));
            $form->select('releases_id',__('版本'))->options(Release::all()->pluck('name','id'));
            $form->select('chips_id',__('芯片'))->options(Chip::all()->pluck('name','id'));
            $form->select('statuses_id',__('状态'))->options(Status::where('parent','!=',null)->pluck('name','id'));
            $form->text('softname');
            $form->text('crossover');
            $form->text('box86');
            $form->text('appstore');
            $form->text('filename');
            $form->text('source');
            $form->text('kernel_version');
            $form->text('kernel_test');
            $form->text('apptype');
            $form->text('class');
            $form->text('kylineco');
            $form->text('comment');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
