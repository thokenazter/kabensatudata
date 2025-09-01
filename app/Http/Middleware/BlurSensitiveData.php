<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlurSensitiveData
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tambahkan flag ke request yang bisa diakses di controllers dan views
        app()->singleton('blur_sensitive_data', function () {
            return !auth()->check();
        });

        return $next($request);
    }
}
