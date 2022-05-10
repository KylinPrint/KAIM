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
                child = div.childNodes;
                for (var i = child.length - 1; i >= 0; i--) {
                    div.removeChild(child[i]);
                }
            JS
        );
    }
}