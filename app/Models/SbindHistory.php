<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SbindHistory extends Model
{
    use HasDateTimeFormatter;

    public function sbind()
    {
        return $this->belongsTo(Sbind::class);
    }
}
