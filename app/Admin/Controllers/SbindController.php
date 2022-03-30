<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\SbindExport;
use App\Admin\Actions\Modal\SbindModal;
use App\Admin\Renderable\ChipTable;
use App\Admin\Renderable\PhistoryTable;
use App\Admin\Renderable\ReleaseTable;
use App\Admin\Renderable\StatusTable;
use App\Models\AdminUser;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\Status;
use App\Models\Stype;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
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

            // $grid->column('id')->sortable();
            $grid->column('softwares.name',__('软件名'))->width('15%');
            $grid->column('softwares.stypes_id',__('类型'))->display(function ($stypes_id) {
                return Stype::where('id',$stypes_id)->pluck('name')->first();
            });
            $grid->column('softwares.manufactors_id',__('厂商名称'))->display(function ($manufactors) {
                return Manufactor::where('id',$manufactors)->pluck('name')->first();
            });
            $grid->column('releases.name',__('操作系统版本'))->width('15%');
            $grid->column('os_subversion')->hide();
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
            $grid->column('admin_users.name', __('当前适配状态责任人'));
            $grid->column('histories')
                ->display('查看')
                ->modal(function () {
                    return PhistoryTable::make();
                });

            $grid->column('solution');
            $grid->column('class')->hide();
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
            $grid->column('created_at')->hide();
            $grid->column('updated_at')->sortable()->hide();


            $grid->scrollbarX();
            $grid->addTableClass(['table-text-center']);
            $grid->withBorder();
            $grid->showColumnSelector();
            
            $grid->quickSearch('softwares.name', 'releases.name', 'chips.name', 'comment');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->like('softwares.name','软件名');
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
        return Show::make($id, new Sbind(), function (Show $show) {
            // $show->field('id');
            $show->field('softwares_id');
            $show->field('releases_id');
            $show->field('os_subversion');
            $show->field('chips_id');
            $show->field('adapt_source');
            $show->field('adapted_before');
            $show->field('statuses_id');
            $show->field('admin_users_id');
            $show->field('solution');
            $show->field('class');
            $show->field('adaption_type');
            $show->field('test_type');
            $show->field('kylineco');
            $show->field('appstore');
            $show->field('iscert');
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
        return Form::make(Sbind::with('softwares','releases','chips'), function (Form $form) {
            // $form->display('id');
            $form->select('softwares_id')->options(Software::all()->pluck('name','id'))->required();
            $form->select('releases_id')->options(Release::all()->pluck('name','id'))->required();
            $form->text('os_subversion');
            $form->select('chips_id')->options(Chip::all()->pluck('name','id'))->required();
            $form->select('adapt_source')
                ->options(config('kaim.adapt_source'))->required();
            $form->select('adapted_before')->options([0 => '否',1 => '是']);
            $form->select('statuses_id')->options(Status::where('parent','!=',null)->pluck('name','id'))->required();
            $form->text('statuses_comment', __('状态变更说明'));
            $form->select('admin_users_id')->options(AdminUser::all()->pluck('name', 'id'))->default(Admin::user()->id);
            $form->text('solution');
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
        
            $form->display('created_at');
            $form->display('updated_at');
            
            $form->saving(function (Form $form) {
                $database_name = env('DB_DATABASE');
                $status_coming = $form->statuses_id;
                $timestamp = date("Y-m-d H:i:s");

                if ($form->isCreating()) {
                    // 脑瘫代码
                    $id = DB::select("
                        SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES 
                        WHERE TABLE_SCHEMA = '$database_name' AND TABLE_NAME = 'sbinds'
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
                    $status_current = DB::table('sbinds')->where('id', $id)->value('statuses_id');
                }

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
                $form->deleteInput('statuses_comment');
            });
        });
    }
}
