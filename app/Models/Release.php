<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
	use HasDateTimeFormatter; 

	protected $table = 'releases';

	protected $fillable = 
	[
		'name',
		'abbr',
		'release_date',
		'eosl_date',
	];

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }
}
