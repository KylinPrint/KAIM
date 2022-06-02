<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\SbindExport;
use App\Admin\Actions\Modal\SbindModal;
use App\Admin\Actions\Others\StatusBatch;
use App\Admin\Renderable\ChipTable;
use App\Admin\Renderable\ShistoryTable;
use App\Admin\Renderable\ReleaseTable;
use App\Admin\Renderable\SolutionTable;
use App\Admin\Renderable\StatusTable;
use App\Admin\Utils\ContextMenuWash;
use App\Models\AdminUser;
use App\Models\Chip;
use App\Models\Manufactor;
use App\Models\Release;
use App\Models\Sbind;
use App\Models\SbindHistory;
use App\Models\Software;
use App\Models\Status;
use App\Models\Stype;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SbindController extends AdminController
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

        return Grid::make(Sbind::with('softwares','releases','chips','admin_users','statuses'), function (Grid $grid) {

            $grid->paginate(10);

            $grid->tools(function  (Grid\Tools  $tools)  { 
                if(Admin::user()->can('sbinds-import')) {
                    $tools->append(new SbindModal()); 
                }         

                if(Admin::user()->can('sbinds-edit')) {
                    $tools->batch(function ($batch) {
                        $batch->add(new StatusBatch('sbind'));
                    });
                }
            });

            // 复制按钮
            if (Admin::user()->can('sbinds-edit')) {
                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->append('<a href="' . admin_url('sbinds/create?template=') . $this->getKey() . '"><i class="feather icon-copy"></i> 复制</a>');
                });
            }
            
            if (Admin::user()->cannot('sbinds-edit')) {
                $grid->disableCreateButton();
                $grid->disableEditButton();
            }
            if (Admin::user()->cannot('sbinds-delete')) {
                $grid->disableDeleteButton();
            }

            if(Admin::user()->can('sbinds-export')) { $grid->export(new SbindExport()); }
            
            if(Admin::user()->cannot('sbinds-action')) { $grid->disableActions(); }

            // 默认按创建时间倒序排列
            $grid->model()->orderBy('created_at', 'desc');

            $grid->column('softwares.manufactors_id',__('厂商名称'))->display(function ($manufactors) {
                return Manufactor::where('id',$manufactors)->pluck('name')->first();
            });
            $grid->column('softwares.name',__('软件名'));
            $grid->column('softwares.stypes_id',__('软件类型'))->display(function ($stypes_id) {
                return Stype::where('id',$stypes_id)->pluck('name')->first();
            });
            $grid->column('releases.name',__('操作系统版本'));
            $grid->column('os_subversion')->hide();
            $grid->column('chips.name',__('芯片名称'));
            $grid->column('adapt_source');
            $grid->column('adapted_before')->display(function ($value) {
                if ($value == '1') { return '是'; }
                elseif ($value == '0') { return '否'; }
            })->hide();
            $grid->column('statuses.parent', __('当前适配状态'))->display(function ($parent) {
                return Status::where('id', $parent)->pluck('name')->first();
            });
            $grid->column('statuses.name', __('当前细分适配状态'));
            $grid->column('user_name');
            $grid->column('histories')
                ->display('查看')
                ->modal(function () {
                    return ShistoryTable::make();
                });
            
            $grid->column('solution_name',__('适配方案'))
            ->display(function ($solution_name){
                if ($solution_name) {
                    return "详情";
                }
            })
            ->expand(function (){
                if ($this->solution_name) {
                    return SolutionTable::make(['solution' => $this->solution,'solution_name' => $this->solution_name]);
                }
            
            });

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
            $grid->column('test_report')->display(function ($value) {
                if ($value == '1')  { return '是'; }
                elseif ($value == '0') { return '否'; }
            });
            $grid->column('certificate_NO');
            $grid->column('start_time');
            $grid->column('complete_time');
            $grid->column('comment')->limit()->hide();
            $grid->column('created_at')->hide();
            $grid->column('updated_at')->sortable()->hide();

            //各种设置
            $grid->scrollbarX();
            $grid->showColumnSelector();
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
            
            // $grid->quickSearch('softwares.name', 'solution', 'comment');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                // 树状下拉  这块有待优化
                $filter->where('sname',function ($query){
                    $query->whereHas('softwares', function ($query){
                        $query->where('name', 'like','%'.$this->input.'%');
                    });
                },'软件名称')->width(3);

                $filter->where('manufacture', function ($query) {
                    $query->whereHas('softwares', function ($query) {
                        $query->whereHas('manufactors', function ($query) {
                            $query->where('name', 'like',"%{$this->input}%");
                        });
                    });
                }, '厂商')->width(3);

                $filter->like('solution')->width(3);

                $filter->where('sbind',function ($query){
                    $query->whereHas('softwares', function ($query){
                        $query->whereHas('stypes', function ($query){
                            if($this->input>8){$query->where('id', $this->input);}
                            elseif($this->input == 0){}
                            else{$query->where('parent', $this->input);}
                        });
                    });
                },'软件类型')->select(config('admin.database.stypes_model')::selectOptions())
                ->width(3);

                $filter->in('releases.id', '操作系统版本')
                    ->multipleSelectTable(ReleaseTable::make(['id' => 'name']))
                    ->title('弹窗标题')
                    ->dialogWidth('50%')
                    ->model(Release::class, 'id', 'name')
                    ->width(3);

                $filter->in('chips.id', '芯片')
                    ->multipleSelectTable(ChipTable::make(['id' => 'name']))
                    ->title('弹窗标题')
                    ->dialogWidth('50%')
                    ->model(Chip::class, 'id', 'name')
                    ->width(3);

                $filter->in('statuses.parent', '适配状态')
                    ->multipleSelectTable(StatusTable::make(['id' => 'name']))
                    ->title('弹窗标题')
                    ->dialogWidth('50%')
                    ->model(Status::class, 'id', 'name')
                    ->width(3);

                $filter->equal('adaption_type',__('适配类型'))->select(config('kaim.adaption_type'))->width(3);
                
                $filter->whereBetween('created_at', function ($query) {
                        $start = $this->input['start'] ?? null;
                        $end = $this->input['end'] ?? null; 

                        if ($start !== null) {$query->where('created_at', '>=', $start);}
                
                        if ($end !== null) {$query->where('created_at', '<=', $end);}

                })->date()->width(3);

                $filter->where('related', function ($query) {
                    if($this->input == 1)
                    {
                        $curUserCtreateArr = SbindHistory::where([
                            ['user_name',Admin::user()->name],
                            ['status_old',null]])->pluck('sbind_id')->toArray();

                        $query->whereIn('id',array_unique($curUserCtreateArr));
                    }
                    else 
                    { 
                        $curUserIncludedArr = array_merge(
                            SbindHistory::where('user_name',Admin::user()->name)
                            ->pluck('sbind_id')
                            ->toArray(),
                            Sbind::where('user_name',Admin::user()->name)
                            ->pluck('id')
                            ->toArray());  
                        $query->whereIn('id',array_unique($curUserIncludedArr));
                    }
                }, __('与我有关'))->select([
                    1 => '我创建的',
                    2 => '我参与的'
                ])->width(3);

                $filter->equal('appstore', __('是否上架'))->select(['1' => '是' , '0' => '否'])->width(2);
                $filter->equal('iscert')->select(['1' => '是' , '0' => '否'])->width(2);
                    
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
        return Show::make($id, Sbind::with(['softwares', 'releases', 'chips', 'statuses', 'admin_users']), function (Show $show) use ($id){
            $show->field('softwares.manufactors_id', __('厂商名称'))->as(function ($manufactors_id) {
                return Manufactor::where('id', $manufactors_id)->pluck('name')->first();
            });
            $show->field('softwares', __('软件名'))->as(function ($softwares) {
                return "<a href=" . admin_url('softwares/' . $softwares["id"]) . ">" . $softwares["name"] . "</a>";
            })->link();
            $show->field('softwares.stypes_id', __('软件类型'))->as(function ($stypes_id) {
                return Stype::where('id', $stypes_id)->pluck('name')->first();
            });
            $show->field('releases.name', __('操作系统版本'));
            $show->field('os_subversion');
            $show->field('chips.name', __('芯片名称'));
            $show->field('adapt_source');
            $show->field('adapted_before')->as(function ($adapted_before) {
                if ($adapted_before == '1') { return '是'; }
                elseif ($adapted_before == '0') { return '否'; }
            });
            $show->field('statuses.parent', __('当前适配状态'))->as(function ($parent) {
                return Status::where('id', $parent)->pluck('name')->first();
            });
            $show->field('statuses.name', __('当前细分适配状态'));
            $show->field('user_name', __('当前适配状态责任人'));
            $show->field('create_name',__('适配数据创建人'))->as(function () use ($id){
                $a =  SbindHistory::where([['sbind_id',$id],['status_old',null]])
                    ->pluck('user_name')->first();
                return $a;
            });
            $show->field('solution_name');
            $show->field('solution');
            $show->field('class');
            $show->field('adaption_type');
            $show->field('test_type');
            $show->field('kylineco')->as(function ($kylineco) {
                if ($kylineco == '1') { return '是'; }
                elseif ($kylineco == '0') { return '否'; }
            });
            $show->field('appstore')->as(function ($appstore) {
                if ($appstore == '1') { return '是'; }
                elseif ($appstore == '0') { return '否'; }
            });
            $show->field('iscert')->as(function ($iscert) {
                if ($iscert == '1') { return '是'; }
                elseif ($iscert == '0') { return '否'; }
            });
            $show->field('start_time');
            $show->field('complete_time');
            $show->field('comment');

            $show->relation('histories', function ($model) {
                $grid = new Grid(SbindHistory::with(['status_old', 'status_new']));
            
                $grid->model()->where('sbind_id', $model->id);
            
                $grid->column('user_name', __('处理人'));
                $grid->column('status_old.name', __('修改前状态'));
                $grid->column('status_new.name', __('修改后状态'));
                $grid->column('comment');
                $grid->updated_at();

                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                        
                return $grid;
            });

            $show->panel()->tools(function ($tools) {
                if (Admin::user()->cannot('sbinds-edit')) { $tools->disableEdit(); }
                if (Admin::user()->cannot('sbinds-delete')) { $tools->disableDelete(); }
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
        return Form::make(Sbind::with('softwares','releases','chips'), function (Form $form) {
            // 获取要复制的行的ID
            $template = Sbind::find($this->urlQuery('template'));

            $form->select('softwares_id')->options(function ($softwares_id) {
                $software = Software::find($softwares_id);
                if ($software) {
                    return [$software->id => $software->name.' '.$software->version];
                }
            })
            ->ajax('api/softwares')
            ->required()
            ->default($template->softwares_id ?? null);
            $form->select('releases_id',__('版本'))
                ->options(Release::all()->pluck('name','id'))
                ->required()
                ->default($template->releases_id ?? null);
            $form->text('os_subversion')
                ->help('例如:V10SP1-Build01-0326')
                ->default($template->os_subversion ?? null);
            if ($form->isCreating()) {
                $form->multipleSelect('chips_id',__('芯片'))
                    ->options(Chip::all()->pluck('name','id'))
                    ->saving(function ($value) { return implode(',', $value); })
                    ->required()
                    ->default($template->chips_id ?? null);
            } else {
                $form->select('chips_id',__('芯片'))
                    ->options(Chip::all()->pluck('name','id'))
                    ->required()
                    ->default($template->chips_id ?? null);
            }
            $form->select('adapt_source')
                 ->options(config('kaim.adapt_source'))
                 ->required()
                 ->default($template->adapt_source ?? null);
            $form->select('adapted_before')
                ->options([0 => '否',1 => '是'])
                ->default($template->adapted_before ?? null);
            $form->select('statuses_id',__('状态'))
                ->options(Status::where('parent','!=',null)->pluck('name','id'))
                ->required()
                ->default($template->statuses_id ?? null);
            $form->text('statuses_comment', __('适配状态变更说明'))
                ->default($template->statuses_comment ?? null);
            $form->select('user_name')->options(function (){
                $curaArr = AdminUser::all()->pluck('name')->toArray();
                foreach($curaArr as $cura){
                    $optionArr[$cura] = $cura;
                }
                return $optionArr;
            })->default(Admin::user()->name);
            $form->text('solution_name')
                ->default($template->solution_name ?? null);
            $form->text('solution')
                ->default($template->solution ?? null);
            $form->select('class')
                 ->options(config('kaim.class'))
                 ->default($template->class ?? null);
            $form->select('adaption_type')
                 ->options(config('kaim.adaption_type'))
                 ->default($template->adaption_type ?? null);
            $form->select('test_type')
                 ->options(config('kaim.test_type'))
                 ->default($template->test_type ?? null);
            $form->select('kylineco')
                ->options([0 => '否',1 => '是'])
                ->required()
                ->default($template->kylineco ?? null);
            $form->select('appstore')
                ->options([0 => '否',1 => '是'])
                ->required()
                ->default($template->appstore ?? null);
            $form->select('iscert')
                ->options([0 => '否',1 => '是'])
                ->required()
                ->default($template->iscert ?? null);
            $form->date('start_time')->format('Y-M-D')
                ->default($template->start_time ?? null);
            $form->date('complete_time')->format('Y-M-D')
                ->default($template->complete_time ?? null);
            $form->text('comment')
                ->default($template->comment ?? null);
            
                // 暂存statuses_comment
                $statuses_comment = '';
                
                $form->saving(function (Form $form) use (&$statuses_comment){
                    if($form->isCreating()) {
                        // 读取表单数据
                        $data = $form->input();
                        // 读取多选的芯片
                        $chips_id = array_filter($data['chips_id']);
                        // 取消无意义的数据
                        unset($data['chips_id'], $data["_previous_"], $data["_token"], $data['chss']);
                        // 初始化错误信息
                        $message = array();
                        // 遍历芯片
                        foreach ($chips_id as $chip_id) {
                            $data['chips_id'] = $chip_id;
                            $chip = Chip::find($data["chips_id"]);
                            // 创建PBinds记录
                            $sbind = null;
                            try {
                                $sbind = Sbind::create($data);
                            } catch (\Throwable $th) {
                                //throw $th;
                            }
                            if($sbind) {
                                // 创建PBinds历史记录
                                SbindHistory::create([
                                    'sbind_id' => $sbind->id,
                                    'status_old' => null,
                                    'status_new' => $data['statuses_id'],
                                    'user_name' => Admin::user()->name,
                                    'comment' => $statuses_comment,
                                ]);
                            } else {
                                // 返回错误
                                $message[] = $chip->name;
                            }
                        }
                        // 返回提示并跳转
                        if ($message) {
                            return $form->response()->warning('操作完成,其中"' . implode(',', $message) . '"的数据创建失败')->redirect('sbinds');
                        } else {
                            return $form->response()->success('操作完成')->redirect('sbinds');
                        }
                    } else {
                        $statuses_comment = $form->statuses_comment ?? null;
                        $form->deleteInput('statuses_comment');
                    }
                });
                
                $form->saved(function (Form $form) use (&$statuses_comment){
                    $id = $form->getKey();
                    $status_old = SbindHistory::where('sbind_id', $id)->orderBy('id','DESC')->pluck('status_new')->first();
                    if($form->statuses_id != $status_old){
                        SbindHistory::create([
                            'sbind_id' => $id,
                            'status_old' => $status_old,
                            'status_new' => $form->statuses_id,
                            'user_name' => Admin::user()->name,
                            'comment' => $statuses_comment,
                        ]);
                    }
                });
        });
    }
    public function sPaginate(Request $request)
    {
        $q = $request->get('q');
        $b = Software::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
        return $b;
    }
}
