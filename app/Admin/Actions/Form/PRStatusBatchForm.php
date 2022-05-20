<?php

namespace App\Admin\Actions\Form;

use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Manufactor;
use App\Models\Pbind;
use App\Models\PbindHistory;
use App\Models\Peripheral;
use App\Models\PRequest;
use App\Models\PRequestHistory;
use App\Models\Status;
use Dcat\Admin\Admin;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Contracts\LazyRenderable;

class PRStatusBatchForm extends Form implements LazyRenderable
{
    use LazyWidget;
    
    /**
      * Handle the form request.
      *
      * @param array $input
      *
      * @return mixed
      */
    public function handle(array $input)
    {
        //接收弹窗提交过来的数据，进行处理
        $ids = explode(',', $input['id'] ?? null); //处理提交过来的批量选择的行的id
        
        if (!$ids) {
            return $this->response()->error('参数错误');
        }
        
        if ($input["change_status"]) {
            // 不同状态的需求不能批量编辑状态
            $status_current = array_unique(PRequest::whereIn('id', $ids)->pluck('status')->toarray());
            if (count($status_current) - 1) {
                return $this->response()->error('不同状态的需求不能批量编辑状态')->refresh();
            }
            // 已关闭的需求不允许编辑
            if ($status_current[0] == '已关闭') {
                return $this->response()->warning('已关闭的需求不允许编辑')->refresh();
            }
            // 直接进行一个状态的判
            $options = config('kaim.request_status');
            if     (in_array($status_current[0], ['处理中', '已处理', '暂停处理', '已拒绝'])) { unset($options['已提交']); }
            elseif (in_array($status_current[0], ['已处理', '已拒绝'])) { unset($options['处理中']); }
            elseif (in_array($status_current[0], ['已提交', '已拒绝'])) { unset($options['已处理']); }
            elseif (in_array($status_current[0], ['已提交', '已处理', '已拒绝'])) { unset($options['暂停处理']); }
            elseif (in_array($status_current[0], ['处理中', '已处理', '暂停处理'])) { unset($options['已拒绝']); }
            if (!in_array($input['status'], $options)) {
                // 状态不能反着改
                return $this->response()->error('"' . $status_current[0] . '"' . '的需求不能修改为' . '"' . $input['status'] . '"');
            }
        }
        
        // 啥也不干你点它干啥
        if (!($input["change_bd"] || $input["change_status"] || $input["status_comment"])) {
            return $this->response()->info('未修改');
        }
        
        //处理逻辑
        foreach ($ids as $id) {
            $prequest = PRequest::find($id);
            // 改BD
            if ($input["change_bd"]) {
                $prequest->bd_id = $input["bd_id"];
            }
            // 改状态
            if ($input["change_status"]) {
                // 已提交改处理中
                if ($status_current[0] == '已提交' && $input['status'] == '处理中') {
                    // Manufactor
                    $manufactor = Manufactor::firstOrCreate([
                        'name' => $prequest->manufactor,
                    ]);

                    // Brand
                    if (preg_match('/\(|\（/', $prequest->brand)) {
                        // 有括号的抓括号拆分中英文
                        preg_match('/(.+(?=\(|\（))/', trim($prequest->brand), $brand_name);
                        preg_match('/(?<=\(|\（).+?(?=\)|\）)/', trim($prequest->brand), $brand_name_en);
                        $brand = Brand::firstOrCreate([
                            'name'      => $brand_name[0] ?? null,
                            'name_en'   => $brand_name_en[0] ?? null,
                        ]);
                    } else {
                        // 没括号的抓中文拆分中英文
                        if (preg_match('/[\x7f-\xff]/', $prequest->brand)) {
                            $brand_name = trim($prequest->brand);
                        } else {
                            $brand_name_en = trim($prequest->brand);
                        }
                        $brand = Brand::firstOrCreate([
                            'name'      => $brand_name ?? null,
                            'name_en'   => $brand_name_en ?? null,
                        ]);
                    }

                    // Peripheral
                    $peripheral = Peripheral::firstOrCreate(
                        [
                            'manufactors_id'    => $manufactor->id,
                            'brands_id'         => $brand->id,
                            'name'              => $prequest->name,
                        ],
                        [
                            'types_id'          => $prequest->type_id,
                            'industries'        => $prequest->industry,
                        ],
                    );

                    // PBind
                    $pbind = Pbind::firstOrCreate(
                        [
                            'peripherals_id'    => $peripheral->id,
                            'releases_id'       => $prequest->release_id,
                            'chips_id'          => $prequest->chip_id,
                        ],
                        [
                            'os_subversion' => $prequest->os_subversion,
                            'adapt_source'  => $prequest->source,
                            'statuses_id'   => $input['statuses_id'],
                            'user_name'     => $input["user_name"],
                            'kylineco'      => $input["kylineco"],
                            'appstore'      => $input["appstore"],
                            'iscert'        => $input["iscert"],
                        ],
                    );

                    // PBindHistory
                    if ($pbind->wasRecentlyCreated) {
                        PbindHistory::create([
                            'pbind_id'      => $pbind->id,
                            'status_old'    => NULL,
                            'status_new'    => $input["statuses_id"],
                            'user_name'     => Admin::user()->name,
                            'comment'       => $input['statuses_comment'],
                        ]);
                    }
                        
                    // 填充关联数据
                    $prequest->pbind_id = $pbind->id;

                } 

                // 状态的改
                $prequest->status = $input["status"];
            }

            if ($input["change_status"] || $input['status_comment']) {
                // 新增PRequestHistory
                PRequestHistory::create([
                    'p_request_id' => $id,
                    'status_old' => $prequest->status,
                    'status_new' => $input["change_status"] ? $input['status'] : $prequest->status,
                    'user_name' => Admin::user()->name,
                    'comment' => $input['status_comment'],
                ]);
            }

            $prequest->save();
        }

        return $this->response()->success('提交成功')->refresh();
    }
  
