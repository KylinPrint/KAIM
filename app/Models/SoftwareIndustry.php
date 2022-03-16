<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SoftwareIndustry extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'software_industry';
    
}
