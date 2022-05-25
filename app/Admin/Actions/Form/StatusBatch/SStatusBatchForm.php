<?php

namespace App\Admin\Actions\Form\StatusBatch;

use App\Models\AdminUser;
use App\Models\Sbind;
use App\Models\SbindHistory;
use App\Models\Status;
use Dcat\Admin\Admin;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Contracts\LazyRenderable;

class SStatusBatchForm extends Form implements LazyRenderable
{
    /**
      * Handle the form request.
      *
      * @param array $input
      *
      * @return mixed
      */
    use LazyWidget;
    public function handle(array $input)
    {
        if (!$input['id']) {
            return $this->response()->error('参数错误');
        }

        // 接收弹窗提交过来的数据，进行处理
        $ids = explode(',', $input['id'] ?? null); //处理提交过来的批量选择的行的id
        
        // 啥也不干你点它干啥
        if (!($input["change_user"] || $input["change_status"] || $input["statuses_comment"])) {
            return $this->response()->info('未修改');
        }

        // 处理逻辑
        foreach ($ids as $id) 
        {
            $sbind = Sbind::find($id);
            $status_old = $sbind->statuses_id;

            // 改责任人
            if ($input["change_user"]) {
                $sbind->user_name = $input["user_name"];
            }

            // 改状态
            if ($input["change_status"]) {
                $sbind->statuses_id = $input["statuses_id"];
            }

            // 新增SbindHistory
            if($input["change_status"] || $input["statuses_comment"]) {
                SbindHistory::create([
                    'sbind_id' => $id,
                    'status_old' => $status_old,
                    'status_new' => $input["change_status"] ? $input["statuses_id"] : $status_old,
                    'user_name' => Admin::user()->name,
                    'comment' => $input["statuses_comment"],
                ]);
            }
            
            $sbind->save();
        }
        
        return $this->response()->success('提交成功')->refresh();
    }
  
    /**
      * Build a form here.
      */
    public function form()     
    {
        $this->radio('change_user', '是否修改当前责任人')
            ->options([0 => '否', 1 => '是'])->default(0)
            ->when(1, function (Form $form) {
                $form->select('user_name')->options(function () {
                        foreach(AdminUser::all()->pluck('name')->toArray() as $name) { $user_names[$name] = $name; }
                        return $user_names;
                    })
                    ->rules('required_if:change_user,1', ['required_if' => '请填写此字段'])
                    ->setLabelClass('asterisk');
            });
        $this->radio('change_status', '是否修改需求状态')
            ->options([0 => '否', 1 => '是'])->default(0)
            ->when(1, function (Form $form) {
                $form->select('statuses_id')->options(Status::where('parent', '!=', null)->pluck('name','id'));
            });
        $this->textarea('statuses_comment');
        //批量选择的行的值传递
        $this->hidden('id')->attribute('id', 'batch-status-id'); //批量选择的行的id通过隐藏元素 提交时一并传递过去
    }
  
    /**
      * The data of the form.
      *
      * @return array
      */
    public function default()
    {
        //设置默认值
        return [];
    }
}