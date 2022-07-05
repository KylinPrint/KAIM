<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    // API
    $router->get('/api/type',[App\Admin\Controllers\TypeController::class,'getName']);
    $router->get('/api/stype',[App\Admin\Controllers\StypeController::class,'getName']);
    $router->get('/api/status',[App\Admin\Controllers\StatusController::class,'getName']);
    $router->get('/api/peripherals',[App\Admin\Controllers\PbindController::class,'pPaginate']);
    $router->get('/api/softwares',[App\Admin\Controllers\SbindController::class,'sPaginate']);
    $router->get('pbinds/template-export', 'PbindController@export')->name('pbind-template.export');


    // 软件
    $router->resource('softwares', SoftwareController::class);
    $router->resource('sbinds', SbindController::class);

    // 外设
    $router->resource('peripherals', PeripheralController::class);
    $router->resource('pbinds', PbindController::class);

    // 整机
    $router->resource('oems',OemController::class);

    // 关联
    $router->resource('manufactors',ManufactorController::class);
    $router->resource('brands', BrandController::class);
    $router->resource('chips',ChipController::class);
    $router->resource('releases',ReleaseController::class);
    $router->resource('otypes',OtypeController::class);
    $router->resource('stypes',StypeController::class);
    $router->resource('types',TypeController::class);
    $router->resource('specifications',SpecificationController::class);
    $router->resource('statuses',StatusController::class);

    // 需求
    $router->resource('srequests',SRequestController::class);
    $router->resource('prequests',PRequestController::class);

    // 小工具
    $router->resource('solution-match',SolutionMatchController::class);

    // 数据统计
    $router->resource('statistics', StatisticsController::class);
});
