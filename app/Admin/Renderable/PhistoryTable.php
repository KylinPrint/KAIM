<?php

namespace App\Admin\Renderable;

use App\Models\PbindHistory;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;

class PhistoryTable extends LazyRenderable
{
    public function render()
    {
        $id = $this->key;
        
        $data = PbindHistory::where('pbind_id', $id)
            ->get(['admin_user_id', 'status_old','status_new', 'updated_at'])
            ->toArray();
        
        $title = [
            '处理人',
            '修改前状态',
            '修改后状态',
            '更新时间',
        ];

        return Table::make($title, $data);
    }
}