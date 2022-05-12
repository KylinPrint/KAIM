<?php

namespace App\Admin\Controllers;

use App\Admin\Utils\ContextMenuWash;
use App\Models\Brand;
use App\Models\Manufactor;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BrandController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        // 恶人还需恶人磨
        ContextMenuWash::wash();

        return Grid::make(new Brand(), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->column('name', '名称')->display(function () {
                if (!$this->name) { return $this->name_en; }
                elseif (!$this->name_en) { return $this->name; }
                else { return $this->name . '(' . $this->name_en . ')'; }
            });
            $grid->column('alias');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
        
            $grid->quickSearch('name', 'alias');
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
        return Show::make($id, new Brand(), function (Show $show) {
            // $show->field('id');
            $show->field('name')->as(function (){
                if (!$this->name) { return $this->name_en; }
                elseif (!$this->name_en) { return $this->name; }
                else { return $this->name . '(' . $this->name_en . ')'; }
            });
            $show->field('alias');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Brand(), function (Form $form) {
            // $form->display('id');
            $form->text('name')->rules(
                'required_without:name_en|regex:/^[\x80-\xff]*$/|nullable',
                [
                    'required_without' => '请在品牌中文名称和英文名称中至少选择一项填写',
                    'regex' => '请输入中文',
                ]
            );
            $form->text('name_en')->rules(
                'required_without:name|regex:/^[\w-]*$/|nullable',
                [
                    'required_without' => '请在品牌中文名称和英文名称中至少选择一项填写',
                    'regex' => '请输入英文或数字',
                ]
            );
            $form->text('alias');
        });
    }
}
