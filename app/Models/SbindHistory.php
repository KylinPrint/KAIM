<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SbindHistory extends Model
{
    use HasDateTimeFormatter;

    protected $fillable = [
        'sbind_id',
        'status_old',
        'status_new',
        'user_name',
        'comment',
    ];

    public function sbinds() { return $this->belongsTo(Sbind::class); }

    public function status_old() { return $this->belongsTo(Status::class, 'status_old'); }

    public function status_new() { return $this->belongsTo(Status::class, 'status_new'); }

    public function admin_users_id() { return $this->belongsTo(AdminUser::class, 'admin_users_id'); }
}
