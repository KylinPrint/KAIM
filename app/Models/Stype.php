<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Stype extends Model
{
	use HasDateTimeFormatter,
	ModelTree {
		ModelTree::boot as treeBoot;
}     

	protected string $parentColumn = 'parent';

	protected string $titleColumn = 'name';

	protected string $orderColumn = 'id';

	protected static function boot()
    {
        static::treeBoot();
    }
}
