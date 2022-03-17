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
use App\Admin\Renderable\PhistoryTable;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\DB;

class PbindController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Pbind::with(['peripherals','releases','chips','solutions','statuses','admin_users']), function (Grid $grid) {

            if(Admin::user()->can('pbinds-import'))
            {
                $grid->tools(function  (Grid\Tools  $tools)  { 
                    $tools->append(new PbindModal()); 
                });
            }

            if(!Admin::user()->can('pbinds-create'))
            {
                $grid->disableCreateButton();
            }

            if(Admin::user()->can('pbinds-export'))
            {
                $grid->export(new PbindExport());
            }

            if(!Admin::user()->can('pbinds-action'))
            {
                $grid->disableActions();
            }

            $grid->column('peripherals.name',__('外设型号'));
            $grid->column('releases.name',__('操作系统版本'));
            $grid->column('os_subversion');
            $grid->column('chips.name',__('芯片'));
            $grid->column('solutions', __('解决方案'))
                ->display('详情')
                ->modal(function ($modal){
                    $modal->title('解决方案');
                    $modal->xl();
                    return SolutionTable::make();
                }); 
            $grid->column('statuses.name',__('适配状态'));
            $grid->column('class');
            $grid->column('adapt_source');
            $grid->column('adapted_before')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                else                { return '否'; }
            })->hide();
            $grid->column('statuses.name',__('当前适配状态'));
            $grid->column('admin_users.name',__('当前适配状态责任人'));
            $grid->column('histories')
                ->display('查看')
                ->modal(function () {
                    return PhistoryTable::make();
                });

            $grid->column('adaption_type')->hide();
            $grid->column('test_type')->hide();
            $grid->column('kylineco')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                else                { return '否'; }
            });
            $grid->column('appstore')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                else                { return '否'; }
            });
            $grid->column('iscert')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                else                { return '否'; }
            });
            $grid->column('comment')->limit()->hide();
            
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
            $form->text('os_subversion');
            $form->select('chips_id',__('芯片'))->options(Chip::all()->pluck('name','id'));
            $form->select('solutions_id',__('解决方案'))->options(Solution::all()->pluck('name','id'));
            $form->select('adapt_source')
                 ->options([
                     '厂商主动申请' => '厂商主动申请',
                     'BD主动拓展' => 'BD主动拓展',
                     '行业营销中心引入' => '行业营销中心引入',
                     '区域营销中心引入' => '区域营销中心引入',
                     '最终客户反馈' => '最终客户反馈',
                     '产品经理引入' => '产品经理引入',
                     '厂商合作事业本部引入' => '厂商合作事业本部引入',
                     '渠道部引入' => '渠道部引入',
                     '相关机构反馈' => '相关机构反馈',
                     '其他方式引入' => '其他方式引入'
                    ]);
            $form->select('adapted_before')->options([0 => '否',1 => '是']);
            $form->select('statuses_id',__('状态'))->options(Status::where('parent','!=',null)->pluck('name','id'));
            if ($form->isEditing()) {
                $form->text('statuses_comment', __('状态变更说明'));
            }
            $form->hidden('admin_users_id')->default(Admin::user()->id);
            $form->select('class')
                 ->options([
                    'READY' => 'READY',
                    'CERTIFICATION' => 'CERTIFICATION',
                    'VALIDATION' => 'VALIDATION',
                    'PM' => 'PM'
                    ]);
            $form->select('adaption_type')
                 ->options([
                    '原生适配' => '原生适配',
                    '自研适配' => '自研适配',
                    '开源适配' => '开源适配',
                    '项目适配' => '项目适配'
                    ]);
            $form->select('test_type')
                 ->options([
                    '厂商自测' => '厂商自测',
                    '视频复测' => '视频复测',
                    '远程测试' => '远程测试',
                    '麒麟适配测试' => '麒麟适配测试'
                    ]);
            $form->select('kylineco')->options([0 => '否',1 => '是']);
            $form->select('appstore')->options([0 => '否',1 => '是']);
            $form->select('iscert')->options([0 => '否',1 => '是']);
            $form->text('comment');
        
            $form->display('created_at');
            $form->display('updated_at');

            $form->saving(function (Form $form) {
                // 判断是否是修改操作
                if ($form->isEditing()) {
                    $status_coming = $form->statuses_id;
                    $id = $form->getKey();
                    $timestamp = date("Y-m-d H:i:s");
                    
                    // 取当前状态
                    $status_current = DB::table('pbinds')->where('id', $id)->value('statuses_id');
                    if ($status_coming != $status_current) {
                        DB::table('pbind_histories')->insert([
                            'pbind_id' => $id,
                            'status_old' => $status_current,
                            'status_new' => $status_coming,
                            'admin_users_id' => Admin::user()->id,
                            'comment' => $form->statuses_comment,

                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);
                    }
                    $form->deleteInput('statuses_comment');
                }
            });
        });
    }
}
