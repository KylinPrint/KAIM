<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\SRequestExport;
use App\Admin\Actions\Modal\SRequestModal;
use App\Admin\Renderable\SRhistoryTable;
use App\Models\AdminUser;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\SbindHistory;
use App\Models\Software;
use App\Models\SRequest;
use App\Models\SRequestHistory;
use App\Models\Status;
use App\Models\Stype;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Alert;

class SRequestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(SRequest::with(['stype', 'release', 'chip', 'bd']), function (Grid $grid) {

            $grid->paginate(10);

            if(!Admin::user()->can('srequests-edit'))
            {
                $grid->disableCreateButton();
            }
            
            if(!Admin::user()->can('srequests-action'))
            {
                $grid->disableActions();
            }

            $grid->tools(function  (Grid\Tools  $tools)  { 
                $tools->append(new SRequestModal()); 
            });

            $grid->export(new SRequestExport());

            if(!Admin::user()->can('prequests-edit'))
            {
                $grid->disableCreateButton();
            }

            $grid->showColumnSelector();

            // 默认按创建时间倒序排列
            $grid->model()->orderBy('created_at', 'desc');

            $grid->column('source');
            $grid->column('manufactor');
            $grid->column('name');
            $grid->column('stype.name');
            $grid->column('industry')->badge();
            $grid->column('release.name');
            $grid->column('os_subversion');
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
                    return SRhistoryTable::make();
            });
            $grid->column('sbind_id')->display(function ($sbind_id) {
                if ($sbind_id) {
                    return "<a href=" . admin_url('sbinds/'.$sbind_id) . ">点击查看</a>";
                }
            });
            $grid->column('bd.name');
            $grid->column('comment');
            $grid->column('created_at');

            $grid->scrollbarX();
            $grid->showColumnSelector();
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);

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

                $filter->where('sbind',function ($query){
                    $query->whereHas('sbinds', function ($query){
                        $query->whereHas('statuses', function ($query){
                            $query->where('parent', "%{$this->input}%");
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
        return Show::make($id, SRequest::with(['stype', 'release', 'chip', 'bd']), function (Show $show) {
            $show->field('source');
            $show->field('manufactor');
            $show->field('name');
            $show->field('version');
            $show->field('stype.name');
            $show->field('industry');
            $show->field('release.name');
            $show->field('os_subversion');
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
                $grid = Grid::make(new SRequestHistory());
            
                $grid->model()->where('s_request_id', $model->id);
            
                $grid->column('user_name', __('处理人'));
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
        return Form::make(SRequest::with(['stype', 'release', 'chip', 'bd']), function (Form $form) {
            if ($form->isCreating()) {
                // 新增需求
                $form->select('source')
                    ->options(config('kaim.adapt_source'))->required();
                $form->text('manufactor')->required();
                $form->text('name')->required();
                $form->text('version')->required();
                $form->select('stype_id')
                    ->options(Stype::all()->pluck('name', 'id'))->required();
                $form->tags('industry')
                    ->options(config('kaim.industry'))
                    ->saving(function ($value) { return implode(',', $value); })->required();
                $form->select('release_id')
                    ->options(Release::all()->pluck('name', 'id'))->required();
                $form->text('os_subversion')->help('例如:V10SP1-Build01-0326');
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
                $form->text('requester_name')->default(Admin::user()->name)->required();
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
                    $form->text('name')->required();
                    $form->text('version')->required();
                    $form->select('stype_id')
                        ->options(Stype::all()->pluck('name', 'id'))->required();
                    $form->tags('industry')
                        ->options(config('kaim.industry'))
                        ->saving(function ($value) { return implode(',', $value); })->required();
                    $form->select('release_id')
                        ->options(Release::all()->pluck('name', 'id'))->required();
                    $form->text('os_subversion')->help('例如:V10SP1-Build01-0326');
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
                    $form->text('requester_name')->default(Admin::user()->name)->required();
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
                    $form->display('name');
                    $form->display('version');
                    $form->display('stype.name');
                    $form->display('industry');
                    $form->display('release.name');
                    $form->display('os_subversion');
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
                            $form->select('user_name')->options(function (){
                                    $curaArr = AdminUser::all()->pluck('name')->toArray();
                                    foreach($curaArr as $cura){$optionArr[$cura] = $cura;}
                                    return $optionArr;
                                })->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
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
                            $form->hidden('sbind_id');
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

                        // 查询Software记录是否存在
                        $software_id = Software::where('name', $form->name)->pluck('id')->first();
                        if (!$software_id) {
                            $software = Software::create([
                                'name' => $form->name,
                                'manufactors_id' => $manufactor_id,
                                'stypes_id' => $form->stype_id,
                                'industries' => implode(',', array_filter($form->industry)),
                            ]);
                            $software_id = $software->id;
                        }

                        // 查询SBind记录是否存在
                        $sbind_id = Sbind::where([
                            [ 'softwares_id', $software_id ],
                            [ 'releases_id', $form->release_id ],
                            [ 'chips_id', $form->chip_id ],
                        ])->pluck('id')->first();
                        if (!$sbind_id) {
                            $sbind = Sbind::create([
                                'softwares_id' => $software_id,
                                'releases_id' => $form->release_id,
                                'os_subversion' => $form->os_subversion,
                                'chips_id' => $form->chip_id,
                                'adapt_source' => $form->source,
                                'statuses_id' => $form->statuses_id,
                                'user_name' =>$form->user_name,
                                'kylineco' => $form->kylineco,
                                'appstore' => $form->appstore,
                                'iscert' => $form->iscert,
                            ]);
                            $sbind_id = $sbind->id;
                            SbindHistory::create([
                                'sbind_id' => $sbind_id,
                                'status_old' => NULL,
                                'status_new' => $form->statuses_id,
                                'user_name' => Admin::user()->name,
                                'comment' => $form->statuses_comment,
                            ]);
                        }
                        // 填充关联数据
                        $form->sbind_id = $sbind_id;
                    }

                    // 需求状态变更记录
                    if ($status_coming != $status_current || $form->status_comment) {
                        SRequestHistory::create([
                            's_request_id' => $id,
                            'status_old' => $status_current,
                            'status_new' => $status_coming,
                            'user_name' => Admin::user()->name,
                            'comment' => $form->status_comment,
                        ]);
                    }
                    
                    // 删除临时数据
                    $form->deleteInput(['status_comment', 'statuses_id', 'statuses_comment', 'user_name', 'kylineco', 'appstore', 'iscert']);
                }
            });
        });
    }
}
