<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\PbindExport;
use App\Admin\Actions\Modal\PbindModal;
use App\Admin\Actions\Others\PStatusBatch;
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
use App\Models\PbindHistory;
use App\Models\Type;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Card;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PbindController extends AdminController
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
        return Grid::make(Pbind::with(['peripherals','releases','chips','statuses','admin_users']), function (Grid $grid) {

            $grid->paginate(10);

            $grid->tools(function  (Grid\Tools  $tools)  { 
                if(Admin::user()->can('pbinds-import'))
                {
                    $tools->append(new PbindModal()); 
                }
                
                if(Admin::user()->can('pbinds-edit'))
                {
                    $tools->batch(function ($batch) 
                    {
                        $batch->add(new PStatusBatch());
                    });
                }
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 复制按钮
                $actions->append('<a href="' . admin_url('pbinds/create?template=') . $this->getKey() . '"><i class="feather icon-copy"></i> 复制</a>');
            });

            if(!Admin::user()->can('pbinds-edit')) { $grid->disableCreateButton(); }

            if(Admin::user()->can('pbinds-export')) { $grid->export(new PbindExport()); }

            if(!Admin::user()->can('pbinds-action')) { $grid->disableActions(); }


            // 默认按创建时间倒序排列
            $grid->model()->orderBy('created_at', 'desc');

            $grid->showColumnSelector();  //后期可能根据权限显示

            $grid->column('peripherals.brands_id',__('品牌'))->display(function ($brand) {
                return Brand::where('id', $brand)->pluck('name')->first();
            });
            $grid->column('peripherals.name',__('外设型号'));
            // 脑瘫代码
            $grid->column('peripherals.types_id',__('外设类型'))->display(function ($type) {
                return Type::where('id', $type)->pluck('name')->first();
            });
            $grid->column('releases.name',__('操作系统版本'));
            $grid->column('os_subversion')->hide();
            $grid->column('chips.name',__('芯片'));
            $grid->column('adapt_source');
            $grid->column('adapted_before')->display(function ($value) {
                if ($value == '1') { return '是'; }
                elseif ($value == '0') { return '否'; }
            })->hide();
            $grid->column('statuses.parent', __('当前适配状态'))->display(function ($parent) {
                    return Status::where('id', $parent)->pluck('name')->first();
                });
            $grid->column('statuses.name', __('当前细分适配状态'));
            $grid->column('user_name', __('当前适配状态责任人'));
            $grid->column('histories')
                ->display('查看')
                ->modal(function () {
                    return PhistoryTable::make();
                });
            $grid->column('solution_name', __('安装包名'));
            $grid->column('solution')->expand(function (Grid\Displayers\Expand $expand) {
                
                $expand->button('详情');
                $card = new Card(null, $this->solution);    
                return "<div style='padding:10px 10px 0;text-align:center;line-height:40px'>$card</div>";

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
            $grid->column('start_time');
            $grid->column('complete_time');
            $grid->column('comment')->limit()->hide();       
            $grid->column('updated_at')->sortable();

            //各种设置
            $grid->scrollbarX();
            $grid->showColumnSelector();
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
           
            $grid->quickSearch('peripherals.name', 'solution', 'comment');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                // 树状下拉  这块有待优化
                $filter->where('pbind',function ($query){
                    $query->whereHas('peripherals', function ($query){
                        $query->whereHas('types', function ($query){
                            if($this->input>5){$query->where('id', $this->input);}
                            elseif($this->input == 0){}
                            else{$query->where('parent', $this->input);}
                        });
                    });
                },'外设类型')->select(config('admin.database.types_model')::selectOptions())
                ->width(4);

                $filter->equal('releases.id', '操作系统版本')
                    ->multipleSelectTable(ReleaseTable::make(['id' => 'name']))
                    ->title('操作系统版本')
                    ->dialogWidth('50%')
                    ->model(Release::class, 'id', 'name')
                    ->width(3);

                $filter->equal('chips.id', '芯片')
                    ->multipleSelectTable(ChipTable::make(['id' => 'name']))
                    ->title('芯片')
                    ->dialogWidth('50%')
                    ->model(Chip::class, 'id', 'name')
                    ->width(3);

                $filter->equal('statuses.parent', '适配状态')
                    ->multipleSelectTable(StatusTable::make(['id' => 'name']))
                    ->title('适配状态')
                    ->dialogWidth('50%')
                    ->model(Status::class, 'id', 'name')
                    ->width(2);

                $filter->whereBetween('created_at', function ($query) {
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

                $filter->where('brand', function ($query) {
                    $query->whereHas('peripherals', function ($query) {
                        $query->whereHas('brands', function ($query) {
                            $query->where('name', 'like',"%{$this->input}%");
                        });
                    });
                }, '品牌')->width(3);

                $filter->where('related', function ($query) {
                    if($this->input == 1)
                    {
                        $curUserCtreateArr = PbindHistory::where([
                            ['user_name',Admin::user()->name],
                            ['status_old',null]])->pluck('pbind_id')->toArray();

                        $query->whereIn('id',array_unique($curUserCtreateArr));
                    }
                    else 
                    { 
                        $curUserIncludedArr = array_merge(
                            PbindHistory::where('user_name',Admin::user()->name)
                            ->pluck('pbind_id')
                            ->toArray(),
                            Pbind::where('user_name',Admin::user()->name)
                            ->pluck('id')
                            ->toArray());  
                        $query->whereIn('id',array_unique($curUserIncludedArr));
                    }
                }, __('与我有关'))->select([
                    1 => '我创建的',
                    2 => '我参与的'
                ])->width(2);

                $filter->equal('adaption_type',__('适配类型'))->select(config('kaim.adaption_type'))->width(2);
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
            $show->field('peripherals.brands_id', __('品牌'))->as(function ($brand) {
                return Brand::where('id', $brand)->pluck('name')->first();
            });
            $show->field('peripherals.name',__('型号'));
            $show->field('peripherals.types_id', __('外设类型'))->as(function ($type) {
                return Type::where('id', $type)->pluck('name')->first();
            });
            $show->field('releases.name',__('操作系统版本'));
            $show->field('os_subversion');
            $show->field('chips.name',__('芯片'));
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
                $grid = new Grid(PbindHistory::with(['status_old', 'status_new']));
            
                $grid->model()->where('pbind_id', $model->id);
            
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
            // 获取要复制的行的ID
            $template = Pbind::find($this->urlQuery('template'));

            $form->select('peripherals_id',__('型号'))
                ->options(function ($id) {
                    $peripheral = Peripheral::find($id);
                
                    if ($peripheral) {
                        return [$peripheral->id => $peripheral->name];
                    }
                })
                ->ajax('api/peripherals')
                ->required()
                ->default($template->peripherals_id ?? null);
            $form->select('releases_id',__('版本'))
                ->options(Release::all()->pluck('name','id'))
                ->required()
                ->default($template->releases_id ?? null);
            $form->text('os_subversion')
                ->help('例如:V10SP1-Build01-0326')
                ->default($template->os_subversion ?? null);
            $form->select('chips_id',__('芯片'))
                ->options(Chip::all()->pluck('name','id'))
                ->required()
                ->default($template->chips_id ?? null);
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
            $form->select('user_name',__('当前适配状态责任人'))->options(function (){
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
        
            $form->saving(function (Form $form) {
                $database_name = env('DB_DATABASE');
                $status_coming = $form->statuses_id;
                
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
                    $status_current = $form->model()->statuses_id;
                }
                
                if ($status_coming != $status_current || $form->statuses_comment) {
                    PbindHistory::create([
                        'pbind_id' => $id,
                        'status_old' => $status_current,
                        'status_new' => $status_coming,
                        'user_name' => Admin::user()->name,
                        'comment' => $form->statuses_comment,
                    ]);
                }
                $form->deleteInput('statuses_comment');
            });
        });
    }
    public function pPaginate(Request $request)
    {
        $q = $request->get('q');

        return Peripheral::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

}
