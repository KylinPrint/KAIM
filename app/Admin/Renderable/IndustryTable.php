<?php

namespace App\Admin\Renderable;

use App\Models\Industry;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;

class IndustryTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new Industry, function (Grid $grid) {

            $grid->column('name');

            $grid->paginate(10);
            // 选了会报错，原因未知
            // $grid->perPages([10, 20, 50, 100, 200]);
            $grid->disableActions();
        });
    }
}