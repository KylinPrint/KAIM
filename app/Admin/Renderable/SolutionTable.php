<?php

namespace App\Admin\Renderable;


use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use App\Models\Solution;
use Dcat\Admin\Widgets\Card;

class SolutionTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new Solution(), function (Grid $grid) {
            $grid->column('name');
            $grid->column('details')->display('详情')->expand(function (){
                $card = new Card(null, $this->details);
                return "<div style='padding:10px 10px 0'>$card</div>";
            });
            $grid->column('source');
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disablePagination();
        });
    }
}