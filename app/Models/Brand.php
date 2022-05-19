<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
	use HasDateTimeFormatter;    

    protected $fillable = [
        'name',
        'name_en',
    ];

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }
}
