<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SRequestHistory extends Model
{
	use HasDateTimeFormatter;
	
	protected $table = 's_request_histories';
}