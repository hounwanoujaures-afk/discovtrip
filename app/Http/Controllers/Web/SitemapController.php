<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Offer;
use App\Models\BlogPost;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Génère et retourne le sitemap XML principal.
     * Mis en cache 12h pour ne pas surcharger la DB.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap_xml', 43200, function () {
            return $this->buildXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function buildXml(): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $now     = now()->toAtomString();

        $urls = [];

        // ── Pages statiques ─────────────────────────────────
        $staticPages = [
            ['loc' => '/',                      'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/destinations',          'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/offers',                'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => '/about',                 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/contact',               'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/faq',                   'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/conditions-utilisation','priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/confidentialite',       'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/annulation-gratuite',   'priority' => '0.5', 'changefreq' => 'yearly'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc'        => $baseUrl . $page['loc'],
                'lastmod'    => $now,
                'changefreq' => $page['changefreq'],
                'priority'   => $page['priority'],
            ];
        }

        // ── Destinations (villes actives) ────────────────────
        $cities = City::where('is_active', true)
            ->whereNotNull('slug')
            ->select(['slug', 'updated_at'])
            ->orderBy('name')
            ->get();

        foreach ($cities as $city) {
            $urls[] = [
                'loc'        => $baseUrl . '/destinations/' . $city->slug,
                'lastmod'    => $city->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ];
        }

        // ── Offres publiées ──────────────────────────────────
        $offers = Offer::where('status', 'published')
            ->whereNull('deleted_at')
            ->whereNotNull('slug')
            ->select(['slug', 'updated_at'])
            ->orderByDesc('published_at')
            ->get();

        foreach ($offers as $offer) {
            $urls[] = [
                'loc'        => $baseUrl . '/offers/' . $offer->slug,
                'lastmod'    => $offer->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority'   => '0.9',
            ];
        }

        // ── Construction XML ─────────────────────────────────
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        return $xml;
    }
}