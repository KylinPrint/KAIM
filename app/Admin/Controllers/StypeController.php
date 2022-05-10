<?php

namespace App\Admin\Controllers;

use App\Admin\Utils\ContextMenuWash;
use App\Models\Stype;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class StypeController extends AdminController
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
        
        return Grid::make(new Stype(), function (Grid $grid) {
            $grid->column('parent')->display(function($parent){
                return Stype::where('id',$parent)->pluck('name')->first();
            });
            $grid->column('name');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
        
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
        return Show::make($id, new Stype(), function (Show $show) {
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
        return Form::make(new Stype(), function (Form $form) {
            $curname = $form->model()->name;

            if($form->isEditing())
            {
                $form->select('parent')->options(Stype::where('parent', 0)->pluck('name','id'))->load('name','/api/stype');
                $form->select('name')->options(
                    function (){

                        $a = Stype::all()->where('parent',$this->parent);

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
                $form->select('parent')->options(Stype::where('parent', 0)->pluck('name','id'));
                $form->text('name');
            }
        });
        
    }
    public function getName(Request $request)
    {
        $provinceId = $request->get('q')?:0;
        return Stype::where('parent', $provinceId)->get(['id', \Illuminate\Support\Facades\DB::raw('name as text')])->toArray();
        
    }
}
