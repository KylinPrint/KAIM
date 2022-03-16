<?php

namespace App\Models;

use Dcat\Admin\Admin;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class PbindHistory extends Model
{
    use HasDateTimeFormatter;

    public function pbinds()
    {
        return $this->belongsTo(Pbind::class);
    }

    public function admin_users()
    {
        return $this->belongsTo(Admin::class);
    }

    public function status_old()
    {
        return $this->belongsTo(Status::class);
    }

    public function status_new()
    {
        return $this->belongsTo(Status::class);
    }
}
