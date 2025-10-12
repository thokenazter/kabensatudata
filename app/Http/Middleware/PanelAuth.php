<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            // Tampilkan halaman error kustom tanpa redirect ke login
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden: panel access requires authentication.'
                ], 403);
            }
            return response()->view('errors.panel-forbidden', [], 403);
        }

        return $next($request);
    }
}
