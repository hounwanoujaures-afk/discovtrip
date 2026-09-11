<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Country extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'currency',
        'phone_code',
        'continent',
        'region',        // ex : "Afrique de l'Ouest"
        'hero_tagline',  // ex : "Du delta de l'Ouémé aux plateaux de l'Atakora"
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($country) {
            if (empty($country->slug)) {
                $country->slug = static::uniqueSlug(Str::slug($country->name));
            }
        });

        static::updating(function ($country) {
            if (empty($country->slug)) {
                $country->slug = static::uniqueSlug(Str::slug($country->name), $country->id);
            }
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

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Emoji drapeau calculé depuis le code ISO à 2 lettres (BJ → 🇧🇯).
     */
    public function getFlagEmojiAttribute(): string
    {
        if (! $this->code || strlen($this->code) !== 2) {
            return '🌍';
        }

        $code = strtoupper($this->code);

        return mb_chr(127397 + ord($code[0])) . mb_chr(127397 + ord($code[1]));
    }
}