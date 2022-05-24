<?php

namespace App\Admin\Actions\Form;

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
    public function handle(array $input){
         
      //接收弹窗提交过来的数据，进行处理
        
      $ids = explode(',', $input['id'] ?? null); //处理提交过来的批量选择的行的id
       
      if (!$ids) {
        return $this->response()->error('参数错误');
      }
  
      $user_name = $input['user_name'];
      $statuses_id = $input['statuses_id'];
      $comment = $input['statuses_comment'];
          
      //处理逻辑
      foreach ($ids as $id) 
      {
        $pbind = Sbind::find($id);

        if($pbind->statuses_id != $statuses_id || $comment)
        {
          SbindHistory::create([
            'sbind_id' => $id,
            'status_old' => $pbind->statuses_id,
            'status_new' => $statuses_id ?? $pbind->statuses_id,
            'user_name' => Admin::user()->name,
            'comment' => $comment,
          ]);

          $pbind->user_name = $user_name ?? $pbind->user_name;
          $pbind->statuses_id = $statuses_id ?? $pbind->statuses_id;
          $pbind->save();
        }

        
      }
        
      return $this->response()->success('提交成功')->refresh();         
    }
  
    /**
      * Build a form here.
      */
    public function form()     
    {
        //弹窗界面
        $this->select('user_name', admin_trans('pbind.fields.user_name'))->options(function (){
          $curaArr = AdminUser::all()->pluck('name')->toArray();
          foreach($curaArr as $cura){ $optionArr[$cura] = $cura; }
          return $optionArr;
        })->help('不更改无需填写');
        $this->select('statuses_id')->options(Status::where('parent','!=',null)->pluck('name','id'));
        $this->textarea('statuses_comment');
        //批量选择的行的值传递
        $this->hidden('id')->attribute('id', 'batchsp-id'); //批量选择的行的id通过隐藏元素 提交时一并传递过去
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