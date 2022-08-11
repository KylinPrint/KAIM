<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\PbindExport;
use App\Admin\Actions\Exports\PbindTemplateExport;
use App\Admin\Actions\Grid\PbindTemplateExportTool;
use App\Admin\Actions\Grid\ShowAudit;
use App\Admin\Actions\Modal\ImportModal;
use App\Admin\Actions\Others\StatusBatch;
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
use App\Admin\Renderable\SolutionTable;
use App\Admin\Renderable\StatusTable;
use App\Admin\Utils\ContextMenuWash;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Manufactor;
use App\Models\Type;
use Dcat\Admin\Admin;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use OwenIt\Auditing\Models\Audit;

class PbindController extends AdminController
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
        
        return Grid::make(Pbind::with(['peripherals','releases','chips','statuses','admin_users']), function (Grid $grid) {         

            // 工具栏
            $grid->tools(function (Grid\Tools $tools) {
                // 导入
                if(Admin::user()->can('pbinds-import')) {
                    $tools->append(new ImportModal('pbinds','p_import')); 
                }
                
                // 批量操作
                $tools->batch(function ($batch) {
                    // 批量修改状态
                    if(Admin::user()->can('pbinds-edit')) {
                        $batch->add(new StatusBatch('pbind'));
                    }
                });
                
                if(Admin::user()->can('pbinds-export')) { $tools->append(new PbindTemplateExportTool('生态模板外设数据导出测试', '按导入模板导出数据')); }
            });

            // 行操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 复制按钮
                if (Admin::user()->can('pbinds-edit')) {
                    $actions->append('<a href="' . admin_url('pbinds/create?template=') . $this->getKey() . '"><i class="fa fa-copy"></i> 复制</a>');
                }
                // 查看历史
                $actions->append(new ShowAudit());
            });

            if (Admin::user()->cannot('pbinds-edit')) {
                $grid->disableCreateButton();
                $grid->disableEditButton();
            }
            if (Admin::user()->cannot('pbinds-delete')) {
                $grid->disableDeleteButton();
            }

            if(Admin::user()->can('pbinds-export')) { $grid->export(new PbindExport()); }

            if(Admin::user()->cannot('pbinds-action')) { $grid->disableActions(); }
         
        
            $grid->showColumnSelector();  //后期可能根据权限显示

            $grid->column('peripherals.manufactors_id',__('厂商'))->display(function ($manufactors_id){
                if($manufactors_id){
                    $manufactor = Manufactor::find($manufactors_id);
                    return $manufactor->name;
                }
            });
            $grid->column('peripherals.brands_id',__('品牌'))->display(function ($brands_id) {
                $brand = Brand::find($brands_id);
                if (!$brand->name) { return $brand->name_en; }
                elseif (!$brand->name_en) { return $brand->name; }
                else { return $brand->name . '(' . $brand->name_en . ')'; }
            });
            $grid->column('peripherals.name',__('产品名称'));
            // 脑瘫代码
            $grid->column('peripherals.types_id',__('产品名称'))->display(function ($type) {
                $curType = Type::where('id',$type)->first();
                $curParentTypeName = Type::where('id',$curType->parent)->pluck('name')->first();
                if($curParentTypeName){
                    $print = '外设/'.$curParentTypeName.'/'.$curType->name;
                }else{
                    $print = '外设/' .$curType->name.'/';
                }
                return $print;
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
            $grid->column('statuses_comment');
            $grid->column('admin_user_id')->display(function ($admin_user_id) {
                return $admin_user_id ? AdminUser::find($admin_user_id)->name : '';
            });
            
            $grid->column('solution',__('适配方案'))
                ->if(function ($column){
                    return $column->getValue();
                })
                ->display('详情')
                ->expand(function (){
                    $solution = $this->solution ?: '';
                    $solution_name = $this->solution_name ?: '';
                    return SolutionTable::make(['solution' => $solution,'solution_name' => $solution_name]);
                })
                ->else()
                ->display('');

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
            $grid->column('updated_at')->sortable();

            //各种设置
            $grid->scrollbarX();
            $grid->showColumnSelector();
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
            $grid->paginate(10);
            // 默认按创建时间倒序排列
            $grid->model()->orderBy('created_at', 'desc');
            
           
            // $grid->quickSearch('peripherals.name', 'solution', 'comment');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->where('pname',function ($query){
                    $query->whereHas('peripherals', function ($query){
                        $query->where('name', 'like','%'.$this->input.'%');
                    });
                },'产品型号')->width(3);

                $filter->where('brand', function ($query) {
                    $query->whereHas('peripherals', function ($query) {
                        $query->whereHas('brands', function ($query) {
                            $query->where('name', 'like',"%{$this->input}%")
                                ->orWhere('name_en','like',"%{$this->input}%");
                        });
                    });
                }, '品牌')->width(3);

                $filter->where('manufactor', function ($query) {
                    $query->whereHas('peripherals', function ($query) {
                        $query->whereHas('manufactors', function ($query) {
                            $query->where('name', 'like',"%{$this->input}%");
                        });
                    });
                }, '厂商')->width(3);

                $filter->like('solution')->width(3);

                // 树状下拉  这块有待优化
                $filter->where('pbind',function ($query){
                    $query->whereHas('peripherals', function ($query){
                        $query->whereHas('types', function ($query){
                            if(Type::where('id',$this->input)->pluck('parent')->first() != 0){$query->where('id', $this->input);}
                            elseif($this->input == 0){}
                            else{$query->where('parent', $this->input);}
                        });
                    });
                },'外设类型')->select(config('admin.database.types_model')::selectOptions())
                ->width(3);

                $filter->in('releases.id', '操作系统版本')
                    ->multipleSelectTable(ReleaseTable::make(['id' => 'name']))
                    ->title('操作系统版本')
                    ->dialogWidth('50%')
                    ->model(Release::class, 'id', 'name')
                    ->width(3);

                $filter->in('chips.id', '芯片')
                    ->multipleSelectTable(ChipTable::make(['id' => 'name']))
                    ->title('芯片')
                    ->dialogWidth('50%')
                    ->model(Chip::class, 'id', 'name')
                    ->width(3);

                $filter->where('status',function ($query){
                    $query->whereHas('statuses', function ($query){
                        if(Status::where('id',$this->input)->pluck('parent')->first() != 0){$query->where('id', $this->input);}
                        elseif($this->input == 0){}
                        else{$query->where('parent', $this->input);}
                    });
                    
                },'适配状态')->select(config('admin.database.statuses_model')::selectOptions())
                ->width(3);


                $filter->equal('adaption_type',__('适配类型'))->select(config('kaim.adaption_type'))->width(3);

                $filter->whereBetween('created_at', function ($query) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                
                    if ($start !== null) { $query->whereDate('created_at', '>=', $start);}
            
                    if ($end !== null) {$query->whereDate('created_at', '<=', $end);}
            
                })->date()->width(3);

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

                $filter->equal('appstore', __('是否上架'))->select(['1' => '是' , '0' => '否'])->width(3);
                $filter->equal('iscert')->select(['1' => '是' , '0' => '否'])->width(3);
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
            $show->field('peripherals.brands_id', __('品牌'))->as(function ($brand_id) {
                $brand = Brand::find($brand_id);
                if (!$brand->name) { return $brand->name_en; }
                elseif (!$brand->name_en) { return $brand->name; }
                else { return $brand->name . '(' . $brand->name_en . ')'; }
            });
            $show->field('peripherals',__('产品名称'))->as(function ($peripherals) {
                return "<a href=" . admin_url('peripherals/' . $peripherals["id"]) . ">" . $peripherals["name"] . "</a>";
            })->link();
            $show->field('peripherals.types_id',__('外设类型'))->as(function ($type) {
                $curType = Type::where('id',$type)->first();
                $curParentTypeName = Type::where('id',$curType->parent)->pluck('name')->first();
                if($curParentTypeName){
                    $print = '外设/'.$curParentTypeName.'/'.$curType->name;
                }else{
                    $print = '外设/' .$curType->name.'/';
                }
                return $print;
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
            $show->field('admin_user_id')->as(function ($admin_user_id) {
                return $admin_user_id ? AdminUser::find($admin_user_id)->name : '';
            });
            $show->field('creator', '适配数据创建人')->as(function () {
                $admin_user_id = Audit::where([
                    'event'             => 'created',
                    'auditable_type'    => 'App\Models\Pbind',
                    'auditable_id'      => $this->id,
                ])->first();
                // 防止出现空值页面直接报错
                return $admin_user_id ? AdminUser::find($admin_user_id->admin_user_id)->name : '';
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

            $show->panel()->tools(function ($tools) {
                if (Admin::user()->cannot('pbinds-edit')) { $tools->disableEdit(); }
                if (Admin::user()->cannot('pbinds-delete')) { $tools->disableDelete(); }
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
            $template = Pbind::find(request('template'));

            $form->select('peripherals_id',__('产品名称'))
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
            if ($form->isCreating()) {
                $form->multipleSelect('chips_id',__('芯片'))
                    ->options(Chip::all()->pluck('name','id'))
                    ->saving(function ($value) { return implode(',', $value); })
                    ->required()
                    ->default($template->chips_id ?? null);
            } elseif ($form->isEditing()) {
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
            $form->select('statuses_id',__('适配状态'))
                ->options(config('admin.database.statuses_model')::selectOptions())
                ->required()
                ->rules(function (){
                    $curparent = Status::where('id',request()->statuses_id)->pluck('parent')->first();
                    if ($curparent == 0) {  //TODO  有点蠢
                        return 'max:0';
                    }
                },['max' => '请选择详细状态'])
                ->default($template->statuses_id ?? null);
            $form->text('statuses_comment_temp', admin_trans('pbind.fields.statuses_comment'))
                ->default($template->statuses_comment ?? null);
            $form->hidden('statuses_comment');
            $form->select('admin_user_id')->options(AdminUser::all()->pluck('name', 'id'))
                ->default(Admin::user()->id);
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
            $form->date('start_time')
                ->default($template->start_time ?? null);
            $form->date('complete_time')
                ->default($template->complete_time ?? null);
            $form->text('comment')
                ->default($template->comment ?? null);

            $form->saving(function (Form $form) {
                if($form->isCreating()) {
                    // 读取表单数据
                    $data = $form->input();
                    // 读取多选的芯片
                    $chips_id = array_filter($data['chips_id']);
                    // 修正状态变更说明
                    $data['statuses_comment'] = $data['statuses_comment_temp'];
                    // 取消无意义的数据
                    unset($data['chips_id'], $data['statuses_comment_temp'], $data["_previous_"], $data["_token"], $data['chss']);
                    // 初始化错误信息
                    $message = array();
                    // 遍历芯片
                    foreach ($chips_id as $chip_id) {
                        $data['chips_id'] = $chip_id;
                        $chip = Chip::find($data["chips_id"]);
                        // 创建PBinds记录
                        $pbind = null;
                        try {
                            $pbind = Pbind::create($data);
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        if(!$pbind) {
                            // 返回错误
                            $message[] = $chip->name;
                        }
                    }
                    // 返回提示并跳转
                    if ($message) {
                        return $form->response()->warning('操作完成,其中"' . implode(',', $message) . '"的数据创建失败')->redirect('pbinds');
                    } else {
                        return $form->response()->success('操作完成')->redirect('pbinds');
                    }
                } else {
                    $form->statuses_comment = $form->statuses_comment_temp;
                    $form->deleteInput('statuses_comment_temp');
                }
            });
        });
    }

    public function pPaginate(Request $request)
    {
        $q = $request->get('q');

        return Peripheral::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function export(Request $request)
    {
        $filename = $request->get('filename');
        $cururl = $request->get('cururl');
        ob_end_clean();
        
        return Excel::download(new PbindTemplateExport($cururl), $filename.'.xlsx');
    }

}
