<?php

namespace App\Admin\Controllers;

use App\Models\Brand;
use App\Models\Peripheral;
use App\Models\Specification;
use App\Models\Type;
use App\Models\Value;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Dcat\Admin\Layout\Content;


class PeripheralController extends AdminController
{
    public function index(Content $content)
    {
        $urlArr = explode('type=',URL::full());
        $param = end($urlArr);
        $header = Type::where('id', $param)->pluck('name')->first();
        return $content
            ->header($header)
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Peripheral::with(['brands','types','manufactors']), function (Grid $grid){

            $urlArr = explode('type=',URL::full());
            $param = end($urlArr);

            if((ctype_alnum($param)) == 0)
            {
                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('manufactors.name',__('厂商'));
                $grid->column('brands.name', __('品牌'));
                $grid->column('types.name', __('类型'));
                $grid->column('release_date');
                $grid->column('eosl_date');
                $grid->column('comment');

                $grid->column('created_at');
                $grid->column('updated_at')->sortable();
            
                $grid->filter(function (Grid\Filter $filter) {
                    $filter->like('name');
                    $filter->like('brands.name');
                    $filter->whereBetween('created_at', function ($query) {
                        $start = $this->input['start'] ?? null;
                        $end = $this->input['end'] ?? null;
                    
                        $query->whereHas('binds', function ($query) use ($start,$end) {
                            if ($start !== null) {
                                $query->where('created_at', '>=', $start);
                            }
                    
                            if ($end !== null) {
                                $query->where('created_at', '<=', $end);
                            }
                        });
                    })->datetime()->width(3);
                });
            }

            else
            {
                $grid->model()->setConstraints([
                    'type' => $param,
                ]);
                $grid->model()->where('types_id',$param);

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('brands.name', __('品牌'));
                $grid->column('types.name', __('类型'));
                $grid->column('release_date');
                $grid->column('eosl_date');

                $specs = Specification::where('types_id',$param)->get(['id', 'name', 'isrequired'])->toArray();
                
                foreach ($specs as $key => $value)
                {
                    $grid->column($value['name'])->display(function() use ($key, $value) {
                        $res = Value::where([['peripherals_id',$this->id], ['specifications_id',$value['id']]])->pluck('value')->first();
                        //处理布尔值
                        if ($value['isrequired'] == 2) {
                            if ($res == "0") {
                                return '否';
                            } else {
                                return '是';
                            }
                        } else {
                            return $res;
                        }
                    });
                }

                $grid->column('created_at');
                $grid->column('updated_at')->sortable();
            
                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();
                    $filter->like('name','产品名称');
                    $filter->like('brands.name','品牌');
                $filter->whereBetween('created_at', function ($query) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                
                    $query->whereHas('binds', function ($query) use ($start,$end) {
                        if ($start !== null) {
                            $query->where('created_at', '>=', $start);
                        }
                
                        if ($end !== null) {
                            $query->where('created_at', '<=', $end);
                        }
                    });
                })->datetime()->width(3);
                });
            }
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
        return Show::make($id, Peripheral::with(['brands','types']), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('brands.name', __('品牌'));
            $show->field('types.name', __('类型'));

            $show->field('release_date');
            $show->field('eosl_date');

            $show->binds(__('参数'), function ($model) {
                $grid = new Grid(Value::with(['peripherals','specifications']));
    
                $grid->setActionClass(Grid\Displayers\Actions::class);
    
                $grid->model()->where('peripherals_id', $model->id);
                $grid->disableFilter();
                $grid->disableCreateButton();
                
                $grid->column('specifications.name', __('参数名'));
                $grid->column('value', __('参数'));
                $grid->disableActions();
                // $grid->actions(function ($actions) {
                //     $actions->disableDelete();
                //     $actions->disableEdit();     
                //     $actions->disableView();
                //     $curStr = '<a href = "/admin/values/'.$actions->row['solutions_id'].'">详情</a>';
                //     $actions->append($curStr);
                //     //$actions->append(new JumpInfo($actions->row['id']));
    
                // });	
                return $grid;
            });

            $show->field('created_at');
            $show->field('updated_at');

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(Peripheral::with(['brands', 'types','values']), function (Form $form) {
            
            // TODO 参数的新增和修改好像哪里有问题
            $form->display('id');
            if ($form->isEditing()) {
                $typesID = $form->model()->types_id;
                $form->select('types_id', __('类型'))->options(Type::where('parent','!=',null)->pluck('name','id'))->disable();
            } else {
                $urlArr = explode('type=',URL::full());
                $typesID = end($urlArr);
                $form->hidden('types_id')->default($typesID);
            }
            
            $form->select('brands_id', __('品牌'))->options(Brand::all()->pluck('name','id'))->required();
            $form->text('name')->required();
            $form->date('release_date')->format('YYYY-MM-DD');
            $form->date('eosl_date')->format('YYYY-MM-DD');

            if ($form->isCreating()) {
                // 脑瘫参数写法
                $specs = Specification::where('types_id', $typesID)->get(['id', 'name', 'isrequired', 'field'])->toArray();
                foreach ($specs as $key => $value) {
                    if ($value['isrequired'] == 0) 
                    {
                        if ($value['field'] == 0) {
                            $form->text($value['id'], $value['name']);
                        }
                        elseif ($value['field'] == 1) {
                            $form->number($value['id'], $value['name']);
                        }
                        elseif ($value['field'] == 2) {
                            $form->radio($value['id'], $value['name'])->options(['0' => '否', '1'=> '是'])->default('0');
                        }
                    }
                    else
                    {
                        if ($value['field'] == 0) {
                            $form->text($value['id'], $value['name'])->required();
                        }
                        elseif ($value['field'] == 1) {
                            $form->number($value['id'], $value['name'])->required();
                        }
                        elseif ($value['field'] == 2) {
                            $form->radio($value['id'], $value['name'])->options(['0' => '否', '1'=> '是'])->default('0')->required();
                        }
                    }
                }
            }

            $form->saving(function (Form $form) {
                if ($form->isCreating()) {
                    $database_name = env('DB_DATABASE');
                    $newID = DB::select("
                        SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES 
                        WHERE TABLE_SCHEMA = '$database_name' AND TABLE_NAME = 'peripherals'
                    ")[0]->AUTO_INCREMENT;
                    $timestamp = date("Y-m-d H:i:s");
                    $specs = Specification::where('types_id', $form->types_id)->get(['id'])->toArray();

                    foreach ($specs as $key => $value) {
                        DB::table('values')->insert([
                            'peripherals_id' => $newID,
                            'specifications_id' => $value['id'],
                            'value' => $form->input($value['id']),
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);
                        $form->deleteInput($value['id']);
                    }
                }
            });

            $form->saved(function (Form $form){
                return $form->response()->redirect('peripherals?type='.$form->input('types_id'));
            });
        });
    }
}
