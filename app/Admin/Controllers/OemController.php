<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Exports\OemExport;
use App\Admin\Actions\Modal\OemModal;
use App\Admin\Renderable\ChipTable;
use App\Admin\Renderable\OhistoryTable;
use App\Admin\Renderable\ReleaseTable;
use App\Admin\Renderable\StatusTable;
use App\Admin\Utils\ContextMenuWash;
use App\Models\Chip;
use App\Models\Oem;
use App\Models\Release;
use App\Models\Status;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Metrics\Card;

class OemController extends AdminController
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

        return Grid::make(Oem::with(['manufactors','otypes','releases','chips','status']), function (Grid $grid) {

            $grid->tools(function  (Grid\Tools  $tools)  { 
                if(Admin::user()->can('oems-import'))
                {
                    $tools->append(new OemModal()); 
                }
            });

            if(Admin::user()->can('oems-export'))
            {
                $grid->export(new OemExport());
            }

            $grid->column('id')->sortable()->hide();
            $grid->column('manufactors.name',__('厂商'));
            $grid->column('name');
            $grid->column('otypes.name', __('类型'));
            $grid->column('source');
            $grid->column('details')->display('查看') // 设置按钮名称
            ->modal(function ($modal) {
                // 设置弹窗标题
                $modal->title('产品描述');
        
                $card = new Card($this->details);
        
                return "<div style='padding:10px 10px 0'>$card</div>";
            });

            $grid->column('releases.name',__('操作系统版本'));
            $grid->column('os_subversion');
            $grid->column('chips.name',__('芯片'));
            $grid->column('status_parent', __('当前适配状态'))->display(function () {
                $a = $this->status_id;
                if($a > 5){return Status::where('id',$this->status->parent)->pluck('name')->first();}
                if($a < 6){return Status::where('id',$a)->pluck('name')->first();}
            });
            $grid->column('status_name', __('当前细分适配状态'))->display(function () {
                $a = $this->status_id;
                if($a > 5){return Status::where('id',$a)->pluck('name')->first();}
            });
            $grid->column('user_name');
            $grid->column('histories')
                ->display('查看')
                ->modal(function () {
                    return OhistoryTable::make();
                });
            $grid->column('class');
            $grid->column('test_type');
            $grid->column('kylineco')->display(function ($value) {
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
            $grid->column('industries');   
            $grid->column('patch');
            $grid->column('start_time')->date();
            $grid->column('complete_time')->date();
            $grid->column('motherboard')->hide();
            $grid->column('gpu')->hide();
            $grid->column('graphic_card')->hide();
            $grid->column('ai_card')->hide();
            $grid->column('network')->hide();
            $grid->column('memory')->hide();
            $grid->column('raid')->hide();
            $grid->column('hba')->hide();
            $grid->column('hard_disk')->hide();
            $grid->column('firmware')->hide();
            $grid->column('sound_card')->hide();
            $grid->column('parallel')->hide();
            $grid->column('serial')->hide();
            $grid->column('isolation_card')->hide();
            $grid->column('other_card')->hide();
            $grid->column('comment')->hide();
            $grid->column('created_at')->hide();
            $grid->column('updated_at')->hide();

            //各种设置
            $grid->showColumnSelector();
            $grid->disableEditButton();
            $grid->disableCreateButton();
            $grid->paginate(10);
            $grid->setActionClass(Grid\Displayers\ContextMenuActions::class);
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->like('name')->width(3);;

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

                $filter->where('statuses',function ($query){
                    $query->whereHas('status', function ($query){
                       $query->where('parent', $this->input)->orWhere('id', $this->input);
                    });
                },__('适配状态'))->select(Status::where('parent',null)
                ->pluck('name','id'))
                ->width(3);

                $filter->where('oem',function ($query){      
                    $query->whereHas('otypes', function ($query){
                        if($this->input>7){$query->where('id', $this->input);}
                        elseif($this->input == 0){}
                        else{$query->where('parent', $this->input);}
                    });                 
                },'整机类型')->select(config('admin.database.otypes_model')::selectOptions())
                ->width(3);

                $filter->equal('test_report',_('是否有测试报告'))->select([1 => '有',0 => '无'])->width(3);
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
        return Show::make($id, Oem::with(['manufactors','otypes','releases','chips','status']), function (Show $show) {

            $show->disableEditButton();

            $show->field('manufactors.name', __('厂商'));
            $show->field('name');
            $show->field('otypes.name', __('类型'));
            $show->field('source');
            $show->text('details');
            $show->field('releases.name',__('操作系统版本'));
            $show->field('os_subversion');
            $show->field('chips.name',__('芯片'));
            $show->field('status_parent', __('当前适配状态'))->as(function () {
                $a = $this->status_id;
                if($a > 5){return Status::where('id',$this->status->parent)->pluck('name')->first();}
                if($a < 6){return Status::where('id',$a)->pluck('name')->first();}
            });
            $show->field('status_name', __('当前细分适配状态'))->as(function () {
                $a = $this->status_id;
                if($a > 5){return Status::where('id',$a)->pluck('name')->first();}
            });
            $show->field('user_name');
            $show->field('class');
            $show->field('test_type');
            $show->field('kylineco')->as(function ($kylineco) {
                if ($kylineco == '1') { return '是'; }
                elseif ($kylineco == '0') { return '否'; }
            });
            $show->field('iscert')->as(function ($iscert) {
                if ($iscert == '1') { return '是'; }
                elseif ($iscert == '0') { return '否'; }
            });
            $show->field('test_report')->as(function ($iscert) {
                if ($iscert == '1') { return '是'; }
                elseif ($iscert == '0') { return '否'; }
            });;
            $show->field('certificate_NO');
            $show->field('industries'); 
            $show->field('patch');
            $show->field('start_time');
            $show->field('complete_time');
            $show->field('motherboard');
            $show->field('gpu');
            $show->field('graphic_card');
            $show->field('ai_card');
            $show->field('network');
            $show->field('memory');
            $show->field('raid');
            $show->field('hba');
            $show->field('hard_disk');
            $show->field('firmware');
            $show->field('sound_card');
            $show->field('parallel');
            $show->field('serial');
            $show->field('isolation_card');
            $show->field('other_card');
            $show->field('comment');
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
        return Form::make(new Oem(), function (Form $form) {
            $form->display('id');
            $form->text('manufactor_id');
            $form->text('name');
            $form->text('type_id');
            $form->text('source');
            $form->text('details');
            $form->text('release_id');
            $form->text('os_subversion');
            $form->text('chip_id');
            $form->text('status_id');
            $form->text('user_name');
            $form->text('class');
            $form->text('test_type');
            $form->text('kylineco');
            $form->text('iscert');
            $form->text('test_report');
            $form->text('certificate_NO');
            $form->text('industries'); 
            $form->text('patch');
            $form->text('start_time');
            $form->text('complete_time');
            $form->text('motherboard');
            $form->text('gpu');
            $form->text('graphic_card');
            $form->text('ai_card');
            $form->text('network');
            $form->text('memory');
            $form->text('raid');
            $form->text('hba');
            $form->text('hard_disk');
            $form->text('firmware');
            $form->text('sound_card');
            $form->text('parallel');
            $form->text('serial');
            $form->text('isolation_card');
            $form->text('other_card');
            $form->text('comment');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
