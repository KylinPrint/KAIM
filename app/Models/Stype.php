<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Stype extends Model
{
	use HasDateTimeFormatter;    

	protected string $parentColumn = 'parent';

	protected string $titleColumn = 'name';
}
