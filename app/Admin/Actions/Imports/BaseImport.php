<?php

namespace App\Admin\Actions\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;


HeadingRowFormatter::default('none');
class BaseImport implements WithMultipleSheets
{

    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        if($this->type == 'oems'){
            $sheets[] = new OemImport();
        }
        elseif($this->type == 'pbinds'){
            $sheets[] = new PbindImport();
        }
        elseif($this->type == 'sbinds'){
            $sheets[] = new SbindImport();
        }
        elseif($this->type == 'p_requests'){
            $sheets[] = new PRequestImport();
        }
        elseif($this->type == 's_requests'){
            $sheets[] = new SRequestImport();
        }

        return $sheets;
    }

}