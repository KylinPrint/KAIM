<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Modal\SolutionMatchModal;
use App\Admin\Actions\Others\SolutionMatchDownload;
use App\Models\SolutionMatch;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Storage;

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
            $grid->model()->orderBy('created_at', 'desc');

            $grid->disableCreateButton();
            
            $grid->tools(function (Grid\Tools $tools) { 
                //Solution匹配
                $tools->append(new SolutionMatchModal());

            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableEdit();

                $actions->append(new SolutionMatchDownload());
                          
            });

            $grid->column('title');
            $grid->column('created_at');

            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
            
            $grid->quickSearch('title','path');
        });
    }

    protected function form()
    {
        return Form::make(new SolutionMatch(), function (Form $form) {
            // 同步删除文件
            $form->deleting(function (Form $form) {
                foreach ($form->model()->toArray() as $file)
                {
                    Storage::disk('public')->delete('solution-match/' . $file['title']);
                }
            });
        });
    }
}
