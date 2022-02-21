<?php

namespace App\Admin\Controllers;

use App\Models\PeripheralIndustry;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PeripheralIndustryController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new PeripheralIndustry(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('peripherals_id');
            $grid->column('industries_id');
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
        return Show::make($id, new PeripheralIndustry(), function (Show $show) {
            $show->field('id');
            $show->field('peripherals_id');
            $show->field('industries_id');
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
        return Form::make(new PeripheralIndustry(), function (Form $form) {
            $form->display('id');
            $form->text('peripherals_id');
            $form->text('industries_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
