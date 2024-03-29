<?php

namespace App\Admin\Controllers;

use App\Admin\Utils\ContextMenuWash;
use App\Models\Manufactor;
use App\Models\Software;
use App\Models\Stype;
use Dcat\Admin\Admin;
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
        // 恶人还需恶人磨
        ContextMenuWash::wash();

        return Grid::make(Software::with(['manufactors','stypes']), function (Grid $grid) {

            $grid->paginate(10);
            // 默认按创建时间倒序排列
            $grid->model()->orderBy('created_at', 'desc');
            $grid->column('name');
            $grid->column('manufactors.name',__('厂商'));
            $grid->column('version');

            $grid->column('stypes_id')->display(function ($stypes_id) {
                $curStype = Stype::where('id',$stypes_id)->first();
                $curParentStypeName = Stype::where('id',$curStype->parent)->pluck('name')->first();
                if($curParentStypeName){
                    $print = '软件/'.$curParentStypeName.'/'.$curStype->name;
                }else{
                    $print = '软件/' .$curStype->name.'/';
                }
                return $print;
                
            });
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
            
            if (Admin::user()->cannot('softwares-edit')) {
                $grid->disableCreateButton();
                $grid->disableEditButton();
            }
            if (Admin::user()->cannot('softwares-delete')) {
                $grid->disableDeleteButton();
            }
            
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();

                // 树状下拉  这块有待优化
                $TypeModel = config('admin.database.stypes_model');
                $filter->where('software',function ($query){
                    $query->whereHas('stypes', function ($query){
                        if(Stype::where('id',$this->input)->pluck('parent')->first() != 0){$query->where('id', $this->input);}
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

            $show->panel()->tools(function ($tools) {
                if (Admin::user()->cannot('softwares-edit')) { $tools->disableEdit(); }
                if (Admin::user()->cannot('softwares-delete')) { $tools->disableDelete(); }
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
        return Form::make(Software::with('manufactors','stypes'), function (Form $form) {

            $version = isset(request()->all()['version']) ? request()->all()['version'] : '';
            $manufactors_id = isset(request()->all()['manufactors_id']) ? request()->all()['manufactors_id'] : '';
            $id = isset(request()->route()->parameters['software']) ? request()->route()->parameters['software'] : '';

            $form->text('name')
            ->creationRules(['required', "unique:softwares,name,NULL,id,version,{$version},manufactors_id,{$manufactors_id}"], ['unique' => '数据已存在'])
            ->updateRules(['required', "unique:softwares,name,{$id},id,version,{$version},manufactors_id,{$manufactors_id}"], ['unique' => '数据已存在'])
            ->required();
            
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
            //TODO null会破坏唯一校验,比如两条同样数据 (微信,腾讯,null) 均可以通过当前唯一校验
            $form->text('version')->default(' ');
            
            $TypeModel = config('admin.database.stypes_model');
            $form->select('stypes_id', __('类型'))
                ->options($TypeModel::selectOptions())
                ->required()
                ->rules(function (){
                    $curparent = Stype::where('id',request()->stypes_id)->pluck('parent')->first();
                    if ($curparent == 0) {  //TODO  有点蠢
                        return 'max:0';
                    }
                },['max' => '请选择详细类别']);    
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
