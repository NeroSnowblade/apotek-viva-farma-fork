<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Optionally force HTTPS scheme for generated URLs (set FORCE_HTTPS=true in production env)
        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }
}
