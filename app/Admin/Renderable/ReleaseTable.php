<?php

namespace App\Admin\Renderable;

use App\Models\Release;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;


class ReleaseTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new Release(), function (Grid $grid) {
            $grid->column('name');

            $grid->paginate(10);
            $grid->disableActions();
        });
    }
}