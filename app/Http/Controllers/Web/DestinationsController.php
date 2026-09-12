<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Offer;

class DestinationsController extends Controller
{
    /**
     * Métadonnées hero par slug de pays.
     * Utilisées comme fallback si les champs region/hero_tagline
     * ne sont pas encore renseignés en base.
     */
    private array $heroFallback = [
        'benin'        => ["Afrique de l'Ouest", "Du delta de l'Ouémé aux plateaux de l'Atakora"],
        'senegal'      => ["Afrique de l'Ouest", "Des rives du fleuve Sénégal aux plages de Casamance"],
        'togo'         => ["Afrique de l'Ouest", "Du littoral atlantique aux monts du Togo"],
        'cote-divoire' => ["Afrique de l'Ouest", "De la forêt équatoriale aux savanes du Nord"],
        'ghana'        => ["Afrique de l'Ouest", "Des forts coloniaux aux plages tropicales du Golfe de Guinée"],
        'niger'        => ["Afrique de l'Ouest", "Des dunes de l'Aïr aux rivages du fleuve Niger"],
        'burkina-faso' => ["Afrique de l'Ouest", "Des cascades de Banfora aux falaises de Sindou"],
        'mali'         => ["Afrique de l'Ouest", "De la falaise de Bandiagara aux méandres du Niger"],
        'cameroun'     => ["Afrique centrale",   "Des plages du littoral atlantique aux sommets du Mont Cameroun"],
        'maroc'        => ["Afrique du Nord",     "Des médinas impériales aux dunes du Sahara"],
        'kenya'        => ["Afrique de l'Est",    "Des savanes du Masai Mara aux rives du lac Turkana"],
    ];

    public function index()
    {
        // Pays ayant au moins une ville active
        $countriesWithCities = Country::whereHas('cities', fn ($q) => $q->where('is_active', true))
            ->withCount(['cities' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $selectedCountry = null;

        if ($slug = request()->query('pays')) {
            $selectedCountry = $countriesWithCities->firstWhere('slug', $slug);
        }

        // 2+ pays, aucun choisi → écran de sélection pays
        if ($countriesWithCities->count() >= 2 && ! $selectedCountry) {
            return view('pages.destinations-countries', compact('countriesWithCities'));
        }

        // Un seul pays → on l'utilise directement
        if ($countriesWithCities->count() === 1) {
            $selectedCountry = $countriesWithCities->first();
        }

        // ── Métadonnées hero dynamiques ──────────────────────────────────────
        // Priorité : champs en base → fallback tableau → valeurs vides
        $heroRegion  = null;
        $heroTagline = null;

        if ($selectedCountry) {
            $fallback = $this->heroFallback[$selectedCountry->slug] ?? [null, null];

            $heroRegion  = $selectedCountry->region  ?? $fallback[0];
            $heroTagline = $selectedCountry->hero_tagline ?? $fallback[1];
        }

        // Villes featured — section "À la une"
        $featuredCities = City::where('is_active', true)
            ->with('country')
            ->when($selectedCountry, fn ($q) => $q->where('country_id', $selectedCountry->id))
            ->where('is_featured', true)
            ->withCount(['offers' => fn ($q) => $q->where('status', 'published')])
            ->withMin(
                ['offers as offers_min_base_price' => fn ($q) => $q->where('status', 'published')],
                'price'
            )
            ->orderBy('featured_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Toutes les villes — grille paginée
        $cities = City::where('is_active', true)
            ->with('country')
            ->when($selectedCountry, fn ($q) => $q->where('country_id', $selectedCountry->id))
            ->withCount(['offers' => fn ($q) => $q->where('status', 'published')])
            ->withMin(
                ['offers as offers_min_base_price' => fn ($q) => $q->where('status', 'published')],
                'price'
            )
            ->orderByDesc('is_featured')
            ->orderBy('featured_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(12)
            ->withQueryString();

        // Les 2 premières villes → hero cards horizontales
        // Le reste → grille standard
        $heroCards = $cities->getCollection()->take(2);
        $gridCards = $cities->getCollection()->skip(2)->values();

        // Stats globales (scopées au pays choisi s'il y en a un)
        $totalOffers = Offer::where('status', 'published')
            ->when($selectedCountry, fn ($q) => $q->whereHas('city', fn ($c) => $c->where('country_id', $selectedCountry->id)))
            ->count();

        $totalCities = City::where('is_active', true)
            ->when($selectedCountry, fn ($q) => $q->where('country_id', $selectedCountry->id))
            ->count();

        $globalRating = City::where('is_active', true)
            ->when($selectedCountry, fn ($q) => $q->where('country_id', $selectedCountry->id))
            ->where('average_rating', '>', 0)
            ->avg('average_rating') ?? 4.8;

        $totalReviews = class_exists(\App\Models\Review::class)
            ? \App\Models\Review::count()
            : 0;

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
            'countriesWithCities',
            'selectedCountry',
            'heroRegion',    // ← nouveau
            'heroTagline',   // ← nouveau
        ));
    }
}