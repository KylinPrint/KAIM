<?php

namespace App\Admin\Controllers;

use App\Models\Chip;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ChipController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Chip(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('arch');
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
        return Show::make($id, new Chip(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('arch');
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
        return Form::make(new Chip(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->select('arch')
            ->options([
                'amd64'         => 'amd64',
                'arm64'         => 'arm64',
                'mips64el'      => 'mips64el',
                'loongarch64'   => 'loongarch64',
                'sw64'          => 'sw64',
               ]);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
