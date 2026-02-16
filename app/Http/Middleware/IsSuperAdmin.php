<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('[IsSuperAdmin Middleware] Incoming request', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'user_role' => optional(auth()->user())->role,
        ]);

        if (auth()->check() && auth()->user()->role == 'super_admin') {
            \Log::info('[IsSuperAdmin Middleware] Passed: User is super_admin', [
                'user_id' => auth()->id(),
            ]);
            return $next($request);
        }

        \Log::warning('[IsSuperAdmin Middleware] Blocked: Unauthorized access', [
            'user_id' => auth()->id(),
            'user_role' => optional(auth()->user())->role,
        ]);
        abort(403, 'Unauthorized access.');
    }
}
