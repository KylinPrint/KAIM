<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
	use HasDateTimeFormatter;    

	protected $table = 'types';

	protected $fillable = 
	[
		'name',
		'parent',
	];

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }
}
