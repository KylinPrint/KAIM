<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class PRequestHistory extends Model
{
	use HasDateTimeFormatter;
	
	protected $table = 'p_request_histories';

	protected $fillable = [
        'p_request_id',
        'status_old',
        'status_new',
        'user_name',
        'comment',
    ];

}