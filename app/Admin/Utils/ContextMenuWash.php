<?php

namespace App\Admin\Utils;

use Dcat\Admin\Admin;

class ContextMenuWash
{
    public static function wash()
    {
        Admin::script(
            <<<JS
                div = document.getElementById('grid-context-menu');
                div.innerHTML = "";
            JS
        );
    }
}