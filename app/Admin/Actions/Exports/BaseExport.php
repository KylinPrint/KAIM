<?php

namespace App\Admin\Actions\Exports;

use Dcat\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

abstract class BaseExport extends AbstractExporter implements FromCollection, WithHeadings
{
    use Exportable;

    public function collection()
    {
        return collect($this->buildData());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $this->download($this->exportFilename())->prepare(request())->send();
        exit;
    }

    public function headings(): array
    {
        return $this->columns;
    }

    /**
     * 导出文件的文件名
     *
     * @return string
     */
    public function exportFilename()
    {
        return $this->fileName ?? "export-data";
    }
}