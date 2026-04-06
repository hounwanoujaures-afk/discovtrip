<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use App\Models\Offer;
use App\Models\SiteSetting;
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
            URL::forceRootUrl(config('app.url'));
        }

        Blade::anonymousComponentPath(
            resource_path('views/emails'),
            'emails'
        );

        Offer::observe(OfferObserver::class);

        // Directive Blade @heroImage('key', 'fallback')
        // Retourne l'URL de l'image hero depuis la DB, avec fallback statique
        Blade::directive('heroImage', function (string $expression) {
            return "<?php echo App\\Providers\\AppServiceProvider::heroImage({$expression}); ?>";
        });
    }

    /**
     * Retourne l'URL d'une image hero gérée en admin.
     * Retourne null si aucune image en DB et pas de fallback explicite.
     */
    public static function heroImage(string $key, string $fallback = ''): ?string
    {
        try {
            $setting = SiteSetting::where('key', $key)->value('value');
            if ($setting) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($setting);
            }
        } catch (\Throwable $e) {
            // DB pas encore disponible au build
        }

        return $fallback ?: null;
    }
}