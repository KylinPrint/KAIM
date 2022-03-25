<?php

namespace App\Admin\Controllers;

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
            });
    }
}
