<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
	use HasDateTimeFormatter;  
	
	protected $table = 'solutions';

	protected $fillable = 
	[
		'name',
		'details',
		'source',
	];

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }

}
