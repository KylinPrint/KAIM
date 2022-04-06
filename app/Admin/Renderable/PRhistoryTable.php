<?php

namespace App\Admin\Renderable;

use App\Models\PRequestHistory;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;

class PRhistoryTable extends LazyRenderable
{
    public function render()
    {
        $id = $this->key;
        
        $data = PRequestHistory::where('p_request_id', $id)
            ->get(['operator', 'status_old', 'status_new', 'comment', 'updated_at'])
            ->toArray();
        
        $title = [
            '处理人',
            '修改前状态',
            '修改后状态',
            '状态变更说明',
            '更新时间',
        ];

        return Table::make($title, $data);
    }
}