<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\PRequestExport;
use App\Admin\Actions\Modal\PRequestModal;
use App\Admin\Actions\Others\PRStatusBatch;
use App\Admin\Renderable\PRhistoryTable;
use App\Admin\Utils\ContextMenuWash;
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
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Alert;
use Illuminate\Support\Facades\URL;

class PRequestController extends AdminController
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

        return Grid::make(PRequest::with(['type', 'release', 'chip', 'bd','pbinds']), function (Grid $grid) {

            $grid->paginate(10);

            $grid->tools(function  (Grid\Tools  $tools)  { 
                $tools->append(new PRequestModal());

                if(Admin::user()->can('prequests-edit')) {
                    $tools->batch(function ($batch) {
                        // 状态修改按钮
                        $batch->add(new PRStatusBatch());
                    });
                }
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 复制按钮
                $actions->append('<a href="' . admin_url('prequests/create?template=') . $this->getKey() . '"><i class="feather icon-copy"></i> 复制</a>');
            });

            $grid->export(new PRequestExport());

            if(!Admin::user()->can('prequests-edit')) { $grid->disableCreateButton(); }
            
            if(!Admin::user()->can('prequests-action')) { $grid->disableActions(); }

            $grid->showColumnSelector();

            // 默认按创建时间倒序排列
            $grid->model()->orderBy('created_at', 'desc');
            
            $grid->column('source');
            $grid->column('manufactor');
            $grid->column('brand');
            $grid->column('name');
            $grid->column('type.name');
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

            $grid->scrollbarX();
            $grid->showColumnSelector();
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->like('name','产品名称')->width(3);
                $filter->like('manufactor','厂商名称')->width(3);
                
                $filter->in('status','处理状态')
                ->multipleSelect([
                    '已提交' => '已提交',
                    '处理中' => '处理中',
                    '已处理' => '已处理',
                    '暂停处理' => '暂停处理',
                    '已拒绝' => '已拒绝',
                    '已关闭' => '已关闭',
                ])->width(3);

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
                    '适配暂停',
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
                            PRequestHistory::where('user_name', Admin::user()->name)->pluck('p_request_id')->toArray(),
                            // 当前BD
                            PRequest::where('bd_id', Admin::user()->id)->pluck('id')->toArray(),
                            // 需求创建人
                            PRequest::where('creator', Admin::user()->id)->pluck('id')->toArray(),
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
        return Show::make($id, PRequest::with(['type', 'release', 'chip', 'bd']), function (Show $show) {
            $show->field('source');
            $show->field('manufactor');
            $show->field('brand');
            $show->field('name');
            $show->field('type.name');
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
            if ($show->model()->pbind_id) {
                $show->field('pbind_id')->as(function ($pbind_id) {
                    return "<a href=" . admin_url('pbinds/'.$pbind_id) . ">点击查看</a>";
                })->link();
            } else {
                $show->field('pbind_id')->as(function () { return "暂无"; });
            }
            $show->field('comment');
            $show->field('created_at');

            // 需求处理记录
            $show->relation('histories', function ($model) {
                $grid = Grid::make(new PRequestHistory);
            
                $grid->model()->where('p_request_id', $model->id);
            
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
        return Form::make(PRequest::with(['type', 'release', 'chip', 'bd']), function (Form $form) {
            // 获取分类ModelTree
            $typeModel = config('admin.database.types_model');
            // 新增需求
            if ($form->isCreating()) {
                // 获取要复制的行的ID
                $template = PRequest::find($this->urlQuery('template'));

                $form->select('source')
                    ->options(config('kaim.adapt_source'))->required()
                    ->default($template->source ?? null);
                $form->text('manufactor')->required()
                    ->default($template->manufactor ?? null);
                // TODO PRequest新增添加品牌的输入提示
                $form->text('brand')->required()
                    ->help('格式: 品牌中文名(品牌英文名)  如: 惠普(HP)')
                    ->default($template->brand ?? null);
                $form->text('name')->required()
                    ->default($template->name ?? null);
                $form->select('type_id')
                    ->options($typeModel::selectOptions())
                    ->required()
                    ->default($template->type_id ?? null);
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
                    $form->text('brand')->required();
                    $form->text('name')->required();
                    $form->select('type_id')
                        ->options($typeModel::selectOptions())->required();
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
                    $form->display('brand');
                    $form->display('name');
                    $form->display('type.name');
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

                        // Brand
                        // 抓括号
                        if (preg_match('/\(|\（/', $form->brand)) {
                            // 拆分中英文
                            preg_match('/(.+(?=\(|\（))/', trim($form->brand), $brand_name);
                            preg_match('/(?<=\(|\（).+?(?=\)|\）)/', trim($form->brand), $brand_name_en);
                        } else {
                            if (preg_match('/[\x7f-\xff]/', $form->brand)) {
                                // 抓中文
                                $brand_name = trim($form->brand);
                            } else {
                                $brand_name_en = trim($form->brand);
                            }
                        }
                        $brand = Brand::firstOrCreate([
                            'name'      => $brand_name[0] ?? $brand_name ?? null,
                            'name_en'   => $brand_name_en[0] ?? $brand_name_en ?? null,
                        ]);

                        // Peripheral
                        $peripheral = Peripheral::firstOrCreate(
                            [
                                'manufactors_id'    => $manufactor->id,
                                'brands_id'         => $brand->id,
                                'name'              => $form->name,
                            ],
                            [
                                'types_id'          => $form->type_id,
                                'industries'        => implode(',', array_filter($form->industry)),
                            ],
                        );

                        // PBind
                        $pbind = Pbind::firstOrCreate(
                            [
                                'peripherals_id'    => $peripheral->id,
                                'releases_id'       => $form->release_id,
                                'chips_id'          => $form->chip_id,
                            ],
                            [
                                'os_subversion' => $form->os_subversion,
                                'adapt_source'  => $form->source,
                                'statuses_id'   => $form->statuses_id,
                                'user_name'     => $form->user_name,
                                'kylineco'      => $form->kylineco,
                                'appstore'      => $form->appstore,
                                'iscert'        => $form->iscert,
                            ],
                        );
                        // PBindHistory
                        if ($pbind->wasRecentlyCreated) {
                            PbindHistory::create([
                                'pbind_id'      => $pbind->id,
                                'status_old'    => NULL,
                                'status_new'    => $form->statuses_id,
                                'user_name'     => Admin::user()->name,
                                'comment'       => $form->statuses_comment,
                            ]);
                        }

                        // 填充关联数据
                        $form->pbind_id = $pbind->id;
                    }

                    // 需求状态变更记录
                    if ($status_coming != $status_current || $form->status_comment) {
                        PRequestHistory::create([
                            'p_request_id' => $id,
                            'status_old' => $status_current,
                            'status_new' => $status_coming,
                            'user_name' => Admin::user()->name,
                            'comment' => $form->status_comment,
                        ]);
                    }
                    
                    // 删除临时数据
                    $form->deleteInput(['status_comment', 'statuses_id', 'statuses_comment', 'user_name', 'kylineco', 'appstore', 'iscert']);
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
                        $prequest = null;
                        try {
                            $prequest = PRequest::create($data);
                        } catch (\Throwable $th) {
                            // 暂不处理异常
                            // throw $th;
                        }
                        // 返回错误
                        if(!$prequest) { $message[] = $chip->name; } 
                    }
                    // 返回提示并跳转
                    if ($message) {
                        return $form->response()->warning('操作完成,其中"' . implode(',', $message) . '"的数据创建失败')->redirect('prequests');
                    } else {
                        return $form->response()->success('操作完成')->redirect('prequests');
                    }
                }
            });
        });
    }
}
