<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\DataAdd;
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
                $row->column(4, new DataAdd());
            });
    }
}
