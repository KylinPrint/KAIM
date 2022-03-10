<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class PbindHistory extends Model
{
    use HasDateTimeFormatter;

    public function pbind()
    {
        return $this->belongsTo(Pbind::class);
    }
}
