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
            // $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('types.name', __('外设分类'));
            $grid->column('isrequired')->display(function ($isrequired) {
                return $isrequired ? '是' : '否';
            });
            $grid->column('field')->display(function ($field) {
                if ($field == 0) {
                    return '文本';
                }
                elseif ($field == 1) {
                    return '数字';
                }
                elseif ($field == 2) {
                    return '布尔';
                }
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
            // $show->field('id');
            $show->field('name');
            $show->field('types.name', __('外设分类'));
            $show->field('isrequired')->as(function ($isrequired) {
                return $isrequired ? '是' : '否';
            });
            $show->field('field')->as(function ($field) {
                if ($field == 0) {
                    return '文本';
                }
                elseif ($field == 1) {
                    return '数字';
                }
                elseif ($field == 2) {
                    return '布尔';
                }
            });
            // $show->field('created_at');
            // $show->field('updated_at');
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
            // $form->display('id');
            $form->text('name')->required();
            $form->select('types_id', __('外设分类'))->options(Type::where('parent', '!=', 0)->pluck('name','id'))->required();
            $form->select('isrequired')->options([0 => '否',1 => '是'])->required();
            $form->select('field')->options([
                0 => '文本',
                1 => '数字',
                2 => '布尔',
            ])->required();
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
