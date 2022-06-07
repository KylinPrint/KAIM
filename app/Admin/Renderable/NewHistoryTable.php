<?php

namespace App\Admin\Renderable;

use App\Models\AdminUser;
use App\Models\Chip;
use App\Models\Peripheral;
use App\Models\Release;
use App\Models\Status;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use OwenIt\Auditing\Models\Audit;

class NewHistoryTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new Audit(), function (Grid $grid) {
            $trans = $this->payload["_trans_"];

            $grid->withBorder();
            $grid->addTableClass(['table-text-center']);
            $grid->disableRowSelector();
            $grid->tableCollapse(false);

            $grid->model()->where([
                'auditable_id'      => $this->payload["auditable_id"],
                'auditable_type'    => $this->payload["auditable_type"],
                'event'             => 'updated',
            ]);

            $grid->column('admin_user_id', '处理人')->display(function ($admin_user_id) {
                return '<strong>' . AdminUser::find($admin_user_id)->name . '</strong>';
            });
            $grid->column('change', '修改内容')->display(function () use ($trans) {
                $tbody = '';
                foreach ($this->old_values as $key => $value) {
                    // Relations
                    if          ($key == 'peripherals_id') {
                        $old = Peripheral::find($value)->name;
                        $new = Peripheral::find($this->new_values[$key])->name;
                    } elseif    ($key == 'releases_id') {
                        $old = Release::find($value)->name;
                        $new = Release::find($this->new_values[$key])->name;
                    } elseif    ($key == 'chips_id') {
                        $old = Chip::find($value)->name;
                        $new = Chip::find($this->new_values[$key])->name;
                    } elseif    ($key == 'statuses_id') {
                        $old = Status::find($value)->name;
                        $new = Status::find($this->new_values[$key])->name;
                    } else {
                        $old = $value;
                        $new = $this->new_values[$key];
                    }
                    $tbody = $tbody . '<tr> <td>' . admin_trans($trans . '.fields.' . $key) . '</td> <td>' . $old . '</td> <td>' . $new . '</td> </tr>';
                }
                return '<table class="table table-bordered table-condensed"> <thead> <tr> <th width="30%">名称</th> <th width="35%">修改前</th> <th width="35%">修改后</th> </tr> </thead> <tbody>' . $tbody . '</tbody> </table>';
            });
            $grid->column('updated_at')->date()->sortable();
        });
    }
}