<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name', 'client_title', 'client_photo', 'testimonial', 'rating',
        'offer_title', 'travel_date', 'is_featured', 'is_published', 'order'
    ];

    protected $casts = [
        'travel_date' => 'date',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('home.testimonials'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('home.testimonials'));
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at', 'desc');
    }

    public function getStarsHtmlAttribute()
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= $this->rating
                ? '<i class="fas fa-star"></i>'
                : '<i class="far fa-star"></i>';
        }
        return $html;
    }

    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->client_name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }
}