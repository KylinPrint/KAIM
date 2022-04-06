<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SRequest extends Model
{
	use HasDateTimeFormatter;

	public function stype() { return $this->belongsTo(Stype::class); }

	public function release() { return $this->belongsTo(Release::class); }

	public function chip() { return $this->belongsTo(Chip::class); }

	public function bd() { return $this->belongsTo(AdminUser::class); }
}