    /**
      * Build a form here.
      */
    public function form()     
    {
        //弹窗界面
        $this->radio('change_bd', '是否修改需求接收人')
            ->options([0 => '否', 1 => '是'])->default(0)
            ->when(1, function (Form $form) {
                $form->select('bd_id')->options(AdminUser::all()->pluck('name', 'id'));
            });
        $this->radio('change_status', '是否修改需求状态')
            ->options([0 => '否', 1 => '是'])->default(0)
            ->when(1, function (Form $form) {
                $form->select('status', admin_trans('p-request.fields.status'))
                    ->when('处理中', function (Form $form) {
                        $form->radio('comment_only', '仅添加需求状态变更说明')
                            ->options([0 => '否', 1 => '是'])->default(0)
                            ->when(0, function (Form $form) {
                                $form->select('statuses_id')->options(Status::where('parent', '!=', null)->pluck('name', 'id'))
                                    ->rules('required_if:comment_only,0',['required_if' => '请填写此字段'])
                                    ->setLabelClass(['asterisk']);
                                $form->text('statuses_comment')
                                    ->rules('required_if:comment_only,0',['required_if' => '请填写此字段'])
                                    ->setLabelClass(['asterisk']);
                                $form->select('user_name')->options(function () {
                                    foreach(AdminUser::all()->pluck('name')->toArray() as $name) { $user_names[$name] = $name; }
                                    return $user_names;
                                    })
                                    ->rules('required_if:comment_only,0',['required_if' => '请填写此字段'])
                                    ->setLabelClass(['asterisk']);
                                $form->select('kylineco')->options([0 => '否', 1 => '是'])
                                    ->rules('required_if:comment_only,0',['required_if' => '请填写此字段'])
                                    ->setLabelClass(['asterisk']);
                                $form->select('appstore')->options([0 => '否', 1 => '是'])
                                    ->rules('required_if:comment_only,0',['required_if' => '请填写此字段'])
                                    ->setLabelClass(['asterisk']);
                                $form->select('iscert')->options([0 => '否', 1 => '是'])
                                    ->rules('required_if:comment_only,0',['required_if' => '请填写此字段'])
                                    ->setLabelClass(['asterisk']);
                            });
                    })
                    ->options(config('kaim.request_status'));
            });
        $this->textarea('status_comment', admin_trans('p-request.fields.status_comment'));
        //批量选择的行的值传递
        $this->hidden('id')->attribute('id', 'batch-prs-id'); //批量选择的行的id通过隐藏元素 提交时一并传递过去
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