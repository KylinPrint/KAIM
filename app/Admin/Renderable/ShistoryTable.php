<?php

namespace App\Admin\Renderable;

use App\Models\SbindHistory;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;

class ShistoryTable extends LazyRenderable
{
    public function render()
    {
        $id = $this->key;
        
        $query = SbindHistory::with(['status_old', 'status_new'])
            ->where('sbind_id', $id)
            ->get()
            ->toArray();

        $data = array();
        foreach ($query as $key => $value) {
            $data[$key]['user_name'] = $value['user_name'];
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