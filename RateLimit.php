<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitProvider extends ServiceProvider
{
    /**
     * Register rate limiting services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap rate limiting services.
     */
    public function boot(): void
    {
        // Rate limiting untuk login (5 requests per menit per IP)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limiting untuk API (60 requests per menit per pengguna)
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(20)->by($request->ip());
        });

        // Rate limiting untuk pencarian (10 requests per menit per IP)
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())
                ->response(function () {
                    return response('Terlalu banyak permintaan pencarian. Silakan coba lagi nanti.', 429);
                });
        });

        // Rate limiting untuk semuanya (300 requests per menit per IP)
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(300)->by($request->ip());
        });
    }
}
