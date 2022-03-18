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

    $router->get('/api/type',[App\Admin\Controllers\TypeController::class,'getName']);
    $router->get('/api/status',[App\Admin\Controllers\StatusController::class,'getName']);

    $router->resource('peripherals', PeripheralController::class);
    $router->resource('solutions', SolutionController::class);
    $router->resource('brands', BrandController::class);
    $router->resource('pbinds', PbindController::class);
    $router->resource('types',TypeController::class);
    $router->resource('releases',ReleaseController::class);
    $router->resource('chips',ChipController::class);
    $router->resource('statuses',StatusController::class);
    $router->resource('specifications',SpecificationController::class);
    $router->resource('values',ValueController::class);
    $router->resource('solution_matches',SolutionMatchController::class);
    $router->resource('sbinds', SbindController::class);
    $router->resource('softwares', SoftwareController::class);
    $router->resource('manufactors',ManufactorController::class);

});
