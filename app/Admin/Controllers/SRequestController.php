<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\SRequestExport;
use App\Admin\Actions\Modal\SRequestModal;
use App\Admin\Actions\Others\StatusBatch;
use App\Admin\Renderable\SRhistoryTable;
use App\Admin\Utils\ContextMenuWash;
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
use Illuminate\Support\Facades\URL;

class SRequestController extends AdminController
{
    public $url_query = array();

    public function __construct()
    {
        // 处理URL参数
        parse_str(parse_url(URL::full())['query'] ?? null, $this->url_query);
    }

    public function urlQuery($key)
    {
        return $this->url_query[$key] ?? null;
    }

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

            $grid->tools(function  (Grid\Tools  $tools)  { 
                $tools->append(new SRequestModal());

                if(Admin::user()->can('srequests-edit')) {
                    $tools->batch(function ($batch) {
                        // 状态修改按钮
                        $batch->add(new StatusBatch('srequest'));
                    });
                }
            });

            // 复制按钮
            if (Admin::user()->can('srequests-edit')) {
                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->append('<a href="' . admin_url('srequests/create?template=') . $this->getKey() . '"><i class="feather icon-copy"></i> 复制</a>');
                });
            }

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
            $grid->column('creator')->display(function ($creator) {
                return AdminUser::find($creator)->name;
            });
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
                $filter->expand();

                $filter->like('name','产品名称')->width(3);
                $filter->like('manufactor','厂商名称')->width(3);

                //卡了上异步
                $filter->equal('project_name')->select(function (){
                    $a = array_filter(SRequest::all()->pluck('project_name','project_name')->toArray());
                    return $a;
                })->width(3);

                $filter->in('status','处理状态')
                ->multipleSelect([
                    '已提交' => '已提交',
                    '处理中' => '处理中',
                    '已处理' => '已处理',
                    '暂停处理' => '暂停处理',
                    '已拒绝' => '已拒绝',
                    '已关闭' => '已关闭',
                ])->width(3);

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
                        $query->where('created_at', '>=', $start);
                    }
                    if ($end !== null) {
                        $query->where('created_at', '<=', $end);
                    }
                })->datetime()->width(3);

                $filter->where('related', function ($query) {
                    if($this->input == 1) {
                        // 我创建的
                        $query->where('creator', Admin::user()->id);
                    } else {
                        // 我参与的
                        $related = array_unique(array_merge(
                            // 历史记录
                            SRequestHistory::where('user_name', Admin::user()->name)->pluck('s_request_id')->toArray(),
                            // 当前BD
                            SRequest::where('bd_id', Admin::user()->id)->pluck('id')->toArray(),
                            // 需求创建人
                            SRequest::where('creator', Admin::user()->id)->pluck('id')->toArray(),
                        ));
                        $query->whereIn('id', $related);
                    }
                }, __('与我有关'))->select([
                    1 => '我创建的',
                    2 => '我参与的'
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
            if ($show->model()->sbind_id) {
                $show->field('sbind_id')->as(function ($sbind_id) {
                    return "<a href=" . admin_url('sbinds/'.$sbind_id) . ">点击查看</a>";
                })->link();
            } else {
                $show->field('sbind_id')->as(function () { return "暂无"; });
            }
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
                $template = SRequest::find($this->urlQuery('template'));

                $form->select('source')
                    ->options(config('kaim.adapt_source'))->required()
                    ->default($template->source ?? null);
                $form->text('manufactor')->required()
                    ->default($template->manufactor ?? null);
                $form->text('name')->required()
                    ->default($template->name ?? null);
                $form->text('version')->required()
                    ->default($template->version ?? null);
                $form->select('stype_id')
                    ->options($stypeModel::selectOptions())
                    ->required()
                    ->default($template->stype_id ?? null);
                $form->tags('industry')
                    ->options(config('kaim.industry'))
                    ->required()
                    ->default($template->industry ?? null);
                $form->select('release_id')
                    ->options(Release::all()->pluck('name', 'id'))
                    ->required()
                    ->default($template->release_id ?? null);
                $form->text('os_subversion')->help('例如:V10SP1-Build01-0326')
                    ->default($template->os_subversion ?? null);
                $form->multipleSelect('chip_id')
                    ->options(Chip::all()->pluck('name', 'id'))
                    ->required()
                    ->default($template->chip_id ?? null);
                $form->text('project_name')
                    ->default($template->project_name ?? null);
                $form->text('amount')
                    ->default($template->amount ?? null);
                $form->select('project_status')
                    ->options(config('kaim.project_status'))
                    ->default($template->project_status ?? null);
                $form->select('level')
                    ->options(config('kaim.project_level'))->required()
                    ->default($template->level ?? null);
                $form->text('manufactor_contact')
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
            else {
                // 已提交的需求
                if ($form->model()->status == '已提交') {
                    $form->select('source')
                        ->options(config('kaim.adapt_source'))->required();
                    $form->text('manufactor')->required();
                    $form->text('name')->required();
                    $form->text('version')->required();
                    $form->select('stype_id')
                        ->options($stypeModel::selectOptions())->required();
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
                            $form->text('statuses_comment');
                            $form->select('user_name')->options(function () {
                                    $curaArr = AdminUser::all()->pluck('name')->toArray();
                                    foreach($curaArr as $cura){$optionArr[$cura] = $cura;}
                                    return $optionArr;
                                })->rules('required_if:status,处理中',['required_if' => '请填写此字段'])
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
                $form->text('status_comment');
            }
            
            $form->saving(function (Form $form) {
                if($form->isEditing()) {
                    $id = $form->getKey();
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
                                'os_subversion' => $form->os_subversion,
                                'adapt_source'  => $form->source,
                                'statuses_id'   => $form->statuses_id,
                                'user_name'     => $form->user_name,
                                'class'         => $form->class,
                                'test_type'     => $form->test_type,
                                'adaption_type' => $form->adaption_type,
                                'kylineco'      => $form->kylineco,
                                'appstore'      => $form->appstore,
                                'iscert'        => $form->iscert,
                            ],
                        );
                        // SbindHistory
                        if ($sbind->wasRecentlyCreated) {
                            SbindHistory::create([
                                'sbind_id' => $sbind->id,
                                'status_old' => NULL,
                                'status_new' => $form->statuses_id,
                                'user_name' => Admin::user()->name,
                                'comment' => $form->statuses_comment,
                            ]);
                        }
                        // 填充关联数据
                        $form->sbind_id = $sbind->id;
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
                    $form->deleteInput(['status_comment', 'statuses_id', 'statuses_comment', 'user_name','class','test_type','adaption_type', 'kylineco', 'appstore', 'iscert']);
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
