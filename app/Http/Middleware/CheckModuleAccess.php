<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $moduleKey = null): Response
    {
        $user = auth()->user();
        if (!$user) return $next($request);

        // SuperAdmin should only access super-admin routes
        if ($user->isSuperAdmin()) {
            if (!$request->is('super-admin*') && !$request->is('logout') && !$request->is('api/*') && !$request->is('frex/*')) {
                return redirect()->route('super-admin.dashboard');
            }
            return $next($request);
        }

        // Customers are strictly confined to the portal
        if ($user->isCustomer()) {
            if (!$request->is('portal*') && !$request->is('logout')) {
                return redirect()->route('customer.dashboard');
            }
            return $next($request);
        }

        // Clients are strictly confined to the client portal
        if ($user->role === 'client') {
            if (!$request->is('client-portal*') && !$request->is('logout')) {
                return redirect()->route('client.index');
            }
            return $next($request);
        }

        // Determine module key from URL if not explicitly passed
        if (!$moduleKey) {
            $segment = $request->segment(1);
            
            // Map URL segments to config module keys if they differ
            $segmentMap = [
                'trade-partner' => 'trade-partners',
                'report' => 'reports',
            ];
            
            $moduleKey = $segmentMap[$segment] ?? $segment;
            
            // If the segment is not a registered module in config, we don't restrict it here
            // (e.g. /dashboard, /profile, /useful-links)
            if (!array_key_exists($moduleKey, config('modules', []))) {
                return $next($request);
            }
        }

        // If user has no company, deny access
        if (!$user->company_id || !$user->company) {
            abort(403, 'No company associated with your account.');
        }

        // Check if company has access to this module
        if (!$user->company->hasModule($moduleKey)) {
            abort(403, 'Your company does not have access to this module.');
        }

        return $next($request);
    }
}
