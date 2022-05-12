<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
	use HasDateTimeFormatter;    

	protected $table = 'statuses';

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
