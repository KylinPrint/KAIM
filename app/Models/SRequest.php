<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SRequest extends Model
{
	use HasDateTimeFormatter;

	protected $fillable = 
	[
		'source',
		'manufactor',
		'name',
		'version',
		'stype_id',
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
		'creator',
		'requester_name',
		'requester_contact',
		'status',
		'bd_id',
		'sbind_id',
		'comment',
	];

	public function stype() { return $this->belongsTo(Stype::class); }

	public function release() { return $this->belongsTo(Release::class); }

	public function chip() { return $this->belongsTo(Chip::class); }

	public function bd() { return $this->belongsTo(AdminUser::class); }

	public function sbinds() { return $this->belongsTo(Sbind::class, 'sbind_id'); }
}
