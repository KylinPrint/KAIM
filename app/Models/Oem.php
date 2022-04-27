<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Oem extends Model
{
	use HasDateTimeFormatter;    

	protected $fillable =[
		'manufactor_id',
		'name',
		'type_id',
		'source',
		'details',
		'release_id',
		'os_subversion',
		'chip_id',
		'status_id',
		'user_name',
		'class',
		'test_type',
		'kylineco',
		'iscert',
		'patch',
		'start_time',
		'complete_time',
		'motherboard',
		'gpu',
		'graphic_card',
		'ai_card',
		'network',
		'memory',
		'raid',
		'hba',
		'hard_disk',
		'firmware',
		'sound_card',
		'parallel',
		'serial',
		'isolation_card',
		'other_card',
		'comment',
	];

	public function manufactors()
	{
		return $this->belongsTo(Manufactor::class);
	}

	public function releases()
    {
        return $this->belongsTo(Release::class);
    }

	public function chips()
	{
		return $this->belongsTo(Chip::class);
	}

	public function types()
    {
        return $this->belongsTo(Type::class);
    }

	public function statuses()
	{
		return $this->belongsTo(Status::class);
	}

	public function admin_users()
	{
		return $this->belongsTo((AdminUser::class));
	}
}
