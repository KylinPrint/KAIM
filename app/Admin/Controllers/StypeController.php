<?php

namespace App\Admin\Controllers;

use App\Admin\Utils\ContextMenuWash;
use App\Models\Stype;
use Dcat\Admin\Form;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Tree;

class StypeController extends AdminController
{
    public function index(Content $content)
    {
        return $content->header('软件分类管理')
            ->body(function (Row $row) {
                $tree = new Tree(new Stype);

                $tree->branch(function ($branch) {
                    return "{$branch['name']}";
                });

                $row->column(12, $tree);
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
            // $form->display('id');
            $typeModel = config('admin.database.types_model');

            $form->select('parent',)
                ->options($typeModel::selectOptions())
                ->saving(function ($v) {
                    return (int) $v;
                });
            $form->text('name')->required(); 
        });
    }
}
