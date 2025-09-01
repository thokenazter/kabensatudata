<?php

namespace App\Providers;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class FilamentLoginResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override Filament login response
        $this->app->singleton(LoginResponse::class, function (): LoginResponse {
            return new class implements LoginResponse {
                public function toResponse($request): RedirectResponse | Redirector
                {
                    // Selalu redirect ke /dashboard setelah login berhasil
                    return redirect('/dashboard');
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
