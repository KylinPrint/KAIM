<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\SbindExport;
use App\Admin\Actions\Modal\SbindModal;
use App\Admin\Renderable\ChipTable;
use App\Admin\Renderable\PhistoryTable;
use App\Admin\Renderable\ReleaseTable;
use App\Admin\Renderable\StatusTable;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\Status;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Form\Events\Saving;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class SbindController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Sbind::with('softwares','releases','chips','admin_users','statuses'), function (Grid $grid) {

            $grid->tools(function  (Grid\Tools  $tools)  { 
                $tools->append(new SbindModal()); 
            });

            $grid->export(new SbindExport());

            $grid->column('id')->sortable();
            $grid->column('softwares.name',__('软件名'))->width('15%');
            $grid->column('releases.name',__('操作系统版本'))->width('15%');
            $grid->column('os_subversion');
            $grid->column('chips.name',__('芯片'));
            $grid->column('adapt_source');
            $grid->column('adapted_before')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                else                { return '否'; }
            })->hide();
            $grid->column('statuses.parent', __('当前适配状态'))->display(function ($parent) {
                return Status::where('id', $parent)->pluck('name')->first();
            });
            $grid->column('statuses.name', __('当前细分适配状态'));
            $grid->column('admin_users.username', __('当前适配状态责任人'));
            $grid->column('histories')
                ->display('查看')
                ->modal(function () {
                    return PhistoryTable::make();
                });

            $grid->column('softname');
            $grid->column('solution');
            $grid->column('class');
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
            $grid->column('created_at')->hide();
            $grid->column('updated_at')->sortable()->hide();


            $grid->scrollbarX();
            $grid->addTableClass(['table-text-center']);
            $grid->withBorder();
            $grid->showColumnSelector();
            
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('softwares.name');
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
                $filter->equal('statuses.id', '适配状态')
                    ->multipleSelectTable(StatusTable::make(['id' => 'name']))
                    ->title('弹窗标题')
                    ->dialogWidth('50%')
                    ->model(Status::class, 'id', 'name');

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
        return Show::make($id, new Sbind(), function (Show $show) {
            $show->field('id');
            $show->field('softwares_id');
            $show->field('releases_id');
            $show->field('os_subversion');
            $show->field('chips_id');
            $show->field('adapt_source');
            $show->field('adapted_before');
            $show->field('statuses_id');
            $show->field('admin_users_id');
            $show->field('softname');
            $show->field('solution');
            $show->field('class');
            $show->field('adaption_type');
            $show->field('test_type');
            $show->field('kylineco');
            $show->field('appstore');
            $show->field('iscert');
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
        return Form::make(Sbind::with('softwares','releases','chips'), function (Form $form) {
            $form->display('id');
            $form->select('softwares_id')->options(Software::all()->pluck('name','id'))->required();
            $form->select('releases_id')->options(Release::all()->pluck('name','id'))->required();
            $form->text('os_subversion');
            $form->select('chips_id')->options(Chip::all()->pluck('name','id'))->required();
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
                    ])->required();
            $form->select('adapted_before')->options([0 => '否',1 => '是']);
            $form->select('statuses_id')->options(Status::where('parent','!=',null)->pluck('name','id'))->required();
            if ($form->isEditing()) {
                $form->text('statuses_comment', __('状态变更说明'));
            }
            $form->hidden('admin_users_id')->default(Admin::user()->id);
            $form->text('softname');
            $form->text('solution');
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
            $form->select('kylineco')->options([0 => '否',1 => '是'])->required();
            $form->select('appstore')->options([0 => '否',1 => '是'])->required();
            $form->select('iscert')->options([0 => '否',1 => '是'])->required();
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
                    $status_current = DB::table('sbinds')->where('id', $id)->value('statuses_id');
                    if ($status_coming != $status_current) {
                        DB::table('sbind_histories')->insert([
                            'sbind_id' => $id,
                            'status_old' => $status_current,
                            'status_new' => $status_coming,
                            'admin_users_id' => Admin::user()->id,
                            'comment' => $form->statuses_comment,

                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);
                    }
                }
            });
        });
    }
}
