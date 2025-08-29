<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\FamilyMember;
use App\Observers\FamilyMemberObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;



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
    }
}
