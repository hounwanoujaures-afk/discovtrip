<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Offer;

class DestinationsController extends Controller
{
    public function index()
    {
        // Villes featured — section "À la une"
        $featuredCities = City::where('is_featured', true)
            ->where('is_active', true)
            ->withCount(['offers' => fn ($q) => $q->where('status', 'published')])
            ->withMin(
                ['offers as offers_min_base_price' => fn ($q) => $q->where('status', 'published')],
                'base_price'
            )
            ->orderBy('featured_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Toutes les villes — grille paginée
        $cities = City::where('is_active', true)
            ->withCount(['offers' => fn ($q) => $q->where('status', 'published')])
            ->withMin(
                ['offers as offers_min_base_price' => fn ($q) => $q->where('status', 'published')],
                'base_price'
            )
            ->orderByDesc('is_featured')
            ->orderBy('featured_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(12);

        // ── Fix slice/take : extraire depuis la collection du paginator ──
        // Les 2 premières villes → hero cards horizontales
        // Le reste → grille standard
        $heroCards = $cities->getCollection()->take(2);
        $gridCards = $cities->getCollection()->skip(2)->values();

        // Stats globales
        $totalOffers  = Offer::where('status', 'published')->count();
        $totalCities  = City::where('is_active', true)->count();

        // Note globale
        $globalRating = City::where('is_active', true)
            ->where('average_rating', '>', 0)
            ->avg('average_rating') ?? 4.8;

        // Total avis
        $totalReviews = class_exists(\App\Models\Review::class)
            ? \App\Models\Review::count()
            : 0;

        // Visiteurs actifs — pseudo-dynamique
        $activeViewers = 12 + (now()->hour % 12) + (now()->minute % 8);

        $heroImage = 'images/hero.jpg';

        return view('pages.destinations', compact(
            'featuredCities',
            'cities',
            'heroCards',
            'gridCards',
            'totalOffers',
            'totalCities',
            'globalRating',
            'totalReviews',
            'activeViewers',
            'heroImage',
        ));
    }
}