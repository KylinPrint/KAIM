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

        if($this->type == 'oem'){
            $sheets[] = new OemImport();
        }
        elseif($this->type == 'pbind'){
            $sheets[] = new PbindImport();
        }
        elseif($this->type == 'sbind'){
            $sheets[] = new SbindImport();
        }
        elseif($this->type == 'prequest'){
            $sheets[] = new PRequestImport();
        }
        elseif($this->type == 'srequest'){
            $sheets[] = new SRequestImport();
        }

        return $sheets;
    }

}