<?php

namespace App\Admin\Actions\Imports;


use App\Models\Pbind;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

use function PHPUnit\Framework\isEmpty;

HeadingRowFormatter::default('none');
class PbindImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        

        return new Pbind([
                
        ]);

    }


    //批量导入1000条
    public function batchSize(): int
    {
        return 10;
    }

    //以1000条数据基准切割数据
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * 从第几行开始处理数据 就是不处理标题
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'pbindid' => Rule::unique('pbinds', 'pbindid'), // Table name, field in your db
        ];
    }

    public function customValidationMessages()
    {
        return [
            'pbindid.unique' => '导入重复数据',
        ];
    }
}