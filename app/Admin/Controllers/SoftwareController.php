<?php

namespace App\Admin\Controllers;

use App\Models\Manufactor;
use App\Models\Software;
use App\Models\Stype;
use App\Models\Type;
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
        return Grid::make(Software::with('manufactors','types'), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('manufactors.name',__('厂商'));
            $grid->column('version');
            $grid->column('packagename');
            $grid->column('types.name',__('类型'));
            $grid->column('kernel_version');
            $grid->column('crossover_version');
            $grid->column('box86_version');
            $grid->column('bd');
            $grid->column('am');
            $grid->column('tsm');
            $grid->column('comment')->limit(50);
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
            $show->field('kernel_version');
            $show->field('crossover_version');
            $show->field('box86_version');
            $show->field('bd');
            $show->field('am');
            $show->field('tsm');
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
        return Form::make(Software::with('manufactors','types'), function (Form $form) {
            $form->display('id');
            $form->text('name')->required();
            $form->select('manufactors_id')->options(Manufactor::all()->pluck('name','id'))->required();
            $form->text('version')->required();
            $form->text('packagename');
            $form->select('types_id', __('类型'))->options(Stype::where('parent','!=',null)->pluck('name','id'))->required();    
            $form->text('kernel_version');
            $form->text('crossover_version');
            $form->text('box86_version');
            $form->text('bd')->required();
            $form->text('am');
            $form->text('tsm');
            $form->text('comment');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
