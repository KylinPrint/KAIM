<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SolutionMatch extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'solution_matches';
    
}
