<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class PeripheralIndustry extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'peripheral_industry';
    
}
