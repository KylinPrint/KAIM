<?php

namespace App\Admin\Actions\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;


HeadingRowFormatter::default('none');
class BaseUpdate implements WithMultipleSheets
{
    use Importable;

    protected $schema = [];

    protected $type;

    public function __construct($type)
    {
        if($type == 'oems'){
            $this->schema[] = new OemUpdate();
        }
        elseif($type == 'pbinds'){
            $this->schema[] = new PbindUpdate();
        }
        elseif($type == 'sbinds'){
            $this->schema[] = new SbindUpdate();
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