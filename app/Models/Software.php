<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'softwares';

    protected $fillable = [
        'name',
        'manufactors_id',
        'version',
        'types_id',
        'packagename',
        'kernel_version',
        'crossover_version',
        'box86_version',
        'bd',
        'am',
        'tsm',
        'comment',
    ];

    public function manufactors()
    {
        return $this->belongsTo(Manufactor::class);
    }  
    
    public function types()
    {
        return $this->belongsTo(Type::class);
    } 

    public function sbinds()
    {
        return $this->hasMany(Sbind::class, 'sotfwares_id');
    }
    
}
