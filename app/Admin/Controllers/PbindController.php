<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\PbindExport;
use App\Admin\Actions\Modal\PbindModal;
use App\Models\Chip;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Release;
use App\Models\Status;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Renderable\ReleaseTable;
use App\Admin\Renderable\ChipTable;
use App\Admin\Renderable\PhistoryTable;
use App\Admin\Renderable\StatusTable;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Type;
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
        return Grid::make(Pbind::with(['peripherals','releases','chips','statuses','admin_users']), function (Grid $grid) {

            if(Admin::user()->can('pbinds-import'))
            {
                $grid->tools(function  (Grid\Tools  $tools)  { 
                    $tools->append(new PbindModal()); 
                });
            }

            if(!Admin::user()->can('pbinds-edit'))
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

            $grid->showColumnSelector();  //后期可能根据权限显示

            $grid->column('peripherals.name',__('外设型号'));
            // 脑瘫代码
            $grid->column('peripherals.types_id',__('外设类型'))->display(function ($type) {
                return Type::where('id', $type)->pluck('name')->first();
            });
            $grid->column('peripherals.brands_id',__('品牌'))->display(function ($brand) {
                return Brand::where('id', $brand)->pluck('name')->first();
            });
            $grid->column('releases.name',__('操作系统版本'));
            $grid->column('os_subversion')->hide();
            $grid->column('chips.name',__('芯片'));
            $grid->column('solution', __('适配方案'));
            $grid->column('class')->hide();
            $grid->column('adapt_source');
            $grid->column('adapted_before')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                else                { return '否'; }
            })->hide();
            $grid->column('statuses.parent', __('当前适配状态'))->display(function ($parent) {
                    return Status::where('id', $parent)->pluck('name')->first();
                });
            $grid->column('statuses.name', __('当前细分适配状态'));
            $grid->column('admin_users.name', __('当前适配状态责任人'));
            $grid->column('histories')
                ->display('查看')
                ->modal(function () {
                    return PhistoryTable::make();
                });
            $grid->column('adaption_type');
            $grid->column('test_type');
            $grid->column('kylineco')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                elseif ($value == '0') { return '否'; }
            });
            $grid->column('appstore')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                elseif ($value == '0') { return '否'; }
            });
            $grid->column('iscert')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                elseif ($value == '0') { return '否'; }
            });
            $grid->column('start_time');
            $grid->column('complete_time');
            $grid->column('comment')->limit()->hide();       
            // $grid->column('created_at');
            $grid->column('updated_at')->sortable();
           
            $grid->quickSearch('peripherals.name', 'releases.name', 'chips.name', 'comment');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->like('peripherals.name','设备名');
                $filter->like('solution','解决方案');
                $filter->like('comment','备注');
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
                $filter->whereBetween('updated_at', function ($query) {
                        $start = $this->input['start'] ?? null;
                        $end = $this->input['end'] ?? null;
                    
                        $query->whereHas('binds', function ($query) use ($start,$end) {
                            if ($start !== null) {
                                $query->where('updated_at', '>=', $start);
                            }
                    
                            if ($end !== null) {
                                $query->where('updated_at', '<=', $end);
                            }
                        });
                    })->datetime()->width(3);
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
        return Show::make($id, Pbind::with(['peripherals','releases','chips','statuses']), function (Show $show) {
            $show->field('peripherals.name',__('型号'));
            $show->field('releases.name',__('版本'));
            $show->field('chips.name',__('芯片'));
            $show->field('solution',__('解决方案'));
            $show->field('statuses.name',__('状态'));
            $show->field('class');
            $show->field('comment');
            // $show->field('created_at');
            // $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(Pbind::with(['peripherals','releases','chips','statuses']), function (Form $form) {
            $form->select('peripherals_id',__('型号'))->options(Peripheral::all()->pluck('name','id'))->required();
            $form->select('releases_id',__('版本'))->options(Release::all()->pluck('name','id'))->required();
            $form->text('os_subversion');
            $form->select('chips_id',__('芯片'))->options(Chip::all()->pluck('name','id'))->required();
            $form->text('solution',__('适配方案'));
            $form->select('adapt_source')
                 ->options(config('kaim.adapt_source'))->required();
            $form->select('adapted_before')->options([0 => '否',1 => '是']);
            $form->select('statuses_id',__('状态'))->options(Status::where('parent','!=',null)->pluck('name','id'))->required();
            $form->text('statuses_comment', __('状态变更说明'));
            $form->select('admin_users_id')->options(AdminUser::all()->pluck('name', 'id'))->default(Admin::user()->id);
            $form->select('class')
                 ->options(config('kaim.class'));
            $form->select('adaption_type')
                 ->options(config('kaim.adaption_type'));
            $form->select('test_type')
                 ->options(config('kaim.test_type'));


            $form->select('kylineco')->options([0 => '否',1 => '是'])->required();
            $form->select('appstore')->options([0 => '否',1 => '是'])->required();
            $form->select('iscert')->options([0 => '否',1 => '是'])->required();
            $form->date('start_time')->format('Y-M-D');
            $form->date('complete_time')->format('Y-M-D');

            $form->text('comment');
        
            // $form->display('created_at');
            // $form->display('updated_at');

            $form->saving(function (Form $form) {
                $database_name = env('DB_DATABASE');
                $status_coming = $form->statuses_id;
                $timestamp = date("Y-m-d H:i:s");
                
                if ($form->isCreating()) {
                    // 脑瘫代码
                    $id = DB::select("
                        SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES 
                        WHERE TABLE_SCHEMA = '$database_name' AND TABLE_NAME = 'pbinds'
                    ")[0]->AUTO_INCREMENT;
                }
                else
                {
                    $id = $form->getKey();
                }
                

                // 判断当前为新增还是修改
                if ($form->isCreating()) {
                    $status_current = NULL;
                }
                else
                {
                    // 取当前状态
                    $status_current = DB::table('pbinds')->where('id', $id)->value('statuses_id');
                }
                
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
            });
        });
    }
}
