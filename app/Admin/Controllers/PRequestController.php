<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\PRequestExport;
use App\Admin\Actions\Modal\PRequestModal;
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
use App\Models\PRequestHistory;
use App\Models\Status;
use App\Models\Type;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Form\Field\Button;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Alert;

class PRequestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(PRequest::with(['type', 'release', 'chip', 'bd','pbinds']), function (Grid $grid) {

            $grid->paginate(10);

            $grid->tools(function  (Grid\Tools  $tools)  { 
                $tools->append(new PRequestModal()); 
            });

            $grid->export(new PRequestExport());

            if(!Admin::user()->can('prequests-edit'))
            {
                $grid->disableCreateButton();
            }
            
            if(!Admin::user()->can('prequests-action'))
            {
                $grid->disableActions();
            }

            $grid->showColumnSelector();

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
                if ($pbind_id) {
                    return "<a href=" . admin_url('pbinds/'.$pbind_id) . ">点击查看</a>";
                }
            });
            $grid->column('bd.name');
            $grid->column('comment');
            $grid->column('created_at');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->like('name','产品名称');
                $filter->in('status','处理状态')
                ->multipleSelect([
                    '已提交' => '已提交',
                    '处理中' => '处理中',
                    '已处理' => '已处理',
                    '暂停处理' => '暂停处理',
                    '已拒绝' => '已拒绝',
                    '已关闭' => '已关闭',]);

                $filter->where('pbind',function ($query){
                    $query->whereHas('pbinds', function ($query){
                        $query->whereHas('statuses', function ($query){
                            $query->where('parent',"%{$this->input}%");
                        });
                    });
                },'适配状态')->select([
                    '未适配',
                    '适配中',
                    '已适配',
                    '待验证',
                    '适配暂停',]);

                $filter->whereBetween('created_at', function ($query) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
            
                    if ($start !== null) {
                        $query->where('created_at', '>=', $start);
                    }
        
                    if ($end !== null) {
                        $query->where('created_at', '<=', $end);
                    }

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

            $show->relation('histories', function ($model) {
                $grid = new Grid(PRequestHistory::with(['operator']));
            
                $grid->model()->where('p_request_id', $model->id);
            
                $grid->column('operator.name', __('处理人'));
                $grid->column('status_old', __('修改前状态'));
                $grid->column('status_new', __('修改后状态'));
                $grid->column('comment');
                $grid->updated_at();

                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                        
                return $grid;
            });
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
            }
            else {
                if ($form->model()->status == '已提交') {
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
                    $form->select('bd_id')
                        ->options(AdminUser::all()->pluck('name', 'id'))->required();
                    $form->text('comment');
                }
                else {
                    // 已关闭的需求不允许编辑
                    if ($form->model()->status == '已关闭') {
                        admin_exit(
                            Content::make()
                                ->body(Alert::make('已关闭的需求不允许编辑')->info())
                                ->body('
                                    <button onclick="history.back()" class="btn btn-primary btn-mini btn-outline" style="margin-right:3px">
                                        <i class="feather icon-corner-up-left"></i>
                                        <span class="d-none d-sm-inline">&nbsp; 返回</span>
                                    </button>
                                ')
                        );
                    }

                    // 除已提交状态外不可编辑的区域
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
                }

                // 按当前需求状态分类
                $form->select('status')
                    ->when('处理中', function (Form $form) {
                        // 由处理中修改为处理中时不显示以下字段
                        if ($form->model()->status != '处理中') {
                            $form->select('statuses_id')->options(Status::where('parent','!=',null)->pluck('name','id'))
                                ->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
                                ->setLabelClass(['asterisk']);
                            $form->text('statuses_comment')
                                ->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
                                ->setLabelClass(['asterisk']);
                            $form->select('admin_users_id')->options(AdminUser::all()->pluck('name', 'id'))
                                ->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
                                ->setLabelClass(['asterisk']);
                            $form->select('kylineco')->options([0 => '否', 1 => '是'])
                                ->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
                                ->setLabelClass(['asterisk']);
                            $form->select('appstore')->options([0 => '否', 1 => '是'])
                                ->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
                                ->setLabelClass(['asterisk']);
                            $form->select('iscert')->options([0 => '否', 1 => '是'])
                                ->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
                                ->setLabelClass(['asterisk']);
                            $form->hidden('pbind_id');
                        }
                    })
                    ->options(function () use ($form) {
                        $status_option = config('kaim.request_status');
                        // 脑瘫代码，极致享受
                        if (in_array($form->model()->status, ['处理中', '已处理', '暂停处理', '已拒绝'])) { unset($status_option['已提交']); }
                        if (in_array($form->model()->status, ['已处理', '已拒绝'])) { unset($status_option['处理中']); }
                        if (in_array($form->model()->status, ['已提交', '已拒绝'])) { unset($status_option['已处理']); }
                        if (in_array($form->model()->status, ['已提交', '已处理', '已拒绝'])) { unset($status_option['暂停处理']); }
                        if (in_array($form->model()->status, ['处理中', '已处理', '暂停处理'])) { unset($status_option['已拒绝']); }
                        return $status_option;
                    })->required();
                $form->text('status_comment', __('状态变更说明'))->required();
            }
            
            $form->saving(function (Form $form) {
                if($form->isEditing()) {
                    $id = $form->getKey();
                    // 取当前状态
                    $status_current = $form->model()->status;
                    $status_coming = $form->status;
                    
                    if ($status_coming == '处理中' && ($status_coming != $status_current)) {
                        // 查询Manufactor记录是否存在
                        $manufactor_id = Manufactor::where('name', $form->manufactor)->pluck('id')->first();
                        if (!$manufactor_id) {
                            $manufactor = Manufactor::create([
                                'name' => $form->manufactor
                            ]);
                            $manufactor_id = $manufactor->id;
                        }

                        // 查询Brand记录是否存在
                        $brand_id = Brand::where('name', $form->brand)->pluck('id')->first();
                        if (!$brand_id) {
                            $brand = Brand::create([
                                'name' => $form->brand
                            ]);
                            $brand_id = $brand->id;
                        }

                        // 查询Peripheral记录是否存在 
                        $peripheral_id = Peripheral::where('name', $form->name)->pluck('id')->first();
                        if (!$peripheral_id) {
                            $peripheral = Peripheral::create([
                                'name' => $form->name,
                                'manufactors_id' => $manufactor_id,
                                'brands_id' => $brand_id,
                                'types_id' => $form->type_id,
                                'industries' => implode(',', array_filter($form->industry)),
                            ]);
                            $peripheral_id = $peripheral->id;
                        }

                        // 查询PBind记录是否存在
                        $pbind_id = Pbind::where([
                            [ 'peripherals_id', $peripheral_id ],
                            [ 'releases_id', $form->release_id ],
                            [ 'chips_id', $form->chip_id ],
                        ])->pluck('id')->first();
                        if (!$pbind_id) {
                            $pbind = Pbind::create([
                                'peripherals_id'=> $peripheral_id,
                                'releases_id' => $form->release_id,
                                'chips_id' => $form->chip_id,
                                'adapt_source' => $form->source,
                                'statuses_id' => $form->statuses_id,
                                'admin_users_id' => $form->admin_users_id,
                                'kylineco' => $form->kylineco,
                                'appstore' => $form->appstore,
                                'iscert' => $form->iscert,
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
                    if ($status_coming != $status_current || $form->status_comment) {
                        PRequestHistory::create([
                            'p_request_id' => $id,
                            'status_old' => $status_current,
                            'status_new' => $status_coming,
                            'operator' => Admin::user()->id,
                            'comment' => $form->status_comment,
                        ]);
                    }
                    
                    // 删除临时数据
                    $form->deleteInput(['status_comment', 'statuses_id', 'statuses_comment', 'admin_users_id', 'kylineco', 'appstore', 'iscert']);
                }
            });
        });
    }
}
