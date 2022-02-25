<?php

namespace App\Admin\Renderable;

use App\Models\Chip;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;


class ChipTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new Chip(), function (Grid $grid) {
            $grid->column('name');

            $grid->paginate(10);
            $grid->disableActions();
        });
    }
}