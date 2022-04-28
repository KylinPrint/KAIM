<?php

namespace App\Admin\Controllers;

use App\Models\Brand;
use App\Models\Manufactor;
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
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Dropdown;

class PeripheralController extends AdminController
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

    public function index(Content $content)
    {
        // 生成下拉菜单选项
        $types = array();
        $options = array();
        foreach (Type::select('id', 'name', 'parent')->get()->toarray() as $query) {
            if ($query['parent']) {
                array_push($options, $query['id']);
            }
            $types[$query['id']]['name'] = $query['name'];
            $types[$query['id']]['parent'] = $query['parent'];
        }

        // type_id设置默认值防止不带参数访问外设页面
        $type_id = $this->urlQuery('type') ?? $options[0];
        
        // 创建下拉菜单
        $dropdown = Dropdown::make($options)
            ->button('选择外设分类') // 设置按钮
            ->buttonClass('btn btn-white  waves-effect') // 设置按钮样式
            ->click($types[$types[$type_id]['parent']]['name'] . ' -> ' . $types[$type_id]['name']) // 默认选项
            ->map(function ($id) use ($types) {
                // 格式化菜单选项
                $url = admin_url('peripherals?type='.$id);
                $label = $types[$types[$id]['parent']]['name'] . ' -> ' . $types[$id]['name'];
                return "<a href='$url'>{$label}</a>";
            });
        
        return $content
            ->header('外设')
            ->description('列表')
            // 暂时隐藏下拉菜单
            ->body($dropdown)
            ->body('<p>')
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

            $grid->paginate(10);
            // 没说就是激光打印机
            // TODO 直接赋6有点脑瘫
            $type_id = $this->urlQuery('type') ?? 6;

            $grid->model()->setConstraints([
                'type' => $type_id,
            ]);
            $grid->model()->where('types_id',$type_id);
            // 默认按创建时间倒序排列
            $grid->model()->orderBy('created_at', 'desc');

            // $grid->column('id')->sortable();
            $grid->column('manufactors.name',__('厂商'));
            $grid->column('brands_id', __('品牌'))->display(function ($brands_id) {
                $brand = Brand::find($brands_id);
                if (!$brand->name) { return $brand->name_en; }
                elseif (!$brand->name_en) { return $brand->name; }
                else { return $brand->name . '(' . $brand->name_en . ')'; }
            });
            $grid->column('name');
            $grid->column('types.name', __('类型'));
            $grid->column('industries')->badge();
            $grid->column('vid');
            $grid->column('pid');
            $grid->column('model');
            $grid->column('release_date');
            $grid->column('eosl_date');

            $specs = Specification::where('types_id',$type_id)->get(['id', 'name', 'isrequired', 'field'])->toArray();
            
            foreach ($specs as $value)
            {
                $grid->column($value['name'])->display(function() use ($value) {
                    $res = Value::where([['peripherals_id',$this->id], ['specifications_id',$value['id']]])->pluck('value')->first();
                    //处理布尔值
                    if ($value['field'] == 2) {
                        if ($res == "0") { return '否'; }
                        elseif ($res == "1") { return '是'; }
                    } 
                    else { return $res; }
                });
            }

            $grid->column('bd');
            $grid->column('am');
            $grid->column('tsm');

            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->quickSearch('name', 'industries', 'comment');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->like('name','产品名称');
                $filter->like('brands.name','品牌');
                $filter->like('comment','备注');
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
        return Show::make($id, Peripheral::with(['brands','types','manufactors']), function (Show $show) {
            // $show->field('id');
            $show->field('name');
            $show->field('manufactors.name', __('厂商'));
            $show->field('brands.id', __('品牌'))->as(function ($brands_id) {
                $brand = Brand::find($brands_id);
                if (!$brand->name) { return $brand->name_en; }
                elseif (!$brand->name_en) { return $brand->name; }
                else { return $brand->name . '(' . $brand->name_en . ')'; }
            });
            $show->field('types.name', __('类型'));
            $show->field('industries')->as(function ($industries) { return explode(',', $industries); })->badge();
            $show->field('vid');
            $show->field('pid');
            $show->field('release_date');
            $show->field('eosl_date');
            $show->field('bd');
            $show->field('am');
            $show->field('tsm');

            $show->binds(__('参数'), function ($model) {
                $grid = new Grid(Value::with(['peripherals','specifications']));
    
                $grid->model()->where('peripherals_id', $model->id);
                
                $grid->column('specifications.name', __('参数名'));
                $grid->column('value', __('参数'));

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
        return Form::make(Peripheral::with(['manufactors', 'brands', 'types', 'values']), function (Form $form) {
            $id = $form->model()->id;
            // TODO 参数的新增和修改好像哪里有问题
            if ($form->isEditing()) {
                $form->display('types_id', __('类型'))->with(function ($types_id) {
                    return Type::where('id', $types_id)->pluck('name')->first();
                });
            } else {
                $form->hidden('types_id')->default($this->urlQuery('type'));
                $form->title(Type::where('id', $this->urlQuery('type'))->pluck('name')->first());
            }
            
            $form->select('manufactors_id', __('厂商'))->options(Manufactor::all()->pluck('name','id'));
            $form->select('brands_id', __('品牌'))
                ->options(function () {
                    foreach (Brand::select('id', 'name', 'name_en')->get()->toarray() as $value) {
                        if (!$value['name']) { $options[$value['id']] = $value['name_en']; }
                        elseif (!$value['name_en']) { $options[$value['id']] = $value['name']; }
                        else { $options[$value['id']] = $value['name'] . '(' . $value['name_en'] . ')'; }
                    }
                    return $options;
                })
                ->required();
            $form->text('name')->required()->rules("unique:peripherals,name,$id", [ 'unique' => '该外设名已存在' ]);
            $form->tags('industries')->options(config('kaim.industry'))->saving(function ($value) { return implode(',', $value); })->required();
            $form->text('vid');
            $form->text('pid');
            $form->text('model');
            $form->date('release_date')->format('YYYY-MM-DD');
            $form->date('eosl_date')->format('YYYY-MM-DD');
            $form->text('bd')->required();
            $form->text('am');
            $form->text('tsm');

            if ($form->isCreating()) {
                // 脑瘫参数写法
                $specs = Specification::where('types_id', $this->urlQuery('type'))->get(['id', 'name', 'isrequired', 'field'])->toArray();
                foreach ($specs as $value) {
                    if ($value['isrequired'] == 0) 
                    {
                        if ($value['field'] == 0) {
                            $form->text($value['id'], $value['name']);
                        }
                        elseif ($value['field'] == 1) {
                            $form->number($value['id'], $value['name']);
                        }
                        elseif ($value['field'] == 2) {
                            $form->radio($value['id'], $value['name'])->options(['0' => '否', '1'=> '是']);
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
                            $form->radio($value['id'], $value['name'])->options(['0' => '否', '1'=> '是'])->required();
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

                    foreach ($specs as $value) {
                        $a = $form->input($value['id']);
                        if ($a != "")
                        {
                            DB::table('values')->insert([
                                'peripherals_id' => $newID,
                                'specifications_id' => $value['id'],
                                'value' => $form->input($value['id']),
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp,
                            ]);
                        }
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
