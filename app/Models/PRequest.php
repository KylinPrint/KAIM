<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class PRequest extends Model
{
	use HasDateTimeFormatter;

	protected $fillable = 
	[
		'source',
		'manufactor',
		'brand',
		'name',
		'type_id',
		'industry',
		'release_id',
		'os_subversion',
		'chip_id',
		'project_name',
		'amount',
		'project_status',
		'level',
		'manufactor_contact',
		'et',
		'requester_name',
		'requester_contact',
		'status',
		'bd_id',
		'pbind_id',
		'comment',
	];

	public function type() { return $this->belongsTo(Type::class); }

	public function release() { return $this->belongsTo(Release::class); }

	public function chip() { return $this->belongsTo(Chip::class); }

	public function bd() { return $this->belongsTo(AdminUser::class); }

	public function pbinds() { return $this->belongsTo(Pbind::class); }
}
