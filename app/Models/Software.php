<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'softwares';


    public function manufactors()
    {
        return $this->belongsTo(Manufactor::class);
    }  
    
    public function stypes()
    {
        return $this->belongsTo(Type::class);
    } 

    public function sbinds()
    {
        return $this->hasMany(Sbind::class, 'sotfwares_id');
    }

    public function industries()
    {
        $pivotTable = 'software_industry'; // 中间表
        $relatedModel = Industry::class; // 关联模型类名
        return $this->belongsToMany($relatedModel, $pivotTable, 'softwares_id', 'industries_id')->withTimestamps();
    }
    
}
