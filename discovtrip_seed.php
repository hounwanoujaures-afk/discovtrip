<?php

/**
 * DiscovTrip — Script de seed complet
 * 2 pays (Bénin + Togo), 5 villes/pays, 20+ offres, spotlight, blog
 * Usage SSH Hostinger : php discovtrip_seed.php
 */

require __DIR__ . '/vendor/autoload.php';
$app    = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BlogPost;
use App\Models\City;
use App\Models\Country;
use App\Models\Offer;
use App\Models\Spotlight;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────
// Clé Pexels — remplace par la tienne si besoin
// ─────────────────────────────────────────────
$PEXELS_KEY = 'dO8NfrFhEbHgd5u1TCmeVj9CicXVUim8nAYTypNCrcCZSwwSPtUdiOly';

// ─────────────────────────────────────────────
// Fonction de téléchargement Pexels
// ─────────────────────────────────────────────
function pexelsDownload(string $query, string $relativePath, string $key): ?string
{
    if (Storage::disk('public')->exists($relativePath)) {
        echo "   [cache] {$relativePath}\n";
        return $relativePath;
    }

    $ch = curl_init(
        'https://api.pexels.com/v1/search?query=' . urlencode($query)
        . '&per_page=1&orientation=landscape'
    );
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: ' . $key],
        CURLOPT_TIMEOUT        => 25,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if (!$res) {
        echo "   [err] recherche Pexels échouée pour « {$query} » : {$err}\n";
        return null;
    }

    $data = json_decode($res, true);
    $url  = $data['photos'][0]['src']['large2x'] ?? $data['photos'][0]['src']['large'] ?? null;

    if (!$url) {
        echo "   [err] aucune photo Pexels pour « {$query} »\n";
        return null;
    }

    $ctx     = stream_context_create(['http' => ['timeout' => 25]]);
    $imgData = @file_get_contents($url, false, $ctx);

    if (!$imgData) {
        echo "   [err] téléchargement échoué pour « {$query} »\n";
        return null;
    }

    Storage::disk('public')->put($relativePath, $imgData);
    $ko = number_format(strlen($imgData) / 1024, 0);
    echo "   [ok]  {$relativePath} ({$ko} Ko)\n";

    usleep(400000); // 0,4 s entre chaque appel pour respecter le rate-limit Pexels
    return $relativePath;
}

// ─────────────────────────────────────────────
// 1. PAYS
// ─────────────────────────────────────────────
echo "\n=== 1. Pays ===\n";

$benin = Country::firstOrCreate(['slug' => 'benin'], [
    'name'        => 'Bénin',
    'code'        => 'BJ',
    'continent'   => 'Afrique',
    'currency'    => 'XOF',
    'phone_code'  => '+229',
    'region'      => "Afrique de l'Ouest",
    'hero_tagline' => 'Le berceau du Vaudou et des royaumes du Dahomey',
]);

$togo = Country::firstOrCreate(['slug' => 'togo'], [
    'name'        => 'Togo',
    'code'        => 'TG',
    'continent'   => 'Afrique',
    'currency'    => 'XOF',
    'phone_code'  => '+228',
    'region'      => "Afrique de l'Ouest",
    'hero_tagline' => 'Entre savanes du nord et plages du golfe de Guinée',
]);

echo "Bénin (id={$benin->id}) | Togo (id={$togo->id})\n";

// ─────────────────────────────────────────────
// 2. VILLES — 5 par pays
// ─────────────────────────────────────────────
echo "\n=== 2. Villes ===\n";

