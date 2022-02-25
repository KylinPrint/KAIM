<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Peripheral extends Model
{
	use HasDateTimeFormatter;
	
	protected $table = 'peripherals';

	protected $fillable = 
	[
		'name',
		'brands_id',
		'types_id',
		'release_date',
		'eosl_data',
	];

	public function brands()
    {
        return $this->belongsTo(Brand::class);
    }

	public function types()
    {
        return $this->belongsTo(Type::class);
    }

	public function values()
    {
        return $this->hasMany(Value::class, 'peripherals_id');
    }

}
