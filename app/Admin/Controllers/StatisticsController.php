<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\DataCount;
use App\Admin\Metrics\DataOverView;
use App\Admin\Metrics\PVShow;
use App\Admin\Metrics\UVShow;
use App\Admin\Metrics\PDataAdd;
use App\Admin\Metrics\SDataAdd;
use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

class StatisticsController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('数据统计')
            ->body(function (Row $row) {
                $row->column(4, new PDataAdd());
                $row->column(4, new SDataAdd());
                // 退役了
                // $row->column(4, new DataCount());
                $row->column(4, new DataOverView());
            })
            ->body(function (Row $row) {
                $row->column(4, new UVShow());
                $row->column(4, new PVShow());
            });
    }
}
