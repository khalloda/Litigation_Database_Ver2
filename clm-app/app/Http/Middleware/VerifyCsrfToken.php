<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // For AJAX requests, return JSON instead of redirecting
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch.',
                    'exception' => 'Symfony\\Component\\HttpKernel\\Exception\\HttpException'
                ], 419);
            }
            
            // For regular requests, let the parent handle the redirect
            throw $e;
        }
    }
}
