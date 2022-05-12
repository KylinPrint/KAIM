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
    // 软件适配菜单
    $menu->add([
        [
            'id'            => '1',
            'title'         => '软件适配结果管理',
            'icon'          => 'fa-tv',
            'uri'           => '',
            'parent_id'     => 0,
            'permission_id' => ['sbinds-get', 'softwares-get'],
        ],
        [
            'id'            => '2',
            'title'         => '软件适配管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'sbinds',
            'parent_id'     => 1,
            'permission_id' => ['sbinds-get'],
        ],
        [
            'id'            => '3',
            'title'         => '软件数据管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'softwares',
            'parent_id'     => 1,
            'permission_id' => ['softwares-get'],
        ],
    ]);

    // 外设适配菜单
    // 先填固定的部分
    $id = 0;
    $menu_peripherals = [
        [
            'id'            => ++$id,
            'title'         => '外设适配结果管理',
            'icon'          => 'fa-tv',
            'uri'           => '',
            'parent_id'     => 0,
            'permission_id' => ['pbinds-get', 'peripherals-get'],
        ],
        [
            'id'            => ++$id,
            'title'         => '外设适配管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'pbinds',
            'parent_id'     => 1,
            'permission_id' => ['pbinds-get'],
        ],
        [
            'id'            => ++$id,
            'title'         => '外设数据管理',
            'icon'          => 'fa-tv',
            'uri'           => '',
            'parent_id'     => 1,
            'permission_id' => ['peripherals-get'],
        ],
    ];
    
    // 再填不同分类
    $types = Type::where('parent', 0)->get();
    foreach ($types as $type) {
        $first_child_type = Type::where('parent', $type->id)->pluck('id')->first();
        $menu_peripherals[] = [
            'id'            => ++$id,
            'title'         => $type->name,
            'icon'          => 'fa-arrow-circle-right',
            'uri'           => 'peripherals?type=' . $first_child_type,
            'parent_id'     => 3,
            'roles'         => ['peripherals-get'],
        ];
    }
    $menu->add($menu_peripherals);

    // 整机适配菜单
    $menu->add([
        [
            'id'            => '1',
            'title'         => '整机适配数据管理',
            'icon'          => 'fa-tv',
            'uri'           => 'oems',
            'parent_id'     => 0,
            'permission_id' => ['oems-get'],
        ],
    ]);

    // 其它信息菜单
    $id = 0;
    $menu->add([
        [
            'id'            => ++$id,
            'title'         => '其他信息管理',
            'icon'          => 'fa-tv',
            'uri'           => 'oems',
            'parent_id'     => 0,
            'permission_id' => ['manufactors', 'brands', 'chips', 'releases', 'types', 'otypes', 'specifications', 'statuses'],
        ],
        [
            'id'            => ++$id,
            'title'         => '厂商信息管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'manufactors',
            'parent_id'     => 1,
            'permission_id' => ['manufactors'],
        ],
        [
            'id'            => ++$id,
            'title'         => '品牌信息管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'brands',
            'parent_id'     => 1,
            'permission_id' => ['brands'],
        ],
        [
            'id'            => ++$id,
            'title'         => '芯片信息管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'chips',
            'parent_id'     => 1,
            'permission_id' => ['chips'],
        ],
        [
            'id'            => ++$id,
            'title'         => '操作系统版本管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'releases',
            'parent_id'     => 1,
            'permission_id' => ['releases'],
        ],
        [
            'id'            => ++$id,
            'title'         => '软件分类管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'stypes',
            'parent_id'     => 1,
            'permission_id' => ['types'],
        ],
        [
            'id'            => ++$id,
            'title'         => '外设分类管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'types',
            'parent_id'     => 1,
            'permission_id' => ['types'],
        ],
        [
            'id'            => ++$id,
            'title'         => '整机分类管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'otypes',
            'parent_id'     => 1,
            'permission_id' => ['otypes'],
        ],
        [
            'id'            => ++$id,
            'title'         => '外设参数管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'specifications',
            'parent_id'     => 1,
            'permission_id' => ['specifications'],
        ],
        // 先别管理了
        // [
        //     'id'            => ++$id,
        //     'title'         => '适配状态管理',
        //     'icon'          => 'fa-angle-double-right',
        //     'uri'           => 'statuses',
        //     'parent_id'     => 1,
        //     'permission_id' => ['statuses'],
        // ],
    ]);

    // 适配需求菜单
    $menu->add([
        [
            'id'            => 1,
            'title'         => '适配需求管理',
            'icon'          => 'fa-tv',
            'uri'           => '',
            'parent_id'     => 0,
            'permission_id' => ['srequests-get', 'prequests-get'],
        ],
        [
            'id'            => 2,
            'title'         => '软件需求管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'srequests',
            'parent_id'     => 1,
            'permission_id' => ['srequests-get'],
        ],
        [
            'id'            => 3,
            'title'         => '外设需求管理',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'prequests',
            'parent_id'     => 1,
            'permission_id' => ['prequests-get'],
        ],
    ]);

    // 实用工具菜单
    $menu->add([
        [
            'id'            => 1,
            'title'         => '实用工具',
            'icon'          => 'fa-wrench',
            'uri'           => '',
            'parent_id'     => 0,
            'permission_id' => ['solution-match'],
        ],
        [
            'id'            => 2,
            'title'         => '解决方案快速筛查',
            'icon'          => 'fa-angle-double-right',
            'uri'           => 'solution-match',
            'parent_id'     => 1,
            'permission_id' => ['solution-match'],
        ],
    ]);
    // 数据统计菜单
    $menu->add([
        [
            'id'            => 1,
            'title'         => '数据统计',
            'icon'          => 'fa-bar-chart',
            'uri'           => 'statistics',
            'parent_id'     => 0,
            'permission_id' => ['statistics'],
        ],
    ]);
});
