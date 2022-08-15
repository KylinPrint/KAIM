<?php

namespace App\Admin\Renderable;

use App\Models\Pbind;
use App\Models\Type;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;


class PbindTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(Pbind::with(['peripherals','releases','chips']), function (Grid $grid) {
            $grid->quickSearch('peripherals.name');

            if ($this->type_id) {
                $grid->model()->whereHas('peripherals', function ($query){
                    $query->whereHas('types', function ($query){
                        $query->where('id', $this->type_id);
                    });
                });
            }

            $grid->column('peripherals.name');
            $grid->column('releases.name');
            $grid->column('chips.name');

            $grid->paginate(10);
            $grid->disableActions();
        });
    }
}