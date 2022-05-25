<?php
 
namespace App\Admin\Actions\Others;

use App\Admin\Actions\Form\StatusBatch\PRStatusBatchForm;
use App\Admin\Actions\Form\StatusBatch\PStatusBatchForm;
use App\Admin\Actions\Form\StatusBatch\SRStatusBatchForm;
use App\Admin\Actions\Form\StatusBatch\SStatusBatchForm;
use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Widgets\Modal;
 
class StatusBatch extends BatchAction
{
	protected $title;
    protected $source;

    public function __construct($source)
    {
        $this->source = $source;
    }
 
    public function render()
    {
        // 实例化表单类并传递自定义参数
        switch ($this->source) {
            case 'sbind':
                $form = SStatusBatchForm::make();
                $this->title = '软件适配状态修改';
                break;

            case 'pbind':
                $form = PStatusBatchForm::make();
                $this->title = '外设适配状态修改';
                break;

            case 'srequest':
                $form = SRStatusBatchForm::make();
                $this->title = '软件需求状态修改';
                break;

            case 'prequest':
                $form = PRStatusBatchForm::make();
                $this->title = '外设需求状态修改';
                break;
            
            default:
                break;
        }
 
        return Modal::make()
            ->xl()
            ->title($this->title)
            ->body($form)
            ->onLoad($this->getModalScript())
            ->button('<a><i class="feather icon-edit-1"></i> ' . '修改状态' . '</a>');
    }
 
    protected function getModalScript()
    {
        // 弹窗显示后往隐藏的id表单中写入批量选中的行ID
        return <<<JS
        // 获取选中的ID数组
        var key = {$this->getSelectedKeysScript()}
        // 与Form中隐藏字段的绑定的id一致
        $('#batch-status-id').val(key);
        JS;
    }
}
