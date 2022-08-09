<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\DataCount;
use App\Admin\Metrics\DataOverView;
use App\Admin\Metrics\NewBind;
use App\Admin\Metrics\ODataAdd;
use App\Admin\Metrics\PVShow;
use App\Admin\Metrics\UVShow;
use App\Admin\Metrics\PDataAdd;
use App\Admin\Metrics\SDataAdd;
use App\Admin\Metrics\NewData;
use App\Admin\Metrics\NewRequest;
use App\Admin\Metrics\NewUsers;
use App\Admin\Metrics\PBindTime;
use App\Admin\Metrics\PRequestTime;
use App\Admin\Metrics\ProjectTest;
use App\Admin\Metrics\Sessions;
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
                $row->column(3, new PDataAdd());
                $row->column(3, new SDataAdd());
                $row->column(6, new DataOverView());
                $row->column(4, new PBindTime());
                $row->column(4, new PRequestTime());
                // $row->column(4, new ODataAdd());
                // 退役了
                // $row->column(4, new DataCount());
            })
            ->body(function (Row $row) {
                $row->column(4, new UVShow());
                $row->column(4, new PVShow());
                // $row->column(4, new NewUsers());
                // $row->column(4, new ProjectTest());
            })
            ->body(function (Row $row) {
                $row->column(4, new NewData());
                $row->column(4, new NewBind());
                $row->column(4, new NewRequest());
            });
    }
}
