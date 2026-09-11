<?php

namespace App\Observers;

use App\Models\Offer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class OfferObserver
{
    /**
     * Vide les caches de la page d'accueil affectés par les offres.
     * Sans ça, une offre créée/modifiée/supprimée en admin n'apparaît (ou ne
     * disparaît) sur la page d'accueil qu'après expiration naturelle du cache
     * (jusqu'à 10 minutes) — bug remonté sept. 2026.
     */
    private function forgetHomeCaches(): void
    {
        Cache::forget('home.featured_offers');
        Cache::forget('home.stats');
    }

    public function created(Offer $offer): void
    {
        $this->forgetHomeCaches();
    }

    public function updated(Offer $offer): void
    {
        $this->forgetHomeCaches();

        if ($offer->wasChanged('cover_image')) {
            $old = $offer->getOriginal('cover_image');
            if ($old && $old !== $offer->cover_image) {
                Storage::disk('public')->delete($old);
            }
        }

        if ($offer->wasChanged('gallery')) {
            $oldGallery = (array) ($offer->getOriginal('gallery') ?? []);
            $newGallery = (array) ($offer->gallery ?? []);

            if (count($oldGallery) === 1 && is_string($oldGallery[0])) {
                $decoded = json_decode($oldGallery[0], true);
                if (is_array($decoded)) $oldGallery = $decoded;
            }

            $removed = array_diff($oldGallery, $newGallery);
            foreach ($removed as $file) {
                if ($file) Storage::disk('public')->delete($file);
            }
        }
    }

    public function deleting(Offer $offer): void
    {
        if ($offer->cover_image) {
            Storage::disk('public')->delete($offer->cover_image);
        }

        foreach ((array) ($offer->gallery ?? []) as $image) {
            if ($image) Storage::disk('public')->delete($image);
        }
    }

    public function deleted(Offer $offer): void
    {
        $this->forgetHomeCaches();
    }
}