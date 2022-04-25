<?php

namespace App\Admin\Actions\Form;

use App\Admin\Actions\Exports\SolutionMatchExport;
use App\Admin\Actions\Imports\SolutionMatchImport;
use App\Models\SolutionMatch;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '8M');
date_default_timezone_set('PRC');

class SolutionMatchForm extends Form
{
    public function handle(array $input)
    {
        try {
            // 提取上传文件内容
            // 写的导入处理类SolutionMatchImport没生效，原因未明
            $array = (Excel::toArray(new SolutionMatchImport, storage_path('app/public/'.$input['file'])))[0];

            // 删除上传的临时文件
            Storage::disk('public')->delete($input['file']);

            // 产品名称标准化后的文件名
            $storeFileName = '产品名称标准化_' . $input['admin_user_name'] . '_' .date('ymd-His') . '.xlsx';
            
            // 产品名称标准化
            (new SolutionMatchExport($array,null))->store('solution-match/' . $storeFileName, 'public');
           
            // 标准化结果文件名存入数据库
            SolutionMatch::create([
                'title' => $storeFileName,
            ]);

            return $this->response()->success('产品名称标准化完成')->refresh();

        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage()); 
        }
    }

    public function form()
    {
        $this->file('file', __('上传数据(Excel)'))
            ->autoUpload()
            ->rules('required', ['required' => '文件不能为空'])
            ->help('<a href="/template/solution_match.xlsx" target="_blank">点击此处</a>下载导入模板,对导入数据进行型号标准化，可能输出多个可能型号，请自行筛选后填入‘产品名称’列。');
        $this->hidden('admin_user_name')->value(Admin::user()->name);
        $this->disableResetButton();
    }
}