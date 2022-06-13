<?php

namespace App\Models;

use Dcat\Admin\Admin;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class PbindHistory extends Model
{
    use HasDateTimeFormatter;

    protected $fillable = [
        'pbind_id',
        'status_old',
        'status_new',
        'user_name',
        'comment',
    ];

    public function pbinds()
    {
        return $this->belongsTo(Pbind::class);
    }

    public function status_old()
    {
        return $this->belongsTo(Status::class, 'status_old');
    }

    public function status_new()
    {
        return $this->belongsTo(Status::class, 'status_new');
    }

    public function admin_users_id()
    {
        return $this->belongsTo(AdminUser::class, 'admin_users_id');
    }
}