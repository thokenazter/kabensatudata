<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SavePreviousUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        // Simpan URL saat ini jika user belum login dan bukan halaman login/admin
        if (!auth()->check() && !$request->is('admin/login')) {
            // Hanya simpan URL untuk halaman map dan analysis
            if ($request->is('map') || $request->is('analysis')) {
                session(['previous_url' => url()->current()]);
            }
        }

        return $next($request);
    }
}
