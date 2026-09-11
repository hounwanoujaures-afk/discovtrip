@extends('layouts.app')

@section('title', 'Destinations — Choisissez un pays | DiscovTrip')

@push('meta')
<meta name="description" content="Découvrez nos destinations à travers {{ $countriesWithCities->count() }} pays d'Afrique de l'Ouest.">
@endpush

@push('styles')
    @vite('resources/css/pages/destinations-countries.css')
@endpush

@php
    $totalCitiesAllCountries = $countriesWithCities->sum('cities_count');
@endphp

@section('content')

<section class="dp-hero dc-hero">
    <x-hero-bg setting-key="hero_destinations" pattern-id="wp-dc" />

    {{-- Drapeaux flottants : de vrais pays de la page, pas des images en dur --}}
    <div class="dc-hero-flags" aria-hidden="true">
        @foreach($countriesWithCities->take(6) as $j => $flagCountry)
        <img src="https://flagcdn.com/w160/{{ strtolower($flagCountry->code) }}.png"
             alt=""
             class="dc-hero-flag-chip"
             style="--fi:{{ $j }}">
        @endforeach
    </div>

    <div class="dt-container dc-hero-inner">
        <div class="dp-hero-eyebrow">
            <span class="dp-eyebrow-dot"></span>
            {{ $countriesWithCities->count() }} pays · Afrique de l'Ouest
            <span class="dp-eyebrow-dot"></span>
        </div>
        <h1 class="dp-hero-title dc-hero-title">
            Un guide local dans<br><em>chaque pays.</em>
        </h1>
        <span class="dc-hero-title-rule" aria-hidden="true"></span>
        <p class="dc-hero-sub">
            {{ $countriesWithCities->count() }} pays, {{ $totalCitiesAllCountries }} destinations —
            chaque circuit est mené par un guide indépendant, sur place.
            Sélectionnez un pays pour réserver le vôtre.
        </p>
        <a href="#dc-grid" class="dc-hero-cta">
            Choisir mon pays
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6l4 4 4-4"/>
            </svg>
        </a>
    </div>
</section>

<section class="dc-grid-section" id="dc-grid">
    <div class="dt-container">
        <div class="dc-grid">
            @foreach($countriesWithCities as $i => $country)
            <a href="{{ route('destinations', ['pays' => $country->slug]) }}"
               class="dc-card"
               style="--delay:{{ $i * 0.06 }}s">

                <div class="dc-card-media">
                    <div class="dc-card-flagwrap">
                        <div class="dc-card-flagwave">
                            <img src="https://flagcdn.com/w320/{{ strtolower($country->code) }}.png"
                                 alt="Drapeau {{ $country->name }}"
                                 class="dc-card-flagimg"
                                 loading="lazy">
                        </div>
                        <div class="dc-card-vignette"></div>
                        <div class="dc-card-glow"></div>
                    </div>
                    <span class="dc-card-seal">{{ $country->code }}</span>
                </div>

                <div class="dc-card-body">
                    <h2 class="dc-card-name">{{ $country->name }}</h2>
                    <span class="dc-card-flourish"></span>

                    <div class="dc-card-meta">
                        <span class="dc-meta-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" class="dc-meta-icon">
                                <path d="M12 21s-7-5.686-7-11a7 7 0 0 1 14 0c0 5.314-7 11-7 11z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                            {{ $country->cities_count }} destination{{ $country->cities_count > 1 ? 's' : '' }} à explorer
                        </span>

                        @if($country->currency)
                        <span class="dc-meta-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" class="dc-meta-icon">
                                <circle cx="12" cy="12" r="8.5"/>
                                <path d="M9.5 15c0 1 1 1.8 2.5 1.8s2.5-.7 2.5-1.7c0-2.5-5-1.2-5-3.6 0-1 1-1.7 2.5-1.7s2.4.6 2.5 1.6"/>
                                <path d="M12 7.3v1.1M12 15.6v1.1"/>
                            </svg>
                            {{ $country->currency }}
                        </span>
                        @endif

                        @if($country->phone_code)
                        <span class="dc-meta-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" class="dc-meta-icon">
                                <path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.3 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C11.6 21 3 12.4 3 2.7c0-.6.4-1 1-1H7.3c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1L6.6 10.8z"/>
                            </svg>
                            {{ Str::startsWith($country->phone_code, '+') ? $country->phone_code : '+'.ltrim($country->phone_code, '+') }}
                        </span>
                        @endif
                    </div>

                    <span class="dc-card-cta">
                        Explorer
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 4l4 4-4 4"/>
                        </svg>
                    </span>
                </div>

            </a>
            @endforeach
        </div>
    </div>
</section>

@endsection