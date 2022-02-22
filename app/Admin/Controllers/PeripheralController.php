<?php

namespace App\Admin\Controllers;

use App\Models\Brand;
use App\Models\Peripheral;
use App\Models\Type;
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
        return Grid::make(Peripheral::with(['brands','types']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('brands.name', __('品牌'));
            $grid->column('types.name', __('类型'));
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
        return Show::make($id, Peripheral::with(['brands','types']), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('brands.name', __('品牌'));
            $show->field('types.name', __('类型'));
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
        return Form::make(Peripheral::with(['brands', 'types']), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->select('brands_id', __('品牌'))->options(Brand::all()->pluck('name','id'));
            $form->select('types_id', __('类型'))->options(Type::where('parent','!=',null)->pluck('name','id'));
            $form->date('release_date')->format('YYYY-MM-DD');
            $form->date('eosl_date')->format('YYYY-MM-DD');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
