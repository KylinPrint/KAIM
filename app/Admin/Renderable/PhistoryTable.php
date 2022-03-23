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
        
        $query = PbindHistory::with(['admin_users_id', 'status_old', 'status_new'])
            ->where('pbind_id', $id)
            ->get()
            ->toArray();

        $data = array();
        foreach ($query as $key => $value) {
            $data[$key]['admin_users_id'] = $value['admin_users_id']['name'];
            if ($value['status_old'] == NULL) {
                $data[$key]['status_old'] = '无';
            } else {
                $data[$key]['status_old'] = $value['status_old']['name'];
            }
            
            $data[$key]['status_new'] = $value['status_new']['name'];
            $data[$key]['comment'] = $value['comment'];

            $data[$key]['updated_at'] = $value['updated_at'];
        }
        
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