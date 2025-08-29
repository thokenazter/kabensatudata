<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SavePreviousUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        // Simpan URL saat ini jika user belum login dan bukan halaman login/admin
        if (!Auth::check() && !$request->is('admin/login') && !$request->is('login')) {
            // Simpan semua URL publik ke session (kecuali halaman login)
            session(['previous_url' => url()->current()]);
        }

        return $next($request);
    }
}
