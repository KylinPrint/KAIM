<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Value extends Model
{
	use HasDateTimeFormatter;    

	protected $table = 'values';

	public function peripherals()
    {
        return $this->belongsTo(Peripheral::class);
    }

	public function specifications()
    {
        return $this->belongsTo(Specification::class);
    }

}
