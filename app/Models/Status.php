<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
	use HasDateTimeFormatter,
	ModelTree {
		ModelTree::boot as treeBoot;
}     

	protected string $parentColumn = 'parent';

	protected string $titleColumn = 'name';

	protected string $orderColumn = 'order';

	protected static function boot()
    {
        static::treeBoot();
    }    

	protected $table = 'statuses';

	protected $fillable = 
	[
		'name',
		'parent',
	];

	public function peripherals()
    {
        return $this->hasMany(Peripheral::class);
    }
}
