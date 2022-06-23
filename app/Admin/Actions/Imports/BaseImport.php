<?php

namespace App\Admin\Actions\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;


HeadingRowFormatter::default('none');
class BaseImport implements WithMultipleSheets
{
    use Importable;

    protected $schema = [];

    protected $type;

    public function __construct($type)
    {
        if($type == 'oems'){
            $this->schema[] = new OemImport();
        }
        elseif($type == 'pbinds'){
            $this->schema[] = new PbindImport();
        }
        elseif($type == 'sbinds'){
            $this->schema[] = new SbindImport();
        }
        elseif($type == 'p_requests'){
            $this->schema[] = new PRequestImport();
        }
        elseif($type == 's_requests'){
            $this->schema[] = new SRequestImport();
        }
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return $this->schema;
    }

}