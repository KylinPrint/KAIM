<?php

namespace App\AuditResolvers;

use Dcat\Admin\Admin;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class AdminUserResolver implements Resolver
{

    public static function resolve(Auditable $auditable = null)
    {
        return Admin::user()->id;
    }
} 