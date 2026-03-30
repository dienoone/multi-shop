<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host       = $request->getHost();
        $baseDomain = config('app.base_domain', 'localhost');

        // Strip the base domain to get the subdomain
        // e.g. store1.multishop.test → store1
        if (!str_ends_with($host, '.' . $baseDomain)) {
            // No subdomain — super admin area or local dev without subdomain
            return $next($request);
        }

        $subdomain = str_replace('.' . $baseDomain, '', $host);

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found.',
                'data'    => null,
                'meta'    => null,
                'error'   => ['code' => 'TENANT_NOT_FOUND'],
            ], 404);
        }

        // Bind to the service container — BelongsToTenant reads from here
        app()->instance('currentTenant', $tenant);

        // Also attach to request for convenience in controllers
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
