<?php

namespace App\Admin\Controllers;

use App\Models\Stype;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class StypeController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Stype(), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('parent');
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
        return Show::make($id, new Stype(), function (Show $show) {
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
        return Form::make(new Stype(), function (Form $form) {
            $form->display('id');

            $form->text('name');
            if($form->isEditing())
            {
                $form->select('parent')->options(Stype::where('parent','=',null)->pluck('name','id'))->load('name','/api/stype');
                $form->select('name');
            }
            else
            {
                $form->select('parent')->options(Stype::where('parent','=',null)->pluck('name','id'));
                $form->text('name');
            }
        
            $form->display('created_at');
            $form->display('updated_at');
        });
        
    }
    public function getName(Request $request)
    {
        $provinceId = $request->get('q')?:0;
        return Stype::where('parent', $provinceId)->get(['id', \Illuminate\Support\Facades\DB::raw('name as text')])->toArray();
        
    }
}
