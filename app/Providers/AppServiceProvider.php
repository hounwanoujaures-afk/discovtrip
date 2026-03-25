<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
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
        // Blade anonymous components pour les emails
        // Usage : <x-emails.layout> dans les vues
        Blade::anonymousComponentPath(
            resource_path('views/emails'),
            'emails'
        );

        // Observer Offer
        Offer::observe(OfferObserver::class);
    }
}