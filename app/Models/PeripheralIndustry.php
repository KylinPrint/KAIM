<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class PeripheralIndustry extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'peripheral_industry';

    protected $fillable = 
	[
		'peripherals_id',
        'industries_id',
	];

    public function peripherals()
    {
        return $this->belongsTo(Peripheral::class);
    }

    public function industries()
    {
        return $this->belongsTo(Industry::class);
    }
    
}
