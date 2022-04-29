<?php

use App\Models\Type;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Layout\Menu;
use Dcat\Admin\Show;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Admin::menu(function (Menu $menu) {
    $types = Type::where('parent', 0)->get();
    $menu_peripherals[] = [
        'id'            => '1',
        'title'         => '外设适配数据',
        'icon'          => 'fa-file-text-o',
        'uri'           => '',
        'parent_id'     => 0,
        'roles'         => ['bd', 'menu-test'],
    ];
    foreach ($types as $type) {
        $first_child_type = Type::where('parent', $type->id)->pluck('id')->first();
        $menu_peripherals[] = [
            'id'            => $type->id + 1,
            'title'         => $type->name,
            'icon'          => 'fa-file-text-o',
            'uri'           => 'peripherals?type=' . $first_child_type,
            'parent_id'     => 1,
            'roles'         => ['bd', 'menu-test'],
        ];
    }
    // 软件适配菜单
    $menu->add([
        
    ]);
    // 外设适配菜单
    $menu->add($menu_peripherals, -2);
    // 其它信息菜单
    $menu->add([
        
    ]);
    // 适配需求菜单
    $menu->add([
        
    ]);
    // 实用工具菜单
    $menu->add([
        
    ]);
    // 数据统计菜单
    $menu->add([
        
    ]);
});
