<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\FamilyMember;
use App\Observers\FamilyMemberObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\ClockMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mendaftarkan observer untuk model FamilyMember
        FamilyMember::observe(FamilyMemberObserver::class);

        View::composer('*', function ($view) {
            $view->with('shouldBlurData', !Auth::check());
        });

        // Daftarkan asset JS untuk voice input
        FilamentAsset::register([
            Js::make('voice-input', asset('js/voice-input.js')),
        ]);

        // Tambahkan macro untuk komponen Filament
        if (class_exists(\Filament\Forms\Components\TextInput::class)) {
            \Filament\Forms\Components\TextInput::macro('enableVoiceInput', function () {
                return $this->extraAttributes(['class' => 'voice-input']);
            });

            \Filament\Forms\Components\Select::macro('enableVoiceInput', function () {
                return $this->extraAttributes(['class' => 'voice-input']);
            });

            \Filament\Forms\Components\Toggle::macro('enableVoiceInput', function () {
                return $this->extraAttributes(['class' => 'voice-input']);
            });
        }

        // Monitor query yang lambat
        if (app()->environment('local')) {
            DB::listen(function ($query) {
                // Log query yang memakan waktu > 100ms
                if ($query->time > 100) {
                    Log::channel('daily')->warning('Query Lambat: ' . $query->sql, [
                        'time' => $query->time . 'ms',
                        'bindings' => $query->bindings,
                        'connection' => $query->connection->getName(),
                    ]);
                }
            });
        }

        // Aliaskan middleware Spatie Permission agar dapat dipakai sebagai 'role', 'permission', dan 'role_or_permission'
        if ($this->app->bound('router')) {
            $router = $this->app->make('router');
            if (method_exists($router, 'aliasMiddleware')) {
                $router->aliasMiddleware('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
                $router->aliasMiddleware('permission', \Spatie\Permission\Middleware\PermissionMiddleware::class);
                $router->aliasMiddleware('role_or_permission', \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class);
            }
        }

        // Catatan: ClockMiddleware perlu didaftarkan di app/Http/Middleware.php atau route secara manual
    }
}
