<?php

namespace App\Admin\Renderable;

use App\Models\Status;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;


class StatusTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new Status(), function (Grid $grid) {
            $grid->column('name');

            $grid->paginate(10);
            $grid->disableActions();
        });
    }
}