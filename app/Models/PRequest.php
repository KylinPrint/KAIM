<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class PRequest extends Model
{
	use HasDateTimeFormatter;

	public function type() { return $this->belongsTo(Type::class); }

	public function release() { return $this->belongsTo(Release::class); }

	public function chip() { return $this->belongsTo(Chip::class); }

	public function bd() { return $this->belongsTo(AdminUser::class); }

	public function pbinds() { return $this->belongsTo(Pbind::class); }
}
