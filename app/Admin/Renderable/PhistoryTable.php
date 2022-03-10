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
            ->get(['pbind_id', 'statuses_id', 'updated_at'])
            ->toArray();
        
        $title = [
            '处理人',
            '状态',
            '更新时间',
        ];

        return Table::make($title, $data);
    }
}