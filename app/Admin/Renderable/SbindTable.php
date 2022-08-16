<?php

namespace App\Admin\Renderable;

use App\Models\Sbind;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;


class SbindTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(Sbind::with(['softwares','releases','chips']), function (Grid $grid) {
            $grid->quickSearch('softwares.name');

            if ($this->stype_id) {
                $grid->model()->whereHas('softwares', function ($query){
                    $query->whereHas('stypes', function ($query){
                        $query->where('id', $this->stype_id);
                    });
                });
            }

            $grid->column('softwares.name');
            $grid->column('releases.name');
            $grid->column('chips.name');

            $grid->paginate(10);
            $grid->disableActions();
        });
    }
}