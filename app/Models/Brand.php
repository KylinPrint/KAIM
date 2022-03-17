<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
	use HasDateTimeFormatter;    

	public function manufactors()
	{
		return $this->belongsTo(Manufactor::class);
	}

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }
}
