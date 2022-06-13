<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OemHistory extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'oem_histories';

    protected $fillable = [
        'oem_id',
        'status_old',
        'status_new',
        'user_name',
        'comment',
    ];

    public function oems()
    {
        return $this->belongsTo(Oem::class);
    }

    public function status_old()
    {
        return $this->belongsTo(Status::class, 'status_old');
    }

    public function status_new()
    {
        return $this->belongsTo(Status::class, 'status_new');
    }
    
}