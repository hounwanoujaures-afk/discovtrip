<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'country_id',
        'latitude',
        'longitude',
        'description',
        'cover_image',
        'region',
        // Métadonnées voyageur
        'distance_from_cotonou',
        'duration_days',
        'best_season',
        'category',
        'average_rating',
        // Featuring & visibilité
        'is_featured',
        'is_active',
        'featured_order',
        // Blocs éditoriaux
        'highlights',
        'landmarks',
        'how_to_get_there',
        'best_time_detail',
        'budget_range',
        'fun_facts',
    ];

    protected $casts = [
        'is_featured'    => 'boolean',
        'is_active'      => 'boolean',
        'average_rating' => 'decimal:1',
        'latitude'       => 'float',
        'longitude'      => 'float',
        'highlights'     => 'array',
        'landmarks'      => 'array',
        'fun_facts'      => 'array',
    ];

    // ════════════════════════════════════════════════════════
    // RELATIONS
    // ════════════════════════════════════════════════════════

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Tous les avis des offres de cette ville (via offers).
     */
    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Offer::class);
    }

    // ════════════════════════════════════════════════════════
    // SCOPES
    // ════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    // ════════════════════════════════════════════════════════
    // ACCESSORS
    // ════════════════════════════════════════════════════════

    /**
     * Nombre d'offres publiées (nécessite withCount pour éviter une requête supplémentaire).
     */
    public function getPublishedOffersCountAttribute(): int
    {
        return $this->offers()->published()->count();
    }

    // ════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════

    /**
     * Recalcule la note moyenne de la ville à partir des reviews de ses offres.
     *
     * CORRECTION : calcul via reviews (pas via average_rating sur offers,
     * qui est une colonne dénormalisée potentiellement vide).
     *
     * Usage : appelé dans un Observer ou une commande artisan.
     */
    public function recalculateRating(): void
    {
        $avg = Review::query()
            ->whereHas('offer', fn ($q) => $q->where('city_id', $this->id)
                                             ->where('status', 'published'))
            ->where('status', 'published')
            ->avg('rating');

        $this->update(['average_rating' => round((float) ($avg ?? 0), 1)]);
    }
}