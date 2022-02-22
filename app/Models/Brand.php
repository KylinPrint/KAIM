<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
	use HasDateTimeFormatter;    

	protected $table = 'brands';

	protected $fillable = 
	[
		'name',
		'alias',
		'manufactors_id'
	];

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }
}
