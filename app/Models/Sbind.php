<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Sbind extends Model
{
	use HasDateTimeFormatter;   
	
	protected $table = 'sbinds';

	protected $fillable = 
	[
		'softwares_id',
		'releases_id',
		'os_subversion',
		'chips_id',
		'adapt_source',
		'adapted_before',
		'statuses_id',
		'user_name',
		'softname',
		'solution',
		'class',
		'adaption_type',
		'test_type',
		'kylineco',
		'appstore',
		'iscert',
		'comment',
	];

	public function softwares()
    {
        return $this->belongsTo(Software::class);
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
		return $this->belongsTo((Administrator::class));
	}
	
	public function histories()
	{
		return $this->hasMany(SbindHistory::class);
	}
}
