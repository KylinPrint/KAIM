<?php

namespace App\Admin\Actions\Form;

use App\Admin\Actions\Exports\AgainSolutionMatchExport;
use App\Admin\Actions\Imports\AgainSolutionMatchImport;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use function PHPUnit\Framework\isEmpty;

ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '8M');
date_default_timezone_set('PRC');

class AgainSolutionMatchForm extends Form
{
    public function handle(array $input)
    {
        try {
            //上传文件位置，这里默认是在storage中，如有修改请对应替换
            $file = storage_path('app/public/'.$input['file']);

            //写的导入处理类SolutionMatchImport没tm生效，原因未明
            $array = (Excel::toArray(new AgainSolutionMatchImport,$file))[0];


            $disk = Storage::disk('local');
            $disk -> delete('public'.$input['file']);

            $storeFile = substr(strrchr($input['file'],'/'),1);
            $storeFileName = 'match/'.substr($storeFile,0,strrpos($storeFile,'.')).'_匹配方案_'.date('Y-m-d_H:i:s').'.xlsx';
            
            
            $time_start = $this->getmicrotime();
            (new AgainSolutionMatchExport($array,$file))->store($storeFileName,'admin');
            $time_end = $this->getmicrotime();
            $time = $time_end - $time_start;
           

            DB::insert('INSERT INTO solution_matches(title,path) VALUES ("'.$storeFile.'","'.$storeFileName.'")');

            return $this->response()->success('数据处理成功')->refresh();

        } catch (\Exception $e) {
    
            return $this->response()->error($e->getMessage()); 
        }
    }

    public function form()
    {
        $this->file('file', '上传数据(Excel)')->rules('required', ['required' => '文件不能为空'])->move('/')
             ->help('请上传已经过型号标准化的文件，用以输出对应适配方案。');
    }

    public function getmicrotime()
    {
        list($usec,$sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

}