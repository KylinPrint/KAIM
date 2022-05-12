<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
	use HasDateTimeFormatter;

	public function types()
	{
		return $this->belongsTo(Type::class);
	}
}
