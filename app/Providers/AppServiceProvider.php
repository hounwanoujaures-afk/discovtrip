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
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        // DIAGNOSTIC CIBLÉ - à supprimer après fix
        if (request()->is('*/upload-file')) {
            $req = request();
            \Log::info('=== UPLOAD SIGNATURE DEBUG ===', [
                'request_url'    => $req->url(),
                'request_scheme' => $req->getScheme(),
                'is_secure'      => $req->isSecure(),
                'app_url'        => config('app.url'),
                'signature_ok'   => $req->hasValidSignature(),
                'expires'        => $req->query('expires'),
                'now'            => time(),
            ]);
        }

        Blade::anonymousComponentPath(resource_path('views/emails'), 'emails');
        Offer::observe(OfferObserver::class);
    }
}