$citiesData = [

    // ── BÉNIN (5 villes) ──────────────────────────────────────────────
    [
        'country_id'  => $benin->id,
        'name'        => 'Cotonou',
        'region'      => 'Littoral',
        'category'    => 'urban',
        'lat'         => 6.3703,  'lng' => 2.3912,
        'distance'    => 0,
        'days'        => 2,
        'season'      => 'novembre à mars',
        'rating'      => 4.6,
        'featured'    => true,
        'order'       => 1,
        'description' => "Poumon économique du Bénin, Cotonou bouillonne entre le marché géant de Dantokpa, ses plages de la Route des Pêches et une vie nocturne animée.",
        'highlights'  => [
            ['icon' => 'shopping-bag', 'title' => 'Marché Dantokpa',    'description' => "L'un des plus grands marchés à ciel ouvert d'Afrique de l'Ouest"],
            ['icon' => 'sun',          'title' => 'Route des Pêches',   'description' => "Plages et villages de pêcheurs à quelques minutes du centre"],
            ['icon' => 'music',        'title' => 'Vie nocturne',       'description' => "Bars, concerts afrobeat et restaurants animés"],
        ],
        'landmarks'   => [
            ['emoji' => '⛪',  'name' => 'Cathédrale Notre-Dame', 'description' => "Édifice emblématique du centre-ville"],
            ['emoji' => '🌊',  'name' => 'Plage de Fidjrossè',    'description' => "Plage populaire prisée le week-end"],
            ['emoji' => '🎨',  'name' => 'Village Artistique',    'description' => "Artisanat béninois réuni en un seul lieu"],
        ],
        'howto'       => "Aéroport international Cadjehoun en plein cœur de la ville ; taxis et zémidjans partout.",
        'besttime'    => "Toute l'année ; pic de novembre à mars (saison sèche).",
        'budget'      => "15 000 – 40 000 FCFA/jour",
        'facts'       => [['fact' => "Dantokpa s'étend sur plus de 20 ha et accueille des milliers de vendeurs chaque jour."]],
        'image_query' => 'colorful african market west africa',
    ],
    [
        'country_id'  => $benin->id,
        'name'        => 'Ouidah',
        'region'      => 'Atlantique',
        'category'    => 'cultural',
        'lat'         => 6.3628,  'lng' => 2.0852,
        'distance'    => 42,
        'days'        => 1,
        'season'      => 'novembre à mars',
        'rating'      => 4.8,
        'featured'    => true,
        'order'       => 2,
        'description' => "Capitale spirituelle du Vaudou et lieu de mémoire de la traite négrière, Ouidah offre un voyage historique et spirituel intense.",
        'highlights'  => [
            ['icon' => 'door',      'title' => 'Route des Esclaves', 'description' => "Parcours mémoriel jusqu'à la Porte du Non-Retour"],
            ['icon' => 'moon-star', 'title' => 'Temple des Pythons', 'description' => "Sanctuaire sacré du culte vaudou"],
            ['icon' => 'building',  'title' => 'Fort portugais',     'description' => "Ancien comptoir négrier converti en musée d'histoire"],
        ],
        'landmarks'   => [
            ['emoji' => '🚪', 'name' => 'Porte du Non-Retour',  'description' => "Monument face à l'océan, symbole de la mémoire de l'esclavage"],
            ['emoji' => '🐍', 'name' => 'Temple des Pythons',   'description' => "Sanctuaire vivant abritant des pythons royaux sacrés"],
            ['emoji' => '🏛️', 'name' => 'Musée d\'Histoire',    'description' => "Collections retraçant la traite négrière et le royaume du Dahomey"],
        ],
        'howto'       => "45 min en voiture depuis Cotonou.",
        'besttime'    => "Festival International du Vaudou le 10 janvier.",
        'budget'      => "15 000 – 35 000 FCFA/jour",
        'facts'       => [['fact' => "Ouidah accueille chaque 10 janvier la Fête Nationale du Vaudou, reconnue dans le monde entier."]],
        'image_query' => 'african sacred shrine voodoo ceremony',
    ],
    [
        'country_id'  => $benin->id,
        'name'        => 'Abomey',
        'region'      => 'Zou',
        'category'    => 'historical',
        'lat'         => 7.1828,  'lng' => 1.9911,
        'distance'    => 145,
        'days'        => 1,
        'season'      => 'novembre à mars',
        'rating'      => 4.9,
        'featured'    => true,
        'order'       => 3,
        'description' => "Ancienne capitale du royaume du Dahomey, Abomey abrite les Palais Royaux classés au patrimoine mondial de l'UNESCO depuis 1985.",
        'highlights'  => [
            ['icon' => 'crown',  'title' => 'Palais Royaux UNESCO',   'description' => "Site UNESCO retraçant 300 ans de règne des rois d'Abomey"],
            ['icon' => 'brush',  'title' => 'Bas-reliefs historiques','description' => "Fresques narrant les exploits des douze rois du Dahomey"],
            ['icon' => 'hammer', 'title' => 'Artisans bronziers',     'description' => "Ateliers héritiers d'un savoir-faire royal ancestral"],
        ],
        'landmarks'   => [
            ['emoji' => '👑', 'name' => 'Palais du Roi Ghézo',  'description' => "L'un des palais les mieux conservés du site"],
            ['emoji' => '🎨', 'name' => 'Musée Historique',     'description' => "Trônes, sceptres et objets royaux authentiques"],
            ['emoji' => '🗿', 'name' => 'Autels du Roi Agadja', 'description' => "Temples royaux chargés d'histoire et de symboles"],
        ],
        'howto'       => "2h30 de route depuis Cotonou, axe principal bitumé.",
        'besttime'    => "Toute l'année, matinée recommandée pour la lumière.",
        'budget'      => "12 000 – 30 000 FCFA/jour",
        'facts'       => [['fact' => "Les Palais Royaux d'Abomey sont inscrits à l'UNESCO depuis 1985."]],
        'image_query' => 'historic african palace ruins heritage',
    ],
    [
        'country_id'  => $benin->id,
        'name'        => 'Ganvié',
        'region'      => 'Atlantique',
        'category'    => 'lakeside',
        'lat'         => 6.4667,  'lng' => 2.4167,
        'distance'    => 18,
        'days'        => 1,
        'season'      => 'novembre à mars',
        'rating'      => 4.7,
        'featured'    => true,
        'order'       => 4,
        'description' => "Surnommée la « Venise de l'Afrique », Ganvié est le plus grand village lacustre du continent, bâti entièrement sur pilotis au-dessus du lac Nokoué.",
        'highlights'  => [
            ['icon' => 'anchor', 'title' => 'Balade en pirogue',  'description' => "Découverte du village sur les eaux du lac Nokoué"],
            ['icon' => 'fish',   'title' => 'Marché flottant',   'description' => "Commerce animé directement depuis les pirogues"],
            ['icon' => 'home',   'title' => 'Maisons sur pilotis','description' => "Architecture lacustre unique en Afrique de l'Ouest"],
        ],
        'landmarks'   => [
            ['emoji' => '🛶', 'name' => 'Lac Nokoué',         'description' => "Plan d'eau de 16 000 ha abritant le village"],
            ['emoji' => '🏠', 'name' => 'Maisons sur pilotis','description' => "Habitat traditionnel unique"],
            ['emoji' => '🐦', 'name' => 'Oiseaux du lac',     'description' => "Hérons, martins-pêcheurs et oiseaux migrateurs"],
        ],
        'howto'       => "45 min depuis Cotonou, puis embarcadère en pirogue.",
        'besttime'    => "Tôt le matin pour le marché flottant et les oiseaux.",
        'budget'      => "10 000 – 25 000 FCFA/jour",
        'facts'       => [['fact' => "Fondé au 18e siècle, Ganvié compte aujourd'hui environ 50 000 habitants vivant sur l'eau."]],
        'image_query' => 'stilt village lake africa boat',
    ],
    [
        'country_id'  => $benin->id,
        'name'        => 'Porto-Novo',
        'region'      => 'Ouémé',
        'category'    => 'historical',
        'lat'         => 6.4969,  'lng' => 2.6289,
        'distance'    => 30,
        'days'        => 1,
        'season'      => 'novembre à mars',
        'rating'      => 4.4,
        'featured'    => false,
        'order'       => 5,
        'description' => "Capitale officielle du Bénin, Porto-Novo séduit par son architecture afro-brésilienne, ses musées royaux et son atmosphère paisible au bord de la lagune.",
        'highlights'  => [
            ['icon' => 'building-museum', 'title' => 'Musée Honmè',            'description' => "Ancien palais royal transformé en musée historique"],
            ['icon' => 'palette',         'title' => 'Architecture afro-brésilienne','description' => "Maisons colorées héritées des anciens esclaves affranchis"],
            ['icon' => 'water',           'title' => 'Lagune de Porto-Novo',   'description' => "Promenade paisible autour de la lagune"],
        ],
        'landmarks'   => [
            ['emoji' => '🏛️', 'name' => 'Palais Honmè',    'description' => "Résidence des rois de Porto-Novo"],
            ['emoji' => '🕌',  'name' => 'Grande Mosquée', 'description' => "Mosquée à l'architecture brésilienne unique"],
            ['emoji' => '📚',  'name' => 'Musée Ethnographique','description' => "Collections d'art et traditions du Bénin"],
        ],
        'howto'       => "30 min en voiture depuis Cotonou par la route côtière.",
        'besttime'    => "Idéal en demi-journée, toute l'année.",
        'budget'      => "10 000 – 25 000 FCFA/jour",
        'facts'       => [['fact' => "Porto-Novo doit son nom aux marins portugais qui la comparèrent à Porto, au Portugal."]],
        'image_query' => 'colorful colonial heritage building africa',
    ],

    // ── TOGO (5 villes) ───────────────────────────────────────────────
    [
        'country_id'  => $togo->id,
        'name'        => 'Lomé',
        'region'      => 'Maritime',
        'category'    => 'urban',
        'lat'         => 6.1319,  'lng' => 1.2228,
        'distance'    => 130,
        'days'        => 2,
        'season'      => 'novembre à mars',
        'rating'      => 4.5,
        'featured'    => true,
        'order'       => 1,
        'description' => "Capitale animée du Togo, Lomé mélange grands marchés traditionnels, plages urbaines et une scène culturelle en plein essor, le tout face à l'Atlantique.",
        'highlights'  => [
            ['icon' => 'shopping-cart', 'title' => 'Grand Marché',         'description' => "Textiles, artisanat et célèbres Nana Benz"],
            ['icon' => 'flask',         'title' => 'Marché des féticheurs','description' => "Marché vaudou d'Akodessewa, unique en son genre"],
            ['icon' => 'beach',         'title' => 'Plage de Lomé',        'description' => "Front de mer animé en soirée"],
        ],
        'landmarks'   => [
            ['emoji' => '🗼',  'name' => 'Monument de l\'Indépendance','description' => "Symbole de la souveraineté togolaise"],
            ['emoji' => '🏖️', 'name' => 'Plage de Lomé',              'description' => "Front de mer animé en soirée"],
            ['emoji' => '🎭', 'name' => 'Marché Akodessewa',          'description' => "Marché vaudou le plus grand du monde"],
        ],
        'howto'       => "Aéroport international Gnassingbé Eyadéma, à 20 min du centre.",
        'besttime'    => "Toute l'année ; novembre à mars pour la saison sèche.",
        'budget'      => "15 000 – 40 000 FCFA/jour",
        'facts'       => [['fact' => "Le marché des féticheurs d'Akodessewa est considéré comme le plus grand marché vaudou du monde."]],
        'image_query' => 'west africa capital city coastal street market',
    ],
    [
        'country_id'  => $togo->id,
        'name'        => 'Kpalimé',
        'region'      => 'Plateaux',
        'category'    => 'nature',
        'lat'         => 6.9,     'lng' => 0.6333,
        'distance'    => 220,
        'days'        => 2,
        'season'      => 'juillet à septembre',
        'rating'      => 4.7,
        'featured'    => false,
        'order'       => 2,
        'description' => "Nichée dans les montagnes verdoyantes de l'ouest togolais, Kpalimé est réputée pour ses plantations de café, ses cascades spectaculaires et sa biodiversité exceptionnelle.",
        'highlights'  => [
            ['icon' => 'coffee',   'title' => 'Plantations de café et cacao','description' => "Visite guidée des exploitations locales"],
            ['icon' => 'droplet',  'title' => 'Chutes de Kpimé',            'description' => "Cascade spectaculaire au cœur de la forêt"],
            ['icon' => 'leaf',     'title' => 'Biodiversité',               'description' => "Papillons endémiques et forêts primaires"],
        ],
        'landmarks'   => [
            ['emoji' => '⛰️', 'name' => 'Mont Klouto',      'description' => "Randonnée avec panorama sur toute la région"],
            ['emoji' => '💧', 'name' => 'Chutes de Kpimé', 'description' => "Cascade de 40 m au cœur de la forêt"],
            ['emoji' => '🦋', 'name' => 'Forêt d\'Assevé', 'description' => "Réserve naturelle riche en papillons endémiques"],
        ],
        'howto'       => "3h de route depuis Lomé, région montagneuse.",
        'besttime'    => "Juin à septembre pour des cascades en plein débit.",
        'budget'      => "12 000 – 30 000 FCFA/jour",
        'facts'       => [['fact' => "Kpalimé est surnommée la capitale togolaise du papillon pour sa biodiversité."]],
        'image_query' => 'tropical mountains waterfall green forest africa',
    ],
    [
        'country_id'  => $togo->id,
        'name'        => 'Kara',
        'region'      => 'Kara',
        'category'    => 'cultural',
        'lat'         => 9.5511,  'lng' => 1.1861,
        'distance'    => 500,
        'days'        => 2,
        'season'      => 'novembre à février',
        'rating'      => 4.8,
        'featured'    => true,
        'order'       => 3,
        'description' => "Porte d'entrée du nord togolais, Kara est le point de départ idéal pour explorer la vallée du Koutammakou, inscrite au patrimoine mondial de l'UNESCO.",
        'highlights'  => [
            ['icon' => 'home',  'title' => 'Koutammakou UNESCO', 'description' => "Habitat traditionnel Tamberma, patrimoine mondial"],
            ['icon' => 'sword', 'title' => 'Lutte Kabyè',       'description' => "Spectacle de lutte ancestrale de la région"],
            ['icon' => 'tree',  'title' => 'Savane de l\'Atakora','description' => "Paysages sauvages et faune de brousse"],
        ],
        'landmarks'   => [
            ['emoji' => '🏯', 'name' => 'Tata Somba',         'description' => "Maisons-forteresses en terre typiques"],
            ['emoji' => '🌄', 'name' => 'Chaîne de l\'Atakora','description' => "Massif montagneux traversant la région"],
            ['emoji' => '🎪', 'name' => 'Festival Evala',     'description' => "Cérémonies initiatiques Kabyè en juillet"],
        ],
        'howto'       => "6h de route depuis Lomé ou vols intérieurs réguliers.",
        'besttime'    => "Novembre à février (saison sèche et fraîche).",
        'budget'      => "15 000 – 35 000 FCFA/jour",
        'facts'       => [['fact' => "Le Koutammakou est inscrit au patrimoine mondial de l'UNESCO depuis 2004."]],
        'image_query' => 'traditional mud castle village africa north',
    ],
    [
        'country_id'  => $togo->id,
        'name'        => 'Aného',
        'region'      => 'Maritime',
        'category'    => 'historical',
        'lat'         => 6.2333,  'lng' => 1.6,
        'distance'    => 45,
        'days'        => 1,
        'season'      => 'novembre à mars',
        'rating'      => 4.3,
        'featured'    => false,
        'order'       => 4,
        'description' => "Ancienne première capitale coloniale du Togo, Aného conserve un charme historique unique entre lagune et océan Atlantique, avec son patrimoine architectural allemand.",
        'highlights'  => [
            ['icon' => 'building',  'title' => 'Architecture coloniale','description' => "Bâtiments allemands et français bien conservés"],
            ['icon' => 'sailboat',  'title' => 'Lac Togo',             'description' => "Excursions en pirogue sur une lagune paisible"],
            ['icon' => 'church',    'title' => 'Cathédrale historique','description' => "Édifice religieux de l'époque coloniale"],
        ],
        'landmarks'   => [
            ['emoji' => '⛪',  'name' => 'Cathédrale d\'Aného', 'description' => "Édifice religieux historique de la ville"],
            ['emoji' => '🌊',  'name' => 'Lagune de Togoville', 'description' => "Berceau spirituel du vaudou togolais"],
            ['emoji' => '🏚️', 'name' => 'Maisons coloniales',  'description' => "Témoins de l'époque allemande puis française"],
        ],
        'howto'       => "45 min depuis Lomé par la route côtière.",
        'besttime'    => "Toute l'année, idéal en excursion à la journée.",
        'budget'      => "10 000 – 25 000 FCFA/jour",
        'facts'       => [['fact' => "Aného fut la première capitale du Togo sous administration allemande."]],
        'image_query' => 'colonial coastal town lagoon africa',
    ],
    [
        'country_id'  => $togo->id,
        'name'        => 'Sokodé',
        'region'      => 'Centrale',
        'category'    => 'cultural',
        'lat'         => 8.9833,  'lng' => 1.1333,
        'distance'    => 380,
        'days'        => 1,
        'season'      => 'novembre à février',
        'rating'      => 4.4,
        'featured'    => false,
        'order'       => 5,
        'description' => "Deuxième plus grande ville du Togo, Sokodé est le cœur culturel du peuple Kabyè, connue pour ses festivals traditionnels et son marché artisanal animé.",
        'highlights'  => [
            ['icon' => 'mask',             'title' => 'Festival Evala',    'description' => "Cérémonies de passage à l'âge adulte"],
            ['icon' => 'building-mosque',  'title' => 'Grande Mosquée',    'description' => "Cœur spirituel de la ville, plus d'un siècle d'histoire"],
            ['icon' => 'scissors',         'title' => 'Artisanat Kabyè',   'description' => "Tissage traditionnel et poterie locale réputée"],
        ],
        'landmarks'   => [
            ['emoji' => '🕌', 'name' => 'Mosquée centrale',  'description' => "Un des plus anciens lieux de culte musulman du Togo"],
            ['emoji' => '🎭', 'name' => 'Marché artisanal', 'description' => "Tissage traditionnel et poterie locale"],
            ['emoji' => '🥊', 'name' => 'Arènes de lutte',  'description' => "Lieu de pratique de la lutte traditionnelle Kabyè"],
        ],
        'howto'       => "4h30 de route depuis Lomé, sur l'axe national N1.",
        'besttime'    => "Juillet pour le festival Evala.",
        'budget'      => "10 000 – 25 000 FCFA/jour",
        'facts'       => [['fact' => "Sokodé est historiquement un carrefour commercial entre le nord et le sud du Togo."]],
        'image_query' => 'african traditional festival mask ceremony',
    ],
];

