<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Sbind extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
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
		'statuses_comment',
		'admin_user_id',
		'softname',
		'solution',
		'solution_name',
		'class',
		'adaption_type',
		'test_type',
		'kylineco',
		'appstore',
		'iscert',
		'test_report',
		'certificate_NO',
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
		return $this->belongsTo((AdminUser::class));
	}
}
