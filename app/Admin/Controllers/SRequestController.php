<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\SRequestExport;
use App\Admin\Actions\Grid\ShowAudit;
use App\Admin\Actions\Modal\ImportModal;
use App\Admin\Actions\Modal\SRequestModal;
use App\Admin\Actions\Others\StatusBatch;
use App\Admin\Utils\ContextMenuWash;
use App\Admin\Utils\RequestStatusGraph;
use App\Models\AdminUser;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\SRequest;
use App\Models\Status;
use App\Models\Stype;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Alert;
use OwenIt\Auditing\Models\Audit;

class SRequestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        // 恶人还需恶人磨
        ContextMenuWash::wash();

        return Grid::make(SRequest::with(['stype', 'release', 'chip', 'bd']), function (Grid $grid) {

            $grid->paginate(10);

            // 工具栏
            $grid->tools(function (Grid\Tools $tools) {
                // 导入
                if(Admin::user()->can('srequests-edit')) {
                    $tools->append(new ImportModal('s_requests','sr_import'));
                }
                
                // 批量操作
                $tools->batch(function ($batch) {
                    // 状态修改按钮
                    if(Admin::user()->can('srequests-edit')) {
                        $batch->add(new StatusBatch('srequest'));
                    }
                });
            });

            // 行操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 复制按钮
                if (Admin::user()->can('srequests-edit')) {
                    $actions->append('<a href="' . admin_url('srequests/create?template=') . $this->getKey() . '"><i class="feather icon-copy"></i> 复制</a>');
                }
                // 查看历史
                $actions->append(new ShowAudit());
            });

            $grid->export(new SRequestExport());

            if (Admin::user()->cannot('srequests-edit')) {
                $grid->disableCreateButton();
                $grid->disableEditButton();
            }
            if (Admin::user()->cannot('srequests-delete')) {
                $grid->disableDeleteButton();
            }
            
            if (Admin::user()->cannot('srequests-action')) {
                $grid->disableActions();
            }

            $grid->showColumnSelector();

            // 默认按创建时间倒序排列
            $grid->model()->orderBy('updated_at', 'desc');

            $grid->column('source');
            $grid->column('manufactor');
            $grid->column('name');
            $grid->column('stype_id')->display(function ($stypes_id) {
                $curStype = Stype::where('id',$stypes_id)->first();
                $curParentStypeName = Stype::where('id',$curStype->parent)->pluck('name')->first();
                if($curParentStypeName){
                    $print = '软件/'.$curParentStypeName.'/'.$curStype->name;
                }else{
                    $print = '软件/' .$curStype->name.'/';
                }
                return $print;
                
            });
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
            $grid->column('creator')->display(function ($creator) {
                return AdminUser::find($creator)->name;
            });
            $grid->column('requester_name');
            $grid->column('requester_contact');
            $grid->column('status');

            $grid->column('sbind_id')->display(function ($sbind_id) {
                if ($sbind_id) {
                    return "<a href=" . admin_url('sbinds/'.$sbind_id) . ">点击查看</a>";
                }
            });
            $grid->column('bd.name');
            $grid->column('comment');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->scrollbarX();
            $grid->showColumnSelector();
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->like('name','产品名称')->width(3);
                $filter->like('manufactor','厂商名称')->width(3);

                //卡了上异步
                $filter->equal('project_name')->select(function (){
                    $a = array_filter(SRequest::all()->pluck('project_name','project_name')->toArray());
                    return $a;
                })->width(3);

                $filter->in('status','处理状态')
                ->multipleSelect(function () {
                    foreach (RequestStatusGraph::make()->getVertices() as $vertex) {
                        $options[$vertex->getId()] = $vertex->getId();
                    }
                    return $options;
                })->width(3);

                $filter->where('sbind_id',function ($query){
                    $query->whereHas('sbinds', function ($query){
                        $query->whereHas('statuses', function ($query){
                            $query->where('parent', $this->input);
                        });
                    });
                },'适配状态')->select([
                    1 => '未适配',
                    2 => '适配中',
                    3 => '已适配',
                    4 => '待验证',
                    5 => '适配暂停',
                ])->width(3);

                $filter->whereBetween('created_at', function ($query) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
            
                    if ($start !== null) {
                        $query->whereDate('created_at', '>=', $start);
                    }
                    if ($end !== null) {
                        $query->whereDate('created_at', '<=', $end);
                    }
                })->datetime()->width(3);

                $filter->whereBetween('updated_at', function ($query) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                
                    if ($start !== null) { $query->whereDate('updated_at', '>=', $start);}
            
                    if ($end !== null) {$query->whereDate('updated_at', '<=', $end);}
            
                })->date()->width(3);

                $filter->where('related', function ($query) {
                    if($this->input == 1) {
                        $query->created();
                    } 
                    elseif($this->input == 2) {
                        $query->related();
                    }
                    elseif($this->input == 3) {
                        $query->todo();
                    }
                }, '与我有关')->select([
                    1 => '我创建的',
                    2 => '我参与的',
                    3 => '我的待办'
                ])->width(3);
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
            $show->field('stype_id')->as(function ($stypes_id) {
                $curStype = Stype::where('id',$stypes_id)->first();
                $curParentStypeName = Stype::where('id',$curStype->parent)->pluck('name')->first();
                if($curParentStypeName){
                    $print = '软件/'.$curParentStypeName.'/'.$curStype->name;
                }else{
                    $print = '软件/' .$curStype->name.'/';
                }
                return $print;
                
            });
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
            if ($show->model()->sbind_id) {
                $show->field('sbind_id')->as(function ($sbind_id) {
                    return "<a href=" . admin_url('sbinds/'.$sbind_id) . ">点击查看</a>";
                })->link();
            } else {
                $show->field('sbind_id')->as(function () { return "暂无"; });
            }
            $show->field('comment');
            $show->field('created_at');

            $show->panel()->tools(function ($tools) {
                if (Admin::user()->cannot('srequests-edit')) { $tools->disableEdit(); }
                if (Admin::user()->cannot('srequests-delete')) { $tools->disableDelete(); }
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
            // 获取分类ModelTree
            $stypeModel = config('admin.database.stypes_model');
            // 新增需求
            if ($form->isCreating()) {
                // 获取要复制的行的ID
                $template = SRequest::find(request('template'));

                $form->select('source')
                    ->options(config('kaim.adapt_source'))->required()
                    ->default($template->source ?? null);
                $form->text('manufactor')->required()
                    ->default($template->manufactor ?? null);
                $form->text('name')->required()
                    ->default($template->name ?? null);
                $form->text('version')->required()
                    ->default($template->version ?? null);
                $form->select('stype_id')->options($stypeModel::selectOptions())
                    ->required()
                    ->rules(function (){
                        if (Stype::where('id',request()->stype_id)->pluck('parent')->first() == 0) {
                            return 'max:0';
                        }
                    },['max' => '软件分类  请选择子分类,例如:即时通讯,浏览器等'])
                    ->default($template->stype_id ?? null);
                $form->tags('industry')
                    ->options(config('kaim.industry'))
                    ->required()
                    ->default($template->industry ?? null);
                $form->select('release_id')
                    ->options(Release::all()->pluck('name', 'id'))
                    ->required()
                    ->default($template->release_id ?? null);
                $form->text('os_subversion')
                    ->help('例如:V10SP1-Build01-0326')
                    ->required()
                    ->default($template->os_subversion ?? null);
                $form->multipleSelect('chip_id')
                    ->options(Chip::all()->pluck('name', 'id'))
                    ->required()
                    ->default($template->chip_id ?? null);
                $form->text('project_name')
                    ->required()
                    ->default($template->project_name ?? null);
                $form->text('amount')
                    ->required()
                    ->default($template->amount ?? null);
                $form->select('project_status')
                    ->required()
                    ->options(config('kaim.project_status'))
                    ->default($template->project_status ?? null);
                $form->select('level')
                    ->options(config('kaim.project_level'))->required()
                    ->default($template->level ?? null);
                $form->text('manufactor_contact')
                    ->required()
                    ->default($template->manufactor_contact ?? null);
                $form->date('et')->required()
                    ->default($template->et ?? null);
                $form->hidden('creator')->default(Admin::user()->id);
                $form->text('requester_name')->default(Admin::user()->name)->required()
                    ->default($template->requester_name ?? null);
                $form->text('requester_contact')->required()
                    ->default($template->requester_contact ?? null);
                $form->hidden('status')->value('已提交');
                $form->select('bd_id')
                    ->options(AdminUser::all()->pluck('name', 'id'))->required()
                    ->default($template->bd_id ?? null);
                $form->text('comment')
                    ->default($template->comment ?? null);
            }
            // 编辑需求
            elseif ($form->isEditing()) {
                // 获取当前状态的图
                $request_status_graph = RequestStatusGraph::make()->getVertex($form->model()->status);

                // 已提交的需求
                if ($form->model()->status == '已提交') {
                    $form->select('source')
                        ->options(config('kaim.adapt_source'))->required();
                    $form->text('manufactor')->required();
                    $form->text('name')->required();
                    $form->text('version')->required();
                    $form->select('stype_id')
                        ->options($stypeModel::selectOptions())
                        ->required()
                        ->rules(function (){
                            if (Stype::where('id',request()->stype_id)->pluck('parent')->first() == 0) {
                                return 'max:0';
                            }
                        },['max' => '软件分类  请选择子分类,例如:即时通讯,浏览器等']);
                    $form->tags('industry')
                        ->options(config('kaim.industry'))
                        ->saving(function ($value) { return implode(',', $value); })->required();
                    $form->select('release_id')
                        ->options(Release::all()->pluck('name', 'id'))->required();
                    $form->text('os_subversion')->help('例如:V10SP1-Build01-0326')->required();
                    $form->select('chip_id')
                        ->options(Chip::all()->pluck('name', 'id'))->required();
                    $form->text('project_name')->required();
                    $form->text('amount')->required();
                    $form->select('project_status')
                        ->required()
                        ->options(config('kaim.project_status'));
                    $form->select('level')
                        ->options(config('kaim.project_level'))->required();
                    $form->text('manufactor_contact')->required();
                    $form->date('et')->required();
                    $form->text('requester_name')->default(Admin::user()->name)->required();
                    $form->text('requester_contact')->required();
                    $form->select('bd_id')
                        ->options(AdminUser::all()->pluck('name', 'id'))->required();
                    $form->text('comment');
                }
                else {
                    // 终态需求不允许编辑
                    if (! $request_status_graph->getEdgesOut()->count()) {
                        admin_exit(Content::make()
                            ->body(Alert::make($form->model()->status.'状态的需求不允许编辑')->info())
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
                            $form->select('statuses_id')->options(Status::where('parent', '!=', null)->pluck('name','id'))
                                ->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
                                ->setLabelClass(['asterisk']);
                            $form->text('statuses_comment');
                            $form->select('admin_user_id')->options(AdminUser::all()->pluck('name', 'id'))
                                ->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
                                ->setLabelClass(['asterisk']);
                            $form->select('class', admin_trans('sbind.fields.class'))
                                ->options(config('kaim.class'));
                            $form->select('adaption_type', admin_trans('sbind.fields.adaption_type'))
                                ->options(config('kaim.adaption_type'));
                            $form->select('test_type' ,admin_trans('sbind.fields.test_type'))
                                ->options(config('kaim.test_type'));
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
                    ->options(function () use ($request_status_graph) {
                        // 加上自己
                        $option[$request_status_graph->getId()] = $request_status_graph->getId();

                        foreach ($request_status_graph->getEdgesOut() as $edge) {
                            $option[$edge->getVertexEnd()->getId()] = $edge->getVertexEnd()->getId();
                        }

                        return $option;
                    })->required();
                $form->text('status_comment');
            }
            
            $form->saving(function (Form $form) {
                if($form->isEditing()) {
                    // 取当前状态
                    $status_current = $form->model()->status;
                    $status_coming = $form->status;
                    
                    if ($status_coming == '处理中' && ($status_coming != $status_current)) {
                        // Manufactor
                        $manufactor = Manufactor::firstOrCreate([
                            'name' => $form->manufactor,
                        ]);

                        // Software
                        $software = Software::firstOrCreate(
                            [
                                'name'  => $form->name,
                            ],
                            [
                                'manufactors_id'    => $manufactor->id,
                                'stypes_id'         => $form->stype_id,
                                'industries'        => implode(',', array_filter($form->industry)),
                            ],
                        );

                        // SBind
                        $sbind = Sbind::firstOrCreate(
                            [
                                'softwares_id'  => $software->id,
                                'releases_id'   => $form->release_id,
                                'chips_id'      => $form->chip_id,
                            ],
                            [
                                'os_subversion'     => $form->os_subversion,
                                'adapt_source'      => $form->source,
                                'statuses_id'       => $form->statuses_id,
                                'statuses_comment'  => $form->statuses_comment,
                                'admin_user_id'     => $form->admin_user_id,
                                'class'             => $form->class,
                                'test_type'         => $form->test_type,
                                'adaption_type'     => $form->adaption_type,
                                'kylineco'          => $form->kylineco,
                                'appstore'          => $form->appstore,
                                'iscert'            => $form->iscert,
                            ],
                        );

                        // 填充关联数据
                        $form->sbind_id = $sbind->id;
                    }
                    
                    // 删除临时数据
                    $form->deleteInput(['statuses_id', 'statuses_comment', 'admin_user_id', 'class', 'test_type', 'adaption_type', 'kylineco', 'appstore', 'iscert']);
                } else {
                    // 读取表单数据
                    $data = $form->input();
                    // 读取多选的芯片
                    $chips_id = array_filter($data['chip_id']);
                    // 取消无意义的数据
                    unset($data['chip_id'], $data["_previous_"], $data["_token"], $data['chss']);
                    // 处理行业标签
                    $data['industry'] = implode(',', array_filter($data['industry']));
                    // 初始化错误信息
                    $message = array();
                    // 遍历芯片
                    foreach ($chips_id as $chip_id) {
                        $data['chip_id'] = $chip_id;
                        $chip = Chip::find($data["chip_id"]);
                        // 创建PRequest记录
                        $srequest = null;
                        try {
                            $srequest = SRequest::create($data);
                        } catch (\Throwable $th) {
                            // 暂不处理异常
                            // throw $th;
                        }
                        // 返回错误
                        if(!$srequest) { $message[] = $chip->name; } 
                    }
                    // 返回提示并跳转
                    if ($message) {
                        return $form->response()->warning('操作完成,其中"' . implode(',', $message) . '"的数据创建失败')->redirect('srequests');
                    } else {
                        return $form->response()->success('操作完成')->redirect('srequests');
                    }
                }
            });
        });
    }
}