$cities = [];
foreach ($citiesData as $d) {
    $slug      = Str::slug($d['name']);
    $imgPath   = "cities/covers/{$slug}.jpg";
    pexelsDownload($d['image_query'], $imgPath, $PEXELS_KEY);

    $city = City::firstOrCreate(['slug' => $slug], [
        'country_id'            => $d['country_id'],
        'name'                  => $d['name'],
        'slug'                  => $slug,
        'latitude'              => $d['lat'],
        'longitude'             => $d['lng'],
        'cover_image'           => $imgPath,
        'description'           => $d['description'],
        'region'                => $d['region'],
        'distance_from_cotonou' => $d['distance'],
        'duration_days'         => $d['days'],
        'best_season'           => $d['season'],
        'category'              => $d['category'],
        'average_rating'        => $d['rating'],
        'is_featured'           => $d['featured'],
        'is_active'             => true,
        'featured_order'        => $d['order'],
        'highlights'            => $d['highlights'],
        'landmarks'             => $d['landmarks'],
        'how_to_get_there'      => $d['howto'],
        'best_time_detail'      => $d['besttime'],
        'budget_range'          => $d['budget'],
        'fun_facts'             => $d['facts'],
    ]);

    $cities[$d['name']] = $city;
    echo "  ✓ {$city->name} (id={$city->id})\n";
}

// ─────────────────────────────────────────────
// 3. OFFRES — minimum 20 (2–3 par ville)
// ─────────────────────────────────────────────
echo "\n=== 3. Offres ===\n";

$offersData = [

    // ── COTONOU (3) ──────────────────────────────────────────────────────
    [
        'city'     => 'Cotonou',
        'title'    => 'Immersion au Marché de Dantokpa',
        'category' => 'cultural',
        'price'    => 20000,
        'duration' => 180,
        'diff'     => 'easy',
        'desc'     => "Plongez dans l'effervescence du plus grand marché d'Afrique de l'Ouest, guidé par un habitant qui vous fera découvrir épices, tissus et spécialités locales.",
        'query'    => 'african market vendor colorful spices',
        'featured' => false,
    ],
    [
        'city'     => 'Cotonou',
        'title'    => 'Route des Pêches : plage et traditions',
        'category' => 'nature',
        'price'    => 25000,
        'duration' => 300,
        'diff'     => 'easy',
        'desc'     => "Détente sur les plages atlantiques, rencontre avec les pêcheurs locaux et dégustation de poisson braisé face à l'océan.",
        'query'    => 'african beach fishermen traditional boat',
        'featured' => false,
    ],
    [
        'city'     => 'Cotonou',
        'title'    => 'Cotonou by Night : gastronomie et afrobeat',
        'category' => 'gastronomy',
        'price'    => 30000,
        'duration' => 240,
        'diff'     => 'easy',
        'desc'     => "Une soirée pour découvrir la cuisine béninoise dans ses meilleurs restaurants, suivie d'un concert de musique afrobeat dans un bar local.",
        'query'    => 'african restaurant night food music',
        'featured' => false,
    ],

    // ── OUIDAH (3) ───────────────────────────────────────────────────────
    [
        'city'     => 'Ouidah',
        'title'    => 'Route des Esclaves et Porte du Non-Retour',
        'category' => 'cultural',
        'price'    => 22000,
        'duration' => 180,
        'diff'     => 'easy',
        'desc'     => "Parcours mémoriel de 4 km retraçant le chemin emprunté par des milliers de captifs, jusqu'au monument face à l'océan Atlantique.",
        'query'    => 'african memorial monument ocean coast',
        'featured' => true,
    ],
    [
        'city'     => 'Ouidah',
        'title'    => 'Temple des Pythons et spiritualité Vaudou',
        'category' => 'cultural',
        'price'    => 20000,
        'duration' => 90,
        'diff'     => 'easy',
        'desc'     => "Découverte du sanctuaire sacré abritant des pythons royaux et initiation aux fondements de la spiritualité vaudou béninoise.",
        'query'    => 'sacred temple ritual snake africa',
        'featured' => false,
    ],
    [
        'city'     => 'Ouidah',
        'title'    => 'Fort portugais et Musée d\'Histoire',
        'category' => 'cultural',
        'price'    => 28000,
        'duration' => 270,
        'diff'     => 'easy',
        'desc'     => "Visite approfondie du Musée d'Histoire de Ouidah installé dans l'ancien fort portugais, avec un guide spécialisé en histoire coloniale.",
        'query'    => 'old fort museum colonial history',
        'featured' => false,
    ],

    // ── ABOMEY (3) ───────────────────────────────────────────────────────
    [
        'city'     => 'Abomey',
        'title'    => 'Palais Royaux d\'Abomey — Site UNESCO',
        'category' => 'cultural',
        'price'    => 25000,
        'duration' => 180,
        'diff'     => 'easy',
        'desc'     => "Visite guidée des palais royaux classés à l'UNESCO, à la découverte de 300 ans d'histoire du puissant royaume du Dahomey.",
        'query'    => 'african royal palace historic unesco heritage',
        'featured' => true,
    ],
    [
        'city'     => 'Abomey',
        'title'    => 'Ateliers d\'artisans : bronziers et brodeurs royaux',
        'category' => 'cultural',
        'price'    => 18000,
        'duration' => 150,
        'diff'     => 'easy',
        'desc'     => "Rencontre avec les artisans bronziers et brodeurs d'Abomey, héritiers d'un savoir-faire royal transmis de génération en génération.",
        'query'    => 'african artisan bronze craft workshop',
        'featured' => false,
    ],
    [
        'city'     => 'Abomey',
        'title'    => 'Légendes royales du Dahomey — journée complète',
        'category' => 'cultural',
        'price'    => 32000,
        'duration' => 360,
        'diff'     => 'moderate',
        'desc'     => "Une journée complète pour explorer l'ensemble du site historique d'Abomey, accompagné d'un conteur local partageant les légendes des douze rois.",
        'query'    => 'african historic ruins storytelling heritage',
        'featured' => false,
    ],

    // ── GANVIÉ (2) ───────────────────────────────────────────────────────
    [
        'city'     => 'Ganvié',
        'title'    => 'Pirogue au village lacustre de Ganvié',
        'category' => 'nature',
        'price'    => 25000,
        'duration' => 150,
        'diff'     => 'easy',
        'desc'     => "Traversée en pirogue du lac Nokoué à la découverte du plus grand village sur pilotis d'Afrique, ses maisons flottantes et son marché.",
        'query'    => 'canoe lake stilt village africa',
        'featured' => true,
    ],
    [
        'city'     => 'Ganvié',
        'title'    => 'Lever de soleil avec les pêcheurs du lac',
        'category' => 'nature',
        'price'    => 20000,
        'duration' => 120,
        'diff'     => 'easy',
        'desc'     => "Départ à l'aube pour vivre la pêche traditionnelle aux côtés des habitants du lac, dans une ambiance authentique et reposante.",
        'query'    => 'sunrise fishing lake traditional africa',
        'featured' => false,
    ],

    // ── PORTO-NOVO (2) ───────────────────────────────────────────────────
    [
        'city'     => 'Porto-Novo',
        'title'    => 'Palais Honmè et musées royaux',
        'category' => 'cultural',
        'price'    => 18000,
        'duration' => 150,
        'diff'     => 'easy',
        'desc'     => "Visite guidée du musée Honmè, ancien palais royal, pour comprendre l'histoire de la royauté de Porto-Novo et son architecture unique.",
        'query'    => 'african museum artifacts royal palace',
        'featured' => false,
    ],
    [
        'city'     => 'Porto-Novo',
        'title'    => 'Balade afro-brésilienne dans Porto-Novo',
        'category' => 'cultural',
        'price'    => 15000,
        'duration' => 120,
        'diff'     => 'easy',
        'desc'     => "Promenade dans les ruelles colorées de Porto-Novo à la découverte des maisons construites par les anciens esclaves affranchis revenus du Brésil.",
        'query'    => 'colorful colonial houses heritage street africa',
        'featured' => false,
    ],

    // ── LOMÉ (3) ─────────────────────────────────────────────────────────
    [
        'city'     => 'Lomé',
        'title'    => 'Grand Marché et Marché des Féticheurs',
        'category' => 'cultural',
        'price'    => 22000,
        'duration' => 180,
        'diff'     => 'easy',
        'desc'     => "Découverte du Grand Marché de Lomé puis du célèbre marché vaudou d'Akodessewa, considéré comme le plus grand marché de fétiches au monde.",
        'query'    => 'voodoo fetish market africa spiritual',
        'featured' => true,
    ],
    [
        'city'     => 'Lomé',
        'title'    => 'Front de mer et Monument de l\'Indépendance',
        'category' => 'urban',
        'price'    => 18000,
        'duration' => 150,
        'diff'     => 'easy',
        'desc'     => "Tour de ville le long du front de mer, avec arrêt au Monument de l'Indépendance et détente sur la plage animée de Lomé.",
        'query'    => 'west africa city beach waterfront monument',
        'featured' => false,
    ],
    [
        'city'     => 'Lomé',
        'title'    => 'Street art et Nana Benz : Lomé créative',
        'category' => 'urban',
        'price'    => 20000,
        'duration' => 180,
        'diff'     => 'easy',
        'desc'     => "Circuit dans les quartiers créatifs de Lomé, à la rencontre des fresques murales et des légendaires commerçantes textiles Nana Benz.",
        'query'    => 'street art mural colorful africa city',
        'featured' => false,
    ],

    // ── KPALIMÉ (2) ──────────────────────────────────────────────────────
    [
        'city'     => 'Kpalimé',
        'title'    => 'Randonnée Mont Klouto et plantations de café',
        'category' => 'nature',
        'price'    => 30000,
        'duration' => 300,
        'diff'     => 'moderate',
        'desc'     => "Trek à travers les plantations de café et de cacao jusqu'au sommet du Mont Klouto, avec un panorama exceptionnel sur les montagnes togolaises.",
        'query'    => 'coffee plantation mountain trekking africa',
        'featured' => false,
    ],
    [
        'city'     => 'Kpalimé',
        'title'    => 'Cascade de Kpimé et forêt aux papillons',
        'category' => 'nature',
        'price'    => 25000,
        'duration' => 240,
        'diff'     => 'moderate',
        'desc'     => "Randonnée jusqu'à la cascade de Kpimé puis exploration de la forêt d'Assevé, sanctuaire d'une biodiversité exceptionnelle en papillons.",
        'query'    => 'waterfall tropical forest butterfly africa',
        'featured' => false,
    ],

    // ── KARA (3) ─────────────────────────────────────────────────────────
    [
        'city'     => 'Kara',
        'title'    => 'Koutammakou et Tata Somba — Site UNESCO',
        'category' => 'cultural',
        'price'    => 35000,
        'duration' => 360,
        'diff'     => 'moderate',
        'desc'     => "Immersion dans la vallée du Koutammakou pour découvrir les célèbres maisons-forteresses Tamberma, inscrites au patrimoine mondial de l'UNESCO en 2004.",
        'query'    => 'traditional fortress mud house village togo',
        'featured' => true,
    ],
    [
        'city'     => 'Kara',
        'title'    => 'Lutte Kabyè et rites ancestraux',
        'category' => 'cultural',
        'price'    => 22000,
        'duration' => 150,
        'diff'     => 'easy',
        'desc'     => "Assistez à une démonstration de lutte traditionnelle Kabyè, sport ancestral chargé de symboles et de rituels de passage à l'âge adulte.",
        'query'    => 'traditional wrestling africa martial arts',
        'featured' => false,
    ],
    [
        'city'     => 'Kara',
        'title'    => 'Safari 4×4 dans la savane de l\'Atakora',
        'category' => 'adventure',
        'price'    => 40000,
        'duration' => 360,
        'diff'     => 'challenging',
        'desc'     => "Excursion en 4×4 à travers les paysages sauvages de la chaîne de l'Atakora, entre savane, collines et faune locale.",
        'query'    => 'savanna 4x4 safari africa landscape',
        'featured' => false,
    ],

    // ── ANÉHO (2) ────────────────────────────────────────────────────────
    [
        'city'     => 'Aného',
        'title'    => 'Aného colonial : histoire et bord de mer',
        'category' => 'cultural',
        'price'    => 16000,
        'duration' => 150,
        'diff'     => 'easy',
        'desc'     => "Découverte du patrimoine colonial allemand et français d'Aného, ancienne première capitale du Togo, suivie d'une pause détente sur la plage.",
        'query'    => 'colonial building history coastal town',
        'featured' => false,
    ],
    [
        'city'     => 'Aného',
        'title'    => 'Pirogue sur le Lac Togo jusqu\'à Togoville',
        'category' => 'nature',
        'price'    => 20000,
        'duration' => 180,
        'diff'     => 'easy',
        'desc'     => "Navigation paisible sur le lac Togo jusqu'à Togoville, berceau spirituel du vaudou togolais, entre lagune et villages traditionnels.",
        'query'    => 'lagoon canoe traditional village lake',
        'featured' => false,
    ],

    // ── SOKODÉ (2) ───────────────────────────────────────────────────────
    [
        'city'     => 'Sokodé',
        'title'    => 'Culture Kabyè et grande mosquée de Sokodé',
        'category' => 'cultural',
        'price'    => 18000,
        'duration' => 180,
        'diff'     => 'easy',
        'desc'     => "Visite du cœur culturel du peuple Kabyè, de sa grande mosquée historique et de son marché artisanal réputé pour le tissage traditionnel.",
        'query'    => 'african mosque traditional market weaving',
        'featured' => false,
    ],
    [
        'city'     => 'Sokodé',
        'title'    => 'Masques et rites du Centre-Togo',
        'category' => 'cultural',
        'price'    => 24000,
        'duration' => 210,
        'diff'     => 'easy',
        'desc'     => "Rencontre avec les artisans de masques traditionnels et découverte des rites ancestraux qui rythment la vie culturelle de Sokodé.",
        'query'    => 'african mask ceremony traditional craft',
        'featured' => false,
    ],
];

// Offres dont les titres seront marquées featured
$featuredTitles = [
    'Palais Royaux d\'Abomey — Site UNESCO',
    'Pirogue au village lacustre de Ganvié',
    'Route des Esclaves et Porte du Non-Retour',
    'Koutammakou et Tata Somba — Site UNESCO',
    'Grand Marché et Marché des Féticheurs',
];

$adminUser = User::where('role', 'super_admin')->orWhere('role', 'admin')->first();
$offersCreated = [];
$sortOrder = 1;

foreach ($offersData as $d) {
    $city = $cities[$d['city']] ?? null;
    if (!$city) {
        echo "  [warn] Ville introuvable : {$d['city']}\n";
        continue;
    }

    $slug      = Str::slug($d['title']);
    $coverPath = "offers/covers/{$slug}.jpg";
    pexelsDownload($d['query'], $coverPath, $PEXELS_KEY);

    // Galerie 2 images supplémentaires
    $gallery = [];
    foreach ([' close up', ' wide landscape'] as $suffix) {
        $gPath = "offers/gallery/{$slug}" . Str::slug($suffix) . ".jpg";
        if (pexelsDownload($d['query'] . $suffix, $gPath, $PEXELS_KEY)) {
            $gallery[] = $gPath;
        }
    }

    $offer = Offer::firstOrCreate(['slug' => $slug], [
        'title'              => $d['title'],
        'slug'               => $slug,
        'short_description'  => Str::limit($d['desc'], 120),
        'description'        => $d['desc'],
        'long_description'   => '<p>' . $d['desc'] . '</p>',
        'category'           => $d['category'],
        'city_id'            => $city->id,
        'user_id'            => $adminUser?->id,
        'guide_type'         => 'agency',
        'base_price'         => $d['price'],
        'payment_mode'       => 'both',
        'currency'           => 'XOF',
        'duration_minutes'   => $d['duration'],
        'min_participants'   => 1,
        'max_participants'   => 12,
        'min_age'            => 0,
        'difficulty_level'   => $d['diff'],
        'languages'          => ['Français', 'Anglais'],
        'meeting_point'      => "Point de rendez-vous communiqué après réservation à {$city->name}",
        'included_items'     => [
            'Guide local francophone',
            'Transport sur place',
            "Frais d'entrée sur les sites visités",
        ],
        'excluded_items'     => [
            'Repas non mentionnés',
            'Pourboires',
            'Transport depuis votre hébergement',
        ],
        'faq'                => [[
            'q' => 'Faut-il réserver à l\'avance ?',
            'r' => 'Oui, au moins 24 h avant pour garantir la disponibilité du guide.',
        ]],
        'cover_image'        => $coverPath,
        'gallery'            => $gallery,
        'status'             => 'published',
        'published_at'       => now()->subDays(random_int(1, 30)),
        'is_featured'        => in_array($d['title'], $featuredTitles, true),
        'is_instant_booking' => (bool) random_int(0, 1),
        'available_spots'    => random_int(5, 20),
        'views_count'        => random_int(20, 500),
        'sort_order'         => $sortOrder++,
        'average_rating'     => round(random_int(40, 50) / 10, 1),
    ]);

    $offersCreated[] = $offer;
    echo "  ✓ {$offer->title} (id={$offer->id})\n";
}

// ─────────────────────────────────────────────
// 4. SPOTLIGHT
// ─────────────────────────────────────────────
echo "\n=== 4. Spotlight ===\n";

$spotOffer = collect($offersCreated)->firstWhere('title', 'Palais Royaux d\'Abomey — Site UNESCO');
$spotImg   = 'spotlights/abomey-dahomey-sunset.jpg';
pexelsDownload('african royal heritage palace sunset golden', $spotImg, $PEXELS_KEY);

Spotlight::firstOrCreate(['sort_order' => 1], [
    'offer_id'      => $spotOffer?->id,
    'title'         => 'Voyagez au cœur du royaume du Dahomey',
    'subtitle'      => 'Bénin & Togo — expériences authentiques et inoubliables',
    'description'   => "Palais royaux UNESCO, villages lacustres, temples vaudou et maisons-forteresses Tamberma vous attendent au cœur de l'Afrique de l'Ouest.",
    'image'         => $spotImg,
    'badge_text'    => 'Nouveau',
    'badge_icon'    => 'sparkles',
    'highlight_word'=> 'Dahomey',
    'stat1_value'   => '25+',
    'stat1_label'   => 'Expériences',
    'stat2_value'   => '10',
    'stat2_label'   => 'Villes',
    'stat3_value'   => '4.8',
    'stat3_label'   => 'Note moy.',
    'cta1_label'    => 'Réserver maintenant',
    'cta1_url'      => null,
    'cta2_label'    => 'Voir les destinations',
    'cta2_url'      => '/destinations',
    'is_active'     => true,
    'starts_at'     => null,
    'ends_at'       => null,
    'sort_order'    => 1,
]);
echo "  ✓ Spotlight créé\n";

// ─────────────────────────────────────────────
// 5. ARTICLE DE BLOG
// ─────────────────────────────────────────────
echo "\n=== 5. Article de blog ===\n";

$blogImg = 'blog/covers/benin-togo-2026.jpg';
pexelsDownload('africa travel adventure cultural heritage landscape', $blogImg, $PEXELS_KEY);

BlogPost::firstOrCreate(
    ['slug' => '5-raisons-decouvrir-benin-togo-2026'],
    [
        'title'            => '5 raisons de découvrir le Bénin et le Togo en 2026',
        'slug'             => '5-raisons-decouvrir-benin-togo-2026',
        'excerpt'          => "Entre patrimoine UNESCO, villages lacustres et traditions vaudou, le Bénin et le Togo comptent parmi les destinations les plus authentiques d'Afrique de l'Ouest.",
        'content'          =>
            "<p>L'Afrique de l'Ouest recèle des trésors encore méconnus. Le Bénin et le Togo, voisins partageant une riche histoire commune, offrent une diversité d'expériences rares.</p>"
            . "<h2>1. Un patrimoine historique classé à l'UNESCO</h2>"
            . "<p>Les Palais Royaux d'Abomey retracent trois siècles de règne du royaume du Dahomey. Plus au nord, la vallée du Koutammakou au Togo dévoile les célèbres maisons-forteresses Tamberma.</p>"
            . "<h2>2. Des paysages à couper le souffle</h2>"
            . "<p>Du village lacustre de Ganvié aux montagnes verdoyantes de Kpalimé et ses cascades, la région regorge de panoramas uniques.</p>"
            . "<h2>3. Une spiritualité vivante</h2>"
            . "<p>Berceau du culte vaudou, Ouidah et sa Route des Esclaves offrent un voyage mémoriel intense. Le marché des féticheurs de Lomé reste une expérience culturelle unique au monde.</p>"
            . "<h2>4. Une gastronomie généreuse</h2>"
            . "<p>Poisson braisé sur la Route des Pêches, plats mijotés béninois et spécialités togolaises à base de manioc et d'igname raviront les amateurs de découvertes culinaires.</p>"
            . "<h2>5. Un accueil authentique et responsable</h2>"
            . "<p>Guides locaux passionnés, artisans transmettant un savoir-faire ancestral et communautés lacustres accueillantes : chaque expérience DiscovTrip est conçue pour un tourisme responsable et humain.</p>"
            . "<p>Prêt à explorer ces deux joyaux d'Afrique de l'Ouest ? Découvrez toutes nos expériences sur DiscovTrip.</p>",
        'cover_image'      => $blogImg,
        'category'         => 'destinations',
        'tags'             => ['Bénin', 'Togo', 'UNESCO', 'Culture', 'Voyage', 'Vaudou', 'Patrimoine'],
        'author_id'        => $adminUser?->id,
        'status'           => 'published',
        'published_at'     => now()->subDays(2),
        'meta_title'       => '5 raisons de découvrir le Bénin et le Togo en 2026 | DiscovTrip',
        'meta_description' => "Patrimoine UNESCO, villages lacustres, culture vaudou : découvrez pourquoi le Bénin et le Togo sont les destinations montantes d'Afrique de l'Ouest.",
    ]
);
echo "  ✓ Article de blog créé\n";

// ─────────────────────────────────────────────
// RÉSUMÉ
// ─────────────────────────────────────────────
$nPays     = Country::count();
$nVilles   = City::count();
$nOffres   = Offer::count();
$nSpot     = Spotlight::count();
$nBlog     = BlogPost::count();

echo <<<SUMMARY

╔══════════════════════════════════════════════╗
║             SEED TERMINÉ ✓                  ║
╠══════════════════════════════════════════════╣
║  Pays          : {$nPays}                           ║
║  Villes        : {$nVilles}                          ║
║  Offres        : {$nOffres}                          ║
║  Spotlights    : {$nSpot}                            ║
║  Articles blog : {$nBlog}                            ║
╚══════════════════════════════════════════════╝

SUMMARY;
