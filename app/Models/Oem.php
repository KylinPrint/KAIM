<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Oem extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	use HasDateTimeFormatter; 
	
	protected $table = 'oems';

	protected $fillable = [
		'manufactors_id',
		'name',
		'otypes_id',
		'source',
		'details',
		'releases_id',
		'os_subversion',
		'chips_id',
		'status_id',
		'admin_user_id',
		'class',
		'test_type',
		'kylineco',
		'iscert',
		'test_report',
		'certificate_NO',
		'adaption_type',
		'industries',
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

	public function otypes()
    {
        return $this->belongsTo(Otype::class);
    }

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function admin_users()
	{
		return $this->belongsTo((AdminUser::class));
	}
}
