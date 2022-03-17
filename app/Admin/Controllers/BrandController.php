<?php

namespace App\Admin\Controllers;

use App\Models\Brand;
use App\Models\Manufactor;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BrandController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Brand::with(['manufactors']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('manufactors.name', __("厂商"));
            $grid->column('name');
            $grid->column('alias');
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
        return Show::make($id, Brand::with(['manufactors']), function (Show $show) {
            $show->field('id');
            $show->field('manufactors.name', __("厂商"));
            $show->field('name');
            $show->field('alias');
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
        return Form::make(Brand::with(['manufactors']), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('alias');
            $form->select('manufactors_id', __('厂商'))->options(Manufactor::all()->pluck('name','id'));
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
