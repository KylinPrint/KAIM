<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Manufactor extends Model
{
	use HasDateTimeFormatter;    

	protected $table = 'manufactors';

	protected $fillable = 
	[
		'name',
		'isconnected',
	];

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }
}
