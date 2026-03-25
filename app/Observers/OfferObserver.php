<?php

namespace App\Observers;

use App\Models\Offer;
use Illuminate\Support\Facades\Storage;

class OfferObserver
{
    /**
     * Appelé après la mise à jour d'une offre.
     * Supprime les anciens fichiers remplacés ou retirés.
     */
    public function updated(Offer $offer): void
    {
        // ── Cover image
        if ($offer->wasChanged('cover_image')) {
            $old = $offer->getOriginal('cover_image');
            if ($old && $old !== $offer->cover_image) {
                Storage::disk('public')->delete($old);
            }
        }

        // ── Gallery : supprimer les images retirées
        if ($offer->wasChanged('gallery')) {
            $oldGallery = (array) ($offer->getOriginal('gallery') ?? []);
            $newGallery = (array) ($offer->gallery ?? []);

            // Décoder si stocké en JSON string
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

    /**
     * Appelé avant la suppression d'une offre.
     * Supprime toutes les images liées.
     */
    public function deleting(Offer $offer): void
    {
        if ($offer->cover_image) {
            Storage::disk('public')->delete($offer->cover_image);
        }

        foreach ((array) ($offer->gallery ?? []) as $image) {
            if ($image) Storage::disk('public')->delete($image);
        }
    }
}