<?php

namespace App\Models;

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
		'chips_id',
		'solutions_id',
		'statuses_id',
		'crossover',
		'box86',
		'appstore',
		'filename',
		'source',
		'kernel_version',
		'kernel_test',
		'apptype',
		'class',
		'kylineco',
		'comment'
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

	public function solutions()
	{
		return $this->belongsTo(Solution::class);
	}

	public function statuses()
	{
		return $this->belongsTo(Status::class);
	}
}
