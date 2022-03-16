<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Pbind extends Model
{
	use HasDateTimeFormatter;    

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

	public function solutions()
	{
		return $this->belongsTo(Solution::class);
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
