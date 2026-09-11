<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'country_id', 'latitude', 'longitude', 'description',
        'cover_image', 'region', 'distance_from_cotonou', 'duration_days',
        'best_season', 'category', 'average_rating', 'is_featured', 'is_active',
        'featured_order', 'highlights', 'landmarks', 'how_to_get_there',
        'best_time_detail', 'budget_range', 'fun_facts',
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

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($city) {
            if (empty($city->slug)) {
                $city->slug = static::uniqueSlug(Str::slug($city->name));
            }
        });

        static::updating(function ($city) {
            if (empty($city->slug)) {
                $city->slug = static::uniqueSlug(Str::slug($city->name), $city->id);
            }
        });

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('home.featured_cities');
            \Illuminate\Support\Facades\Cache::forget('home.stats');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('home.featured_cities');
            \Illuminate\Support\Facades\Cache::forget('home.stats');
        });
    }

    protected static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Offer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    public function getPublishedOffersCountAttribute(): int
    {
        return $this->offers()->published()->count();
    }

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