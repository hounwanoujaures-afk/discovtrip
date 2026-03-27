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
        // Forcer HTTPS en production (Railway proxy)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Blade anonymous components pour les emails
        Blade::anonymousComponentPath(
            resource_path('views/emails'),
            'emails'
        );

        // Observer Offer
        Offer::observe(OfferObserver::class);
    }
}