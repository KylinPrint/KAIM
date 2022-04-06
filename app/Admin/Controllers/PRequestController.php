<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\PRhistoryTable;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Release;
use App\Models\Pbind;
use App\Models\PbindHistory;
use App\Models\Peripheral;
use App\Models\PRequest;
use App\Models\Status;
use App\Models\Type;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class PRequestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(PRequest::with(['type', 'release', 'chip', 'bd']), function (Grid $grid) {

            $grid->paginate(10);
            if(!Admin::user()->can('prequests-edit'))
            {
                $grid->disableCreateButton();
            }
            
            if(!Admin::user()->can('prequests-action'))
            {
                $grid->disableActions();
            }

            $grid->column('source');
            $grid->column('manufactor');
            $grid->column('brand');
            $grid->column('name');
            $grid->column('type.name');
            $grid->column('industry')->badge();
            $grid->column('release.name');
            $grid->column('chip.name');
            $grid->column('project_name');
            $grid->column('amount');
            $grid->column('project_status');
            $grid->column('level');
            $grid->column('manufactor_contact');
            $grid->column('et');
            $grid->column('requester_name');
            $grid->column('requester_contact');
            $grid->column('status');
            $grid->column('history')
                ->display('查看')
                ->modal(function () {
                    return PRhistoryTable::make();
            });
            $grid->column('pbind_id')->display(function ($pbind_id) {
                $href = admin_url('pbinds/'.$pbind_id);
                return "<a href='$href'>点击查看</a>";
            });
            $grid->column('bd.name');
            $grid->column('comment');
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
        return Show::make($id, PRequest::with(['type', 'release', 'chip', 'bd']), function (Show $show) {
            $show->field('source');
            $show->field('manufactor');
            $show->field('brand');
            $show->field('name');
            $show->field('type.name');
            $show->field('industry');
            $show->field('release.name');
            $show->field('chip.name');
            $show->field('project_name');
            $show->field('amount');
            $show->field('project_status');
            $show->field('level');
            $show->field('manufactor_contact');
            $show->field('et');
            $show->field('requester_name');
            $show->field('requester_contact');
            $show->field('status');
            $show->field('bd.name');
            $show->field('comment');
            $show->field('created_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(PRequest::with(['type', 'release', 'chip', 'bd']), function (Form $form) {
            if ($form->isCreating()) {
                // 新增需求
                $form->select('source')
                    ->options(config('kaim.adapt_source'))->required();
                $form->text('manufactor')->required();
                $form->text('brand')->required();
                $form->text('name')->required();
                $form->select('type_id')
                    ->options(Type::where('parent', '!=', 0)->pluck('name', 'id'))->required();
                $form->tags('industry')
                    ->options(config('kaim.industry'))
                    ->saving(function ($value) { return implode(',', $value); })->required();
                $form->select('release_id')
                    ->options(Release::all()->pluck('name', 'id'))->required();
                $form->select('chip_id')
                    ->options(Chip::all()->pluck('name', 'id'))->required();
                $form->text('project_name');
                $form->text('amount');
                $form->select('project_status')
                    ->options(config('kaim.project_status'));
                $form->select('level')
                    ->options(config('kaim.project_level'))->required();
                $form->text('manufactor_contact');
                $form->date('et')->required();
                $form->text('requester_name')->required();
                $form->text('requester_contact')->required();
                $form->hidden('status')->value('已提交');
                $form->select('bd_id')
                    ->options(AdminUser::all()->pluck('name', 'id'))->required();
                $form->text('comment');
            } else {
                // 编辑需求
                // 按当前需求状态分类
                switch ($form->model()->status) {
                    // TODO 已拒绝和已关闭不允许编辑
                    case "已拒绝":
                        break;
                    
                    case '已关闭':
                        break;
                    
                    // TODO 改为暂停处理还能改回处理中吗
                    case '已提交':
                        $form->display('source');
                        $form->display('manufactor');
                        $form->display('brand');
                        $form->display('name');
                        $form->display('type.name');
                        $form->display('industry');
                        $form->display('release.name');
                        $form->display('chip.name');
                        $form->display('project_name');
                        $form->display('amount');
                        $form->display('project_status');
                        $form->display('level');
                        $form->display('manufactor_contact');
                        $form->display('et');
                        $form->display('requester_name');
                        $form->display('requester_contact');
                        $form->display('bd.name');
                        $form->display('comment');

                        $form->select('status')
                            ->when('处理中', function (Form $form) {
                                $form->select('statuses_id')->options(Status::where('parent','!=',null)->pluck('name','id'));
                                $form->text('statuses_comment');
                                $form->select('admin_users_id')->options(AdminUser::all()->pluck('name', 'id'));
                                $form->select('kylineco')->options([0 => '否', 1 => '是']);
                                $form->select('appstore')->options([0 => '否', 1 => '是']);
                                $form->select('iscert')->options([0 => '否', 1 => '是']);
                                $form->hidden('pbind_id');
                            })
                            ->options([
                                '处理中' => '处理中',
                                '已拒绝' => '已拒绝',
                            ])->required();
                        $form->text('status_comment', __('状态变更说明'))->required();
                        break;
                    
                    default:
                        $form->display('source');
                        $form->display('manufactor');
                        $form->display('brand');
                        $form->display('name');
                        $form->display('type.name');
                        $form->display('industry');
                        $form->display('release.name');
                        $form->display('chip.name');
                        $form->display('project_name');
                        $form->display('amount');
                        $form->display('project_status');
                        $form->display('level');
                        $form->display('manufactor_contact');
                        $form->display('et');
                        $form->display('requester_name');
                        $form->display('requester_contact');
                        $form->display('bd.name');
                        $form->display('comment');

                        $form->select('status')
                            ->options(config('kaim.request_status'))->required();
                        $form->text('status_comment', __('需求状态变更说明'))->required();
                        break;
                }
            }
            
            $form->saving(function (Form $form) {
                if($form->isEditing()) {
                    $id = $form->getKey();
                    // 取当前状态
                    $status_current = DB::table('p_requests')->where('id', $id)->value('status');
                    $status_coming = $form->status;
                    $timestamp = date("Y-m-d H:i:s");
                    
                    if ($form->status == '处理中') {
                        // 查询Manufactor记录是否存在
                        $manufactor_id = Manufactor::where('name', $form->model()->manufactor)->pluck('id')->first();
                        if (!$manufactor_id) {
                            $manufactor = Manufactor::create([
                                'name' => $form->model()->manufactor
                            ]);
                            $manufactor_id = $manufactor->id;
                        }

                        // 查询Brand记录是否存在
                        $brand_id = Brand::where('name', $form->model()->brand)->pluck('id')->first();
                        if (!$brand_id) {
                            $brand = Brand::create([
                                'name' => $form->model()->brand
                            ]);
                            $brand_id = $brand->id;
                        }

                        // 查询Peripheral记录是否存在 
                        $peripheral_id = Peripheral::where('name', $form->model()->name)->pluck('id')->first();
                        if (!$peripheral_id) {
                            $peripheral = Peripheral::create([
                                'name' => $form->model()->name,
                                'manufactors_id' => $manufactor_id,
                                'brands_id' => $brand_id,
                                'types_id' => $form->model()->type_id,
                                'industries' => $form->model()->industry,
                            ]);
                            $peripheral_id = $peripheral->id;
                        }

                        // 查询PBind记录是否存在
                        $pbind_id = Pbind::where([
                            [ 'peripherals_id', $peripheral_id ],
                            [ 'releases_id', $form->model()->release_id ],
                            [ 'chips_id', $form->model()->chip_id ],
                        ])->pluck('id')->first();
                        if (!$pbind_id) {
                            $pbind = Pbind::create([
                                'peripherals_id'=> $peripheral_id,
                                'releases_id' => $form->model()->release_id,
                                'chips_id' => $form->model()->chip_id,
                                'adapt_source' => $form->model()->source,
                                'statuses_id' => $form->statuses_id,
                                'admin_users_id' => $form->admin_users_id,
                                'kylineco' => $form->model()->kylineco,
                                'appstore' => $form->model()->appstore,
                                'iscert' => $form->model()->iscert,
                            ]);
                            $pbind_id = $pbind->id;
                            PbindHistory::create([
                                'pbind_id' => $pbind_id,
                                'status_old' => NULL,
                                'status_new' => $form->statuses_id,
                                'admin_users_id' => $form->admin_users_id,
                                'comment' => $form->statuses_comment,
                            ]);
                        }
                        // 填充关联数据
                        $form->pbind_id = $pbind_id;
                    }

                    // 需求状态变更记录
                    if ($status_coming != $status_current) {
                        DB::table('p_request_histories')->insert([
                            'p_request_id' => $id,
                            'status_old' => $status_current,
                            'status_new' => $status_coming,
                            'operator' => Admin::user()->id,
                            'comment' => $form->status_comment,
    
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);
                    }
                    
                    // 删除临时数据
                    $form->deleteInput(['status_comment', 'statuses_id', 'statuses_comment', 'admin_users_id', 'kylineco', 'appstore', 'iscert']);
                }
            });
        });
    }
}
