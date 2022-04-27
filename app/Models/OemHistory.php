<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OemHistory extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'oem_histories';
    
}
