<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use App\Models\Offer;
use App\Observers\OfferObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // DIAGNOSTIC TEMPORAIRE
        \Log::info('=== APP_KEY PREFIX: ' . substr(md5(config('app.key')), 0, 8) . ' ===');

        Blade::anonymousComponentPath(
            resource_path('views/emails'),
            'emails'
        );

        Offer::observe(OfferObserver::class);
    }
}