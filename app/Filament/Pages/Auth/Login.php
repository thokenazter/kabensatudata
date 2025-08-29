<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class Login extends BaseLogin
{
    public function getRedirectUrl(): string
    {
        // Debugging
        Log::info('Login::getRedirectUrl dipanggil');

        // 1. Cek apakah ada query string '?redirect=/custom-page'
        if (Request::query('redirect')) {
            $url = Request::query('redirect');
            Log::info('Redirect URL dari query string: ' . $url);
            return $url;
        }

        // 2. Cek apakah ada intended URL (halaman yang membutuhkan login)
        if (session()->has('url.intended')) {
            // Pastikan URL intended bukan halaman admin
            $intendedUrl = session()->get('url.intended');
            Log::info('Intended URL dari session: ' . $intendedUrl);

            if (strpos($intendedUrl, '/admin/login') === false) {
                Log::info('Menggunakan intended URL: ' . $intendedUrl);
                return session()->pull('url.intended');
            }
        }

        // 3. Cek apakah ada previous URL tersimpan di session
        if (session()->has('previous_url')) {
            $previousUrl = session()->get('previous_url');
            Log::info('Previous URL dari session: ' . $previousUrl);
            return session()->pull('previous_url');
        }

        // 4. Default: redirect ke dashboard
        Log::info('Tidak ada URL redirect yang ditemukan, menggunakan default: /dashboard');
        return '/dashboard';
    }
}
