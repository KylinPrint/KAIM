<?php

namespace App\Admin\Actions\Form;

use App\Admin\Actions\Imports\BaseImport;
use App\Admin\Actions\Imports\BaseUpdate;
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
            if($input['mode'] == 1){
                $import = new BaseImport($this->payload['type']);
                $import->import($file);
            }elseif($input['mode'] == 2){
                $import = new BaseUpdate($this->payload['type']);
                $import->import($file);
            }
            
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
        
        if($filename == 's_import' || $filename == 'p_import' || $filename == 'o_import'){
            $this->radio('mode', '上传模式')->options([1 => '新增', 2=> '更新'])->default(1);
        }else{
            $this->hidden('mode', '上传模式')->default(1);
        }
        
        $this->file('file', '上传数据(Excel)')
            ->autoUpload()
            ->rules('required', ['required' => '文件不能为空'])
            ->move('admin/upload')
            ->help('<a href="/template/'.$filename.'.xlsx" target="_blank">点击此处</a>下载导入模板');
        $this->disableResetButton();
    }

}