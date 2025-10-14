<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  Permission name to check (e.g., 'cases.view')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(403, 'Unauthorized: Please login first.');
        }

        // Check if user has the required permission
        if (!auth()->user()->can($permission)) {
            abort(403, "Unauthorized: You don't have permission to perform this action.");
        }

        return $next($request);
    }
}
