<?php

namespace App\Admin\Actions\Others;

use Dcat\Admin\Grid\RowAction;

class JumpInfo extends RowAction
{
    
    /**
     * @return string
     */
    public function title()

    {
        return '处理';
    }

    public function href()
    {
        $id = $this->getKey();
        if     ($this->row->type == "软件适配") { $type = 'sbinds'; }
        elseif ($this->row->type == "外设适配") { $type = 'pbinds'; }
        elseif ($this->row->type == "软件需求") { $type = 'srequests'; }
        elseif ($this->row->type == "外设需求") { $type = 'prequests'; }
        return admin_url($type.'/'.$id);
    }
}