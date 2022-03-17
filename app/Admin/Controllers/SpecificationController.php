<?php

namespace App\Admin\Controllers;

use App\Models\Specification;
use App\Models\Type;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SpecificationController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Specification::with(['types']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('types.name', __('外设分类'));
            $grid->column('isrequired')->display(function ($isrequired) {
                return $isrequired ? '是' : '否';
            });
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
        return Show::make($id, Specification::with(['types']), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('types.name', __('外设分类'));
            $show->field('isrequired');
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
        return Form::make(Specification::with('types'), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->select('types_id', __('外设分类'))->options(Type::where('parent','!=',null)->pluck('name','id'));
            $form->select('isrequired')->options([0 => '否',1 => '是']);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
