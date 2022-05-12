<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Otype extends Model
{
	use HasDateTimeFormatter,
		ModelTree {
			ModelTree::boot as treeBoot;
	}  

	protected $table = 'otypes';

	protected string $parentColumn = 'parent';

	protected string $titleColumn = 'name';

	protected string $orderColumn = 'order';

	protected $fillable = 
	[
		'name',
		'parent',
	];

	public function oems()
    {
        return $this->hasMany(Oem::class);
    }

	protected static function boot()
    {
        static::treeBoot();
    }    
}
