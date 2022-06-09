<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Renderable\AuditTable;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;

class ShowAudit extends RowAction
{    
    public function render()
    {
        // 实例化表单类并传递自定义参数
        $grid = AuditTable::make()->payload([
            'auditable_id'      => $this->getKey(),
            'auditable_type'    => get_class($this->row)
        ]);

        return Modal::make()
            ->xl()
            ->title('查看变更历史')
            ->body($grid)   
            ->button('<a><i class="fa fa-history"></i> 历史</a>');
    }
}