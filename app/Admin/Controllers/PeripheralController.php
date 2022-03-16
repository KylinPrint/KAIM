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


class PeripheralController extends AdminController
{
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
                    $filter->equal('id');
                });
            }

            else
            {
                //$typeID = Type::where('name',$param)->pluck('id')->first();
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

                $spcificArr = Specification::where('types_id',$param)->pluck('name','id');  //获取对应type的specification数据，格式为键名为id，键值为name的数组

                foreach($spcificArr as $id => $name)
                {
                    $grid->column($name)->display(function() use ($id)
                    {
                        return Value::where([['peripherals_id',$this->id],['specifications_id',$id]])->pluck('value')->first();
                    });
                }

                $grid->column('created_at');
                $grid->column('updated_at')->sortable();
            
                $grid->filter(function (Grid\Filter $filter) {
                    $filter->equal('id');
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
            
            if($form->isEditing())
            {
                $form->display('id');
                $typesID = $form->model()->types_id;
                $form->text('name');
                $form->select('brands_id', __('品牌'))->options(Brand::all()->pluck('name','id'));
                $form->select('types_id', __('类型'))->options(Type::where('parent','!=',null)->pluck('name','id'))->disable();
                $form->date('release_date')->format('YYYY-MM-DD');
                $form->date('eosl_date')->format('YYYY-MM-DD');

                
                $form->hasMany('values', '参数', function (Form\NestedForm $form) use ($typesID){

                    $form->select('specifications_id',__('specifications name'))
                        ->options(Specification::where('types_id',$typesID)
                        ->pluck('name', 'id'))
                        ->disable();
                    $form->text('value');

                })->useTable();
                
            }    
            
            elseif($form->isCreating()){

                $urlArr = explode('type=',URL::full());
                $param = end($urlArr);
                $form->display('id');
                $form->text('name');
                $form->select('brands_id', __('品牌'))->options(Brand::all()->pluck('name','id'));
                $form->hidden('types_id')->default($param);
                $form->date('release_date')->format('YYYY-MM-DD');
                $form->date('eosl_date')->format('YYYY-MM-DD');

                $spcificArr = Specification::where('types_id',$param)->pluck('name','id');  //获取对应type的specification数据，格式为键名为id，键值为name的数组
                
                $form->hasMany('values', '参数', function (Form\NestedForm $form) use ($param) {

                    $form->select('specifications_id',__('specifications name'))
                        ->options(Specification::where('types_id',$param)
                        ->pluck('name', 'id'));
                    $form->text('value');

                })->useTable();
                
                

                $form->confirm('?','content');
                $form->saved(function (Form $form){
                    $types_id = $form->input('types_id');
                    $peripherals_id = DB::getPdo()->lastInsertId();
                    return $form->response()->redirect('values/create?typy='.$types_id.'&peripherals_id='.$peripherals_id);
                });
            }    
        });
    }
}
