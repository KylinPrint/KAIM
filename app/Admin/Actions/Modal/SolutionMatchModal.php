<?php

namespace App\Admin\Actions\Modal;

use App\Admin\Actions\Form\AgainSolutionMatchForm;
use App\Admin\Actions\Form\SolutionMatchForm;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;


class SolutionMatchModal extends AbstractTool
{
    /**
     * @return string
     */
    protected $title = 'Title';

    public function render()
    {
        $id1 = "reset-pw-{$this->getKey()}";
        $id2 = "reset-pwd-{$this->getKey()}";

        // 模态窗
        $this->modal1($id1);
        $this->modal2($id2);

        return <<<HTML
<span class="grid-expand1" data-toggle="modal" data-target="#{$id1}">
   <a href="javascript:void(0)"><button class="btn btn-outline-info ">导入适配需求</button></a>
</span>
<span class="grid-expand2" data-toggle="modal" data-target="#{$id2}">
   <a href="javascript:void(0)"><button class="btn btn-outline-info ">导入筛查后型号表</button></a>
</span>
HTML;
    }

    protected function modal1($id1)
    {
        $form = new SolutionMatchForm();

        Admin::script('Dcat.onPjaxComplete(function () {
            $(".modal-backdrop").remove();
            $("body").removeClass("modal-open");
        }, true)');

        // 通过 Admin::html 方法设置模态窗HTML
        Admin::html(
            <<<HTML
<div class="modal fade" id="{$id1}">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">型号数据</h4>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        {$form->render()}
      </div>
    </div>
  </div>
</div>
HTML
        );
    }

    protected function modal2($id2)
    {
        $form = new AgainSolutionMatchForm();

        Admin::script('Dcat.onPjaxComplete(function () {
            $(".modal-backdrop").remove();
            $("body").removeClass("modal-open");
        }, true)');

        // 通过 Admin::html 方法设置模态窗HTML
        Admin::html(
            <<<HTML
<div class="modal fade" id="{$id2}">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">处理数据</h4>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        {$form->render()}
      </div>
    </div>
  </div>
</div>
HTML
        );
    }

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}