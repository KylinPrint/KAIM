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
            // 报错不刷新
            return $this->response()->error('"' . $status_current[0] . '"' . '状态的需求不能修改为' . '"' . $input['status'] . '"');
        }
        
            
        //处理逻辑
        // 已提交改处理中
        if ($status_current[0] == '已提交' && $input['status'] == '处理中') {
            foreach ($ids as $id) {
                $prequest = PRequest::find($id);
                // Manufactor
                $manufactor = Manufactor::firstOrCreate([
                    'name' => $prequest->manufactor,
                ]);

                // Brand
                // 抓括号
                if (preg_match('/\(|\（/', $prequest->brand)) {
                    // 拆分中英文
                    preg_match('/(.+(?=\(|\（))/', trim($prequest->brand), $brand_name);
                    preg_match('/(?<=\(|\（).+?(?=\)|\）)/', trim($prequest->brand), $brand_name_en);
                    $brand = Brand::firstOrCreate([
                        'name'      => $brand_name[0] ?? null,
                        'name_en'   => $brand_name_en[0] ?? null,
                    ]);
                } else {
                    // 抓中文
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
                $prequest->save();
            }

        } elseif ($input['status'] && $status_current[0] != $input['status']) {
            // 不是已提交改处理中的直接批量修改需求状态
            PRequest::whereIn('id', $ids)->update([ 'status' => $input['status'] ]);

        } elseif ($input['status_comment']) {
            // 只修改了备注

        } else {
            // 没有任何修改
            return $this->response()->success('未修改')->refresh();
        }

        // PRequestHistory
        foreach ($ids as $id) {
            PRequestHistory::create([
                'p_request_id' => $id,
                'status_old' => $status_current[0],
                'status_new' => $input['status'] ?? $status_current[0],
                'user_name' => Admin::user()->name,
                'comment' => $input['status_comment'],
            ]);
        }

        return $this->response()->success('提交成功')->refresh();
    }
  
    /**
      * Build a form here.
      */
    public function form()     
    {
        //弹窗界面
        $this->select('status', admin_trans('p-request.fields.status'))
            ->when('处理中', function (Form $form) {
                $form->select('statuses_id')->options(Status::where('parent', '!=', null)->pluck('name', 'id'));
                $form->text('statuses_comment');
                $form->select('user_name')->options(function () {
                    foreach(AdminUser::all()->pluck('name')->toArray() as $name) { $user_names[$name] = $name; }
                    return $user_names;
                });
                $form->select('kylineco')->options([0 => '否', 1 => '是']);
                $form->select('appstore')->options([0 => '否', 1 => '是']);
                $form->select('iscert')->options([0 => '否', 1 => '是']);
            })
            ->options(config('kaim.request_status'))
            ->required();
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