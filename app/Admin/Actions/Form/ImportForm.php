<?php

namespace App\Admin\Actions\Form;

use App\Admin\Actions\Imports\BaseImport;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '10M');

class ImportForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function handle(array $input)
    {
        try {
            $file = storage_path('app/public/' . $input['file']);

            // Excel::import(new BaseImport($this->payload['type']),$file);

            $import = new BaseImport($this->payload['type']);
            $import->import($file);

            $disk = Storage::disk('public');
            $disk -> delete($input['file']);
            
            return $this->response()->success('数据导入成功')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function form()
    {
        if(isset($this->payload['filename'])){$filename = $this->payload['filename'];}
        else{$filename = null;}
        $this->file('file', '上传数据(Excel)')
            ->autoUpload()
            ->rules('required', ['required' => '文件不能为空'])
            ->move('admin/upload')
            ->help('<a href="/template/'.$filename.'.xlsx" target="_blank">点击此处</a>下载导入模板');
        $this->disableResetButton();
    }

}