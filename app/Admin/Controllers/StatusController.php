<?php

namespace App\Admin\Controllers;

use App\Models\Status;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class StatusController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Status(), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('parent')->display(function($parent){
                return Status::where('id',$parent)->pluck('name')->first();
            });
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
        
            $grid->quickSearch('name');
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
        return Show::make($id, new Status(), function (Show $show) {
            // $show->field('id');
            $show->field('name');
            $show->field('parent');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Status(), function (Form $form) {
            // $form->display('id');
            $form->select('parent')
            ->options(Status::where('parent','=',null)
            ->pluck('name','id'))
            #->load('name','/api/status')
            ;
            $form->text('name')->required();
        });

    }

    // public function getName(Request $request)
    // {
    //     $provinceId = $request->get('q')?:0;
    //     return Status::where('parent', $provinceId)->get(['id', \Illuminate\Support\Facades\DB::raw('name as text')])->toArray();
        
    // }
}
