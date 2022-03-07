<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\PbindExport;
use App\Admin\Actions\Modal\PbindModal;
use App\Models\Chip;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Release;
use App\Models\Solution;
use App\Models\Status;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Renderable\SolutionTable;
use App\Admin\Renderable\ReleaseTable;
use App\Admin\Renderable\ChipTable;

class PbindController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Pbind::with(['peripherals','releases','chips','solutions','statuses']), function (Grid $grid) {

            $grid->tools(function  (Grid\Tools  $tools)  { 
                $tools->append(new PbindModal()); 
            });

            $grid->export(new PbindExport());

            $grid->column('peripherals.name',__('外设型号'));
            $grid->column('releases.name',__('操作系统版本'));
            $grid->column('chips.name',__('芯片'));
            $grid->column('solutions', __('解决方案'))
                ->modal(function ($modal){
                    $modal->title('解决方案');
                    $modal->xl();
                    $modal->value('详情');
                    return SolutionTable::make();
                }); 
            $grid->column('statuses.name',__('适配状态'));
            $grid->column('class');
            $grid->column('comment');
            // $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->like('peripherals.name','设备名');
                $filter->like('solutions.name','解决方案');
                $filter->equal('releases.id', '操作系统版本')
                    ->multipleSelectTable(ReleaseTable::make(['id' => 'name']))
                    ->title('弹窗标题')
                    ->dialogWidth('50%')
                    ->model(Release::class, 'id', 'name');
                $filter->equal('chips.id', '芯片')
                    ->multipleSelectTable(ChipTable::make(['id' => 'name']))
                    ->title('弹窗标题')
                    ->dialogWidth('50%')
                    ->model(Chip::class, 'id', 'name');
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
        return Show::make($id, new Pbind(['peripherals','releases','chips','solutions','statuses']), function (Show $show) {
            $show->field('peripherals.name',__('型号'));
            $show->field('releases.name',__('版本'));
            $show->field('chips.name',__('芯片'));
            $show->field('solutions.name',__('解决方案'));
            $show->field('statuses.name',__('状态'));
            $show->field('class');
            $show->field('comment');
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
        return Form::make(new Pbind(['peripherals','releases','chips','solutions','statuses']), function (Form $form) {
            $form->select('peripherals_id',__('型号'))->options(Peripheral::all()->pluck('name','id'));
            $form->select('releases_id',__('版本'))->options(Release::all()->pluck('name','id'));
            $form->select('chips_id',__('芯片'))->options(Chip::all()->pluck('name','id'));
            $form->select('solutions_id',__('解决方案'))->options(Solution::all()->pluck('name','id'));
            $form->select('statuses_id',__('状态'))->options(Status::where('parent','!=',null)->pluck('name','id'));
            $form->select('class')->options(['READY' => 'READY','CERTIFICATION' => 'CERTIFICATION']);
            $form->text('comment');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
