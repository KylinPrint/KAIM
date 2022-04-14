<?php

namespace App\Admin\Controllers;

use App\Models\Type;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class TypeController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Type(), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->column('parent')->display(function($parent){
                return Type::where('id',$parent)->pluck('name')->first();
            });
            $grid->column('name');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->quickSearch('name');
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
        return Show::make($id, new Type(), function (Show $show) {
            // $show->field('id');
            $show->field('name');
            $show->field('parent');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Type(), function (Form $form) {
            // $form->display('id');

            if($form->isEditing())
            {
                $form->select('parent')->options(Type::where('parent', 0)->pluck('name','id'))->load('name','/api/type');
                $form->select('name')->options(
                    function (){

                        $a = Type::all()->where('parent',$this->parent);

                        if($a){
                            $arr = array();
  
                            foreach($a as $b){
                                $arr = $arr + [$b->id => $b->name];
                            }
                            return $arr;
                        }
                    }
                );
            }
            else
            {
                $form->select('parent')->options(Type::where('parent', 0)->pluck('name','id'));
                $form->text('name');
            }          
        });
    }

    public function getName(Request $request)
    {
        $provinceId = $request->get('q')?:0;
        return Type::where('parent', $provinceId)->get(['id', \Illuminate\Support\Facades\DB::raw('name as text')])->toArray();
        
    }

}
