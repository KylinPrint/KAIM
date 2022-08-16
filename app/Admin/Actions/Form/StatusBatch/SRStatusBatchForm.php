<?php

namespace App\Admin\Actions\Form\StatusBatch;

use App\Admin\Renderable\SbindTable;
use App\Admin\Utils\RequestStatusGraph;
use App\Models\AdminUser;
use App\Models\Manufactor;
use App\Models\Sbind;
use App\Models\Software;
use App\Models\SRequest;
use App\Models\Status;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Contracts\LazyRenderable;

class SRStatusBatchForm extends Form implements LazyRenderable
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
        if (!$input['id']) {
            return $this->response()->error('参数错误');
        }

        //接收弹窗提交过来的数据，进行处理
        $ids = explode(',', $input['id'] ?? null); //处理提交过来的批量选择的行的id
        
        // 获取当前状态
        $status_current = array_unique(SRequest::whereIn('id', $ids)->pluck('status')->toarray());

        // 不同状态的需求不能批量编辑状态
        if ($input['change_status'] && (count($status_current) != 1)) {
            return $this->response()->error('不同状态的需求不能批量编辑状态')->refresh();
        }
        $status_current = $status_current[0];

        // 获取状态的图
        $graph = RequestStatusGraph::make();

        // 终态需求不允许编辑
        if (! $graph->getVertex($status_current)->getEdgesOut()) {
            return $this->response()->warning('已关闭的需求不允许编辑')->refresh();
        }

        if ($input['change_status']) {
            // 二选一必填
            if (!($input['status'] || $input['status_comment'])) {
                return $this->response()->info('请在"需求处理状态"和"需求状态变更说明"中至少选择一项填写');
            }

            // 已提交改处理中不能只填备注
            if ($status_current == '已提交' && $input['comment_only']) {
                return $this->response()->warning('已提交状态的需求变更为处理中时,不允许仅添加需求状态变更说明');
            }

            // 直接进行一个状态的判
            if ($input['status']) {
                if (! $graph->getVertex($status_current)->hasEdgeTo($graph->getVertex($input['status']))) {
                    return $this->response()->error('"' . $status_current . '"' . '的需求不能修改为' . '"' . $input['status'] . '"');
                }
            }
        } elseif (! $input['change_bd']) {
            // 啥也不干你点它干啥
            return $this->response()->info('未修改');
        }

        //处理逻辑
        foreach ($ids as $id) {
            $srequest = SRequest::find($id);

            // 改BD
            if ($input['change_bd']) {
                $srequest->bd_id = $input['bd_id'];
            }

            // 改状态
            if ($input['change_status']) {
                // 已提交改处理中
                if ($status_current == '已提交' && $input['status'] == '处理中') {
                    // Manufactor
                    $manufactor = Manufactor::firstOrCreate([
                        'name' => $srequest->manufactor,
                    ]);

                    // Software
                    $software = Software::firstOrCreate(
                        [
                            'name'  => $srequest->name,
                        ],
                        [
                            'manufactors_id'    => $manufactor->id,
                            'stypes_id'         => $srequest->stype_id,
                            'industries'        => $srequest->industry,
                        ],
                    );

                    // SBind
                    $sbind = Sbind::firstOrCreate(
                        [
                            'softwares_id'  => $software->id,
                            'releases_id'   => $srequest->release_id,
                            'chips_id'      => $srequest->chip_id,
                        ],
                        [
                            'os_subversion' => $srequest->os_subversion,
                            'adapt_source'  => $srequest->source,
                            'statuses_id'   => $input['statuses_id'],
                            'statuses_comment'  => $input['statuses_comment'],
                            'admin_user_id'     => $input['admin_user_id'],
                            'class'         => $input['class'],
                            'adaption_type' => $input['adaption_type'],
                            'test_type'     => $input['test_type'],
                            'kylineco'      => $input['kylineco'],
                            'appstore'      => $input['appstore'],
                            'iscert'        => $input['iscert'],
                        ],
                    );
                    
                    // 填充关联数据
                    $srequest->sbind_id = $sbind->id;
                }

                if ($input['status'] == '已拒绝' && $input['is_exist'] == 1) {
                    $srequest->sbind_id = $input['sbind_id'];
                }

                // 状态的改
                if ($input['status']) { $srequest->status = $input['status']; }
                if ($input['status_comment']) { $srequest->status_comment = $input['status_comment']; }   
            }

            $srequest->save();
        }

        return $this->response()->success('提交成功')->refresh();
    }
  
    /**
      * Build a form here.
      */
    public function form()
    {
        $this->radio('change_bd', '是否修改需求接收人')
            ->options([0 => '否', 1 => '是'])->default(0)
            ->when(1, function (Form $form) {
                $form->select('bd_id')->options(AdminUser::all()->pluck('name', 'id'))
                    ->rules('required_if:change_bd,1', ['required_if' => '请填写需求接收人'])
                    ->setLabelClass('asterisk');
            });
        $this->radio('change_status', '是否修改需求状态')
            ->options([0 => '否', 1 => '是'])->default(0)
            ->when(1, function (Form $form) {
                $form->select('status', admin_trans('s-request.fields.status'))
                    ->when('处理中', function (Form $form) {
                        $form->radio('comment_only', '仅添加需求状态变更说明')
                            ->setLabelClass(['asterisk'])
                            ->options([0 => '否', 1 => '是'])->default(0)
                            ->when(0, function (Form $form) {
                                $form->select('statuses_id')->options(config('admin.database.statuses_model')::selectOptions())
                                    ->rules(function () {
                                        if (request()->status == '处理中') {
                                            if (! Status::where('id', request()->statuses_id)->pluck('parent')->first()) {
                                                return 'max:0|required_if:comment_only,0';
                                            } else {
                                                return 'required_if:comment_only,0';
                                            }
                                        }
                                    }, ['required_if' => '请填写' . admin_trans('sbind.fields.statuses_id'), 'max' => '请选择子分类'])
                                    ->setLabelClass(['asterisk']);
                                $form->text('statuses_comment');
                                $form->select('admin_user_id')->options(AdminUser::all()->pluck('name', 'id'))
                                    ->rules(function (){ if(request()->status == '处理中') { return 'required_if:comment_only,0'; } },
                                        ['required_if' => '请填写' . admin_trans('sbind.fields.statuses_id')]
                                    )
                                    ->setLabelClass(['asterisk']);
                                $form->select('class', admin_trans('sbind.fields.class'))
                                    ->options(config('kaim.class'));
                                $form->select('adaption_type', admin_trans('sbind.fields.adaption_type'))
                                    ->options(config('kaim.adaption_type'));
                                $form->select('test_type' ,admin_trans('sbind.fields.test_type'))
                                    ->options(config('kaim.test_type'));
                                $form->select('kylineco')->options([0 => '否', 1 => '是'])
                                    ->rules(function (){ if(request()->status == '处理中') { return 'required_if:comment_only,0'; } },
                                        ['required_if' => '请填写' . admin_trans('sbind.fields.kylineco')]
                                    )
                                    ->setLabelClass(['asterisk']);
                                $form->select('appstore')->options([0 => '否', 1 => '是'])
                                    ->rules(function (){ if(request()->status == '处理中') { return 'required_if:comment_only,0'; } },
                                        ['required_if' => '请填写' . admin_trans('sbind.fields.appstore')]
                                    )
                                    ->setLabelClass(['asterisk']);
                                $form->select('iscert')->options([0 => '否', 1 => '是'])
                                    ->rules(function (){ if(request()->status == '处理中') { return 'required_if:comment_only,0'; } },
                                        ['required_if' => '请填写' . admin_trans('sbind.fields.iscert')]
                                    )
                                    ->setLabelClass(['asterisk']);
                            });
                    })
                    ->when('已拒绝', function (Form $form) {
                        $form->radio('is_exist', '是否关联适配数据')
                            ->setLabelClass(['asterisk'])
                            ->options([0 => '否', 1 => '是'])->default(0)
                            ->when(1, function (Form $form) {
                                $form->selectTable('sbind_id', '关联的适配数据')
                                    ->from(SbindTable::make())
                                    // 这里Dcat不支持关系,只能填俩ID
                                    ->model(Sbind::class, 'id', 'id');
                            });
                    })
                    ->options(function () {
                        foreach (RequestStatusGraph::make()->getVertices() as $vertex) {
                            $options[$vertex->getId()] = $vertex->getId();
                        }
                        return $options;
                    });
                $form->textarea('status_comment', admin_trans('s-request.fields.status_comment'));

            });
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