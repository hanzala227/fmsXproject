<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Usage in routes: middleware('role:Admin,User')
     * This restricts the route to ONLY users with role Admin or User.
     * Any other role gets redirected to their correct dashboard.
     *
     * @param  string  ...$roles  Comma-separated allowed roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user's role is in the allowed list
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Get the target dashboard URL
        $targetUrl = $this->getDashboardUrl($user);

        // If the target URL is the current request URL, allow access to prevent an infinite loop
        if ($request->url() === $targetUrl || $request->fullUrl() === $targetUrl) {
            return $next($request);
        }

        // Redirect to the correct dashboard for the user's role
        return redirect($targetUrl);
    }

    /**
     * Get the correct dashboard URL for a user based on their role.
     */
    protected function getDashboardUrl($user): string
    {
        return match ($user->role) {
            'SuperAdmin' => route('super-admin.dashboard'),
            'Customer'   => route('customer.dashboard'),
            'client'     => route('client.index'),
            default      => route('dashboard'),
        };
    }
}
