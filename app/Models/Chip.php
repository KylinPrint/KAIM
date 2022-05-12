<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Chip extends Model
{
	use HasDateTimeFormatter;    

	protected $table = 'chips';

	protected $fillable = 
	[
		'name',
		'arch',
	];

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }
}
