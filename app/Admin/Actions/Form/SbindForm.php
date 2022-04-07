<?php

namespace App\Admin\Actions\Form;

use App\Admin\Actions\Imports\SbindImport;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '10M');

class SbindForm extends Form
{
    public function handle(array $input)
    {
        try {
            $file = storage_path('app/public/' . $input['file']);

            Excel::import(new SbindImport(),$file);

            $disk = Storage::disk('public');
            $disk -> delete($input['file']);
            
            return $this->response()->success('数据导入成功')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function form()
    {
        $this->file('file', '上传数据(Excel)')->rules('required', ['required' => '文件不能为空'])->move('admin/upload')
            ->help('<a href="/template/s_import.xlsx" target="_blank">点击此处</a>下载导入模板');

    }

}