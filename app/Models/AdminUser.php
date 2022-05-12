<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
	use HasDateTimeFormatter;

    protected $fillable = [
        'name',
    ];
	
    protected $table = 'admin_users';
}
