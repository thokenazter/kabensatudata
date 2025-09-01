<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeComponentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::componentNamespace('App\\View\\Components', 'app');

        // Alias komponen umum
        Blade::component('components.sensitive-data', 'sensitive-data');
        Blade::component('components.health-indicator', 'health-indicator');
        Blade::component('components.toggle-sensitive', 'toggle-sensitive');
    }
}
