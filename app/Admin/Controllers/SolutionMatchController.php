<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Modal\SolutionMatchModal;
use App\Admin\Actions\Others\SolutionMatchDownload;
use App\Models\SolutionMatch;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SolutionMatchController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid 
     */
    protected function grid()
    {
        return Grid::make(new SolutionMatch(), function (Grid $grid) {

            $grid->model()->orderBy('created_at');

            $grid->disableCreateButton();
            
            $grid->tools(function (Grid\Tools $tools) { 
                //Solution匹配
                $tools->append(new SolutionMatchModal());

            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableEdit();
                $actions->disableQuickEdit();

                $actions->append(new SolutionMatchDownload());
                          
            });

            // $grid->column('id')->sortable();
            $grid->column('title');
            $grid->column('path');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            
            $grid->quickSearch('title','path');
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
        return Show::make($id, new SolutionMatch(), function (Show $show) {
            // $show->field('id');
            $show->field('title');
            $show->field('path');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SolutionMatch(), function (Form $form) {
            // $form->display('id');
            // $form->text('title');
            // $form->text('path');
        
            // $form->display('created_at');
            // $form->display('updated_at');

        });
    }
}
