<?php

namespace App\Admin\Controllers;

use App\Models\Manufactor;
use App\Models\Software;
use App\Models\Stype;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SoftwareController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Software::with(['manufactors','stypes']), function (Grid $grid) {

            $grid->paginate(10);
            // 默认按创建时间倒序排列
            $grid->model()->orderBy('created_at', 'desc');
            $grid->column('name');
            $grid->column('manufactors.name',__('厂商'));
            $grid->column('version');

            $grid->column('stypes.name',__('类型'));
            $grid->column('industries')->badge();
            $grid->column('appstore_soft')->display(function ($value) {
                if ($value == '1') { return '是'; }
                elseif ($value == '0') { return '否'; }
            });

            $grid->column('kernel_version');
            $grid->column('crossover_version');
            $grid->column('box86_version');
            $grid->column('bd');
            $grid->column('am');
            $grid->column('tsm');
            $grid->column('comment')->limit(50);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
            
            $grid->quickSearch('name', 'industries', 'comment');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();

                // 树状下拉  这块有待优化
                $TypeModel = config('admin.database.stypes_model');
                $filter->where('software',function ($query){
                    $query->whereHas('stypes', function ($query){
                        if($this->input > 8){$query->where('id', $this->input);}
                        elseif($this->input == 0){}
                        else{$query->where('parent', $this->input);}
                    });
                },'软件类型')->select($TypeModel::selectOptions());

                $filter->like('name','产品名称');
                $filter->like('manufactors.name','厂商');
                $filter->like('comment','备注');
                $filter->whereBetween('created_at', function ($query) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                

                    if ($start !== null) {
                        $query->where('created_at', '>=', $start);
                    }
            
                    if ($end !== null) {
                        $query->where('created_at', '<=', $end);
                    }

                })->date()->width(3);
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
        return Show::make($id, Software::with(['manufactors', 'stypes']), function (Show $show) {
            // $show->field('id');
            $show->field('name');
            $show->field('manufactors.name', __('厂商'));
            $show->field('version');
            $show->field('stypes.name', __('分类'));
            $show->field('industries')->as(function ($industries) { return explode(',', $industries); })->badge();
            $show->field('appstore_soft')->as(function ($appstore_soft) {
                if ($appstore_soft == '1') { return '是'; }
                elseif ($appstore_soft == '0') { return '否'; }
            });
            $show->field('kernel_version');
            $show->field('crossover_version');
            $show->field('box86_version');
            $show->field('bd');
            $show->field('am');
            $show->field('tsm');
            $show->field('comment');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(Software::with('manufactors','stypes'), function (Form $form) {
            $id = $form->model()->id;
            // $form->display('id');
            $form->text('name')->required()->rules("unique:softwares,name,$id", [ 'unique' => '该外设名已存在' ]);
            $form->select('manufactors_id')->options(function () {
                $manufactors = Manufactor::all()->pluck('name','id');
                $options = [ 0 => '自定义' ];
                foreach ($manufactors as $key => $value) {
                    $options[$key] = $value;
                }
                return $options;
            })
            ->when(0, function (Form $form) {
                $form->text('new_manufactor', __('新厂商名'))
                    ->rules(['unique:manufactors,name', 'required_if:manufactors_id,0'], ['unique' => '厂商已存在', 'required_if' => '请填写此字段'])
                    ->setLabelClass(['asterisk']);;
                $form->select('isconnected', __('是否建联'))->options([0 => '否', 1 => '是']);
            })
            ->required();
            $form->text('version');
            
            $TypeModel = config('admin.database.stypes_model');
            $form->select('stypes_id', __('类型'))->options($TypeModel::selectOptions())->required();    
            $form->tags('industries')->options(config('kaim.industry'))->saving(function ($value) { return implode(',', $value); })->required();
            $form->select('appstore_soft')->options([0 => '否',1 => '是']);
            $form->text('kernel_version');
            $form->text('crossover_version');
            $form->text('box86_version');
            $form->text('bd')->required();
            $form->text('am');
            $form->text('tsm');
            $form->text('comment');

            $form->saving(function (Form $form) {
                if ($form->new_manufactor) {
                    $new_manufactor = Manufactor::create([
                        'name' => $form->new_manufactor,
                        'isconnected' => $form->isconnected,
                    ]);
                    $form->manufactors_id = $new_manufactor->id;
                }
                $form->deleteInput(['new_manufactor', 'isconnected']);
            });
        });
    }
}
