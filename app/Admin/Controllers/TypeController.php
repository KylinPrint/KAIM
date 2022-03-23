<?php

namespace App\Admin\Controllers;

use App\Models\Type;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class TypeController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Type(), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->column('parent')->display(function($parent){
                return Type::where('id',$parent)->pluck('name')->first();
            });
            $grid->column('name');
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
        return Show::make($id, new Type(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('parent');
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
        return Form::make(new Type(), function (Form $form) {
            $form->display('id');

            if($form->isEditing())
            {
                $form->select('parent')->options(Type::where('parent','=',null)->pluck('name','id'))->load('name','/api/type');
                $form->select('name');
            }
            else
            {
                $form->select('parent')->options(Type::where('parent','=',null)->pluck('name','id'));
                $form->text('name');
            }          

        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    public function getName(Request $request)
    {
        $provinceId = $request->get('q')?:0;
        return Type::where('parent', $provinceId)->get(['id', \Illuminate\Support\Facades\DB::raw('name as text')])->toArray();
        
    }

}
