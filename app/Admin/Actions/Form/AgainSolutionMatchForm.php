<?php

namespace App\Admin\Actions\Form;

use App\Admin\Actions\Exports\AgainSolutionMatchExport;
use App\Admin\Actions\Imports\AgainSolutionMatchImport;
use App\Models\SolutionMatch;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '8M');
date_default_timezone_set('PRC');

class AgainSolutionMatchForm extends Form
{
    public function handle(array $input)
    {
        try {
            // 提取上传文件内容
            // 写的导入处理类SolutionMatchImport没生效，原因未明
            $array = (Excel::toArray(new AgainSolutionMatchImport, storage_path('app/public/'.$input['file'])))[0];

            // 删除上传的临时文件
            Storage::disk('public')->delete($input['file']);
            
            // 产品名称标准化后的文件名
            $storeFileName = '适配查询结果_' . $input['admin_user_name'] . '_' .date('ymd-His') . '.xlsx';

            // 产品名称标准化
            (new AgainSolutionMatchExport($array,null))->store('solution-match/' . $storeFileName, 'public');
           
            // 标准化结果文件名存入数据库
            SolutionMatch::create([
                'title' => $storeFileName,
            ]);

            return $this->response()->success('适配查询结果已生成')->refresh();

        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage()); 
        }
    }

    public function form()
    {
        $this->file('file', '上传数据(Excel)')
            ->autoUpload()
            ->rules('required', ['required' => '文件不能为空'])
            ->help('请上传已经过型号标准化的文件，用以输出对应适配方案。');
        $this->hidden('admin_user_name')->value(Admin::user()->name);
        $this->disableResetButton();
    }
}