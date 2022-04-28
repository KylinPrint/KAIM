<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Pbind extends Model
{
	use HasDateTimeFormatter;    

	protected $fillable = 
	[
		'peripherals_id',
		'releases_id',
		'os_subversion',
		'chips_id',	
		'os_subversion',
		'statuses_id',
		'class' ,
		'solution_name' ,
		'solution' ,
		'comment' ,
		'adapt_source' ,
		'adapted_before' ,
		'user_name' ,
		'adaption_type' ,
		'test_type' ,
		'kylineco' ,
		'appstore' ,
		'iscert' ,
		'start_time' ,
		'complete_time' ,
	];

	public function peripherals()
    {
        return $this->belongsTo(Peripheral::class);
    }

	public function releases()
    {
        return $this->belongsTo(Release::class);
    }

	public function chips()
	{
		return $this->belongsTo(Chip::class);
	}

	public function statuses()
	{
		return $this->belongsTo(Status::class);
	}

	public function admin_users()
	{
		return $this->belongsTo((AdminUser::class));
	}

	public function histories()
	{
		return $this->hasMany(PbindHistory::class);
	}
}
