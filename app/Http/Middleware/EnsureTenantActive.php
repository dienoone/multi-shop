<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app()->bound('currentTenant')
            ? app('currentTenant')
            : null;

        if ($tenant && !$tenant->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This store is currently unavailable.',
                'data'    => null,
                'meta'    => null,
                'error'   => ['code' => 'TENANT_INACTIVE'],
            ], 503);
        }

        return $next($request);
    }
}
