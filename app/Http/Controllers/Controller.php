<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Traits\ApiResponse;

abstract class Controller
{
    use ApiResponse;

    protected function tenant(): Tenant
    {
        return app('currentTenant');
    }
}
