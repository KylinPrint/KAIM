<?php

namespace App\Admin\Actions\Form;

use App\Models\Pbind;
use App\Models\PbindHistory;
use App\Models\Status;
use Dcat\Admin\Admin;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Contracts\LazyRenderable;

class PStatusBatchForm extends Form implements LazyRenderable
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
       
      if (!$ids) 
      {
        return $this->response()->error('参数错误');
      }
  
      $statuses_id = $input['statuses_id'];
      $comment = $input['comment'];
          
      //处理逻辑
      foreach ($ids as $id) 
      {
        $pbind = Pbind::find($id);

        if($pbind->statuses_id != $statuses_id || $comment)
        {
          PbindHistory::create([
            'pbind_id' => $id,
            'status_old' => $pbind->statuses_id,
            'status_new' => $statuses_id,
            'user_name' => Admin::user()->name,
            'comment' => $comment,
          ]);

          $pbind->statuses_id = $statuses_id;
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
        $this->select('statuses_id', admin_trans('pbind.fields.statuses_id'))->options(Status::where('parent','!=',null)->pluck('name','id'))->required();
        $this->textarea('comment', admin_trans('pbind.fields.statuses_comment'))->required();
        //批量选择的行的值传递
        $this->hidden('id')->attribute('id', 'batchsp-id'); //批量选择的行的id通过隐藏元素 提交时一并传递过去
        // $a = $this->parent->attributes;
        $b = 0;
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