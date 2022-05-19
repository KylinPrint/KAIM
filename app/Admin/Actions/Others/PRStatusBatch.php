<?php
 
namespace App\Admin\Actions\Others;

use App\Admin\Actions\Form\PRStatusBatchForm;
use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Widgets\Modal;
 
class PRStatusBatch extends BatchAction
{
	protected $title = '修改状态';
 
    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = PRStatusBatchForm::make();
 
        return Modal::make()
            ->xl()
            ->title($this->title)
            ->body($form)
            ->onLoad($this->getModalScript())
            ->button('<a><i class="feather icon-edit-1"></i> ' . $this->title . '</a>');
    }
 
 
    protected function getModalScript()
    {
        // 弹窗显示后往隐藏的id表单中写入批量选中的行ID
        return <<<JS
        // 获取选中的ID数组
        var key = {$this->getSelectedKeysScript()}
        // 与Form中隐藏字段的绑定的id一致
        $('#batch-prs-id').val(key);    
        JS;
    }
}
