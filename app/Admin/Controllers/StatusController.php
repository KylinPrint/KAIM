<?php

namespace App\Admin\Controllers;

use App\Admin\Utils\ContextMenuWash;
use App\Models\Status;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Tree;

class StatusController extends AdminController
{
    
    public function index(Content $content)
    {
        return $content->header('适配状态分类管理')
            ->body(function (Row $row) {
                $tree = new Tree(new Status());

                $tree->branch(function ($branch) {
                    return "{$branch['name']}";
                });

                $row->column(12, $tree);
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
            ->options(Status::where('parent',0)
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
