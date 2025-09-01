<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreCurrentUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        // Store current URL if not a filament route
        if (!str_starts_with($request->path(), 'admin')) {
            session(['previous_url' => url()->current()]);
        }

        return $next($request);
    }
}
