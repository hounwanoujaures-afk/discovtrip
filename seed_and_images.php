<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Country;
use App\Models\City;
use App\Models\Offer;
use App\Models\Spotlight;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

$pexelsKey = 'dO8NfrFhEbHgd5u1TCmeVj9CicXVUim8nAYTypNCrcCZSwwSPtUdiOly';

function pexelsDownload(string $query, string $relativePath, string $pexelsKey): ?string
{
    if (Storage::disk('public')->exists($relativePath)) {
        echo "  -> deja present : {$relativePath}\n";
        return $relativePath;
    }

    $ch = curl_init('https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=1&orientation=landscape');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $pexelsKey]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if (!$res) {
        echo "  !! recherche echouee pour \"{$query}\" : {$err}\n";
        return null;
    }

    $data = json_decode($res, true);
    $url = $data['photos'][0]['src']['large'] ?? null;

    if (!$url) {
        echo "  !! aucune image trouvee pour \"{$query}\"\n";
        return null;
    }

    $ctx = stream_context_create(['http' => ['timeout' => 20]]);
    $imgData = @file_get_contents($url, false, $ctx);

    if (!$imgData) {
        echo "  !! telechargement echoue pour \"{$query}\"\n";
        return null;
    }

    Storage::disk('public')->put($relativePath, $imgData);
    echo "  -> {$relativePath} (" . number_format(strlen($imgData) / 1024, 0) . " Ko)\n";
    usleep(350000);

    return $relativePath;
}

echo "=== 1. Creation des pays ===\n";

$benin = Country::create([
    'name' => 'Bénin',
    'slug' => 'benin',
    'code' => 'BJ',
    'continent' => 'Afrique',
    'currency' => 'XOF',
    'phone_code' => '+229',
    'region' => "Afrique de l'Ouest",
    'hero_tagline' => 'Le berceau du Vaudou et des royaumes du Dahomey',
]);

$togo = Country::create([
    'name' => 'Togo',
    'slug' => 'togo',
    'code' => 'TG',
    'continent' => 'Afrique',
    'currency' => 'XOF',
    'phone_code' => '+228',
    'region' => "Afrique de l'Ouest",
    'hero_tagline' => 'Entre savanes du nord et plages du golfe de Guinée',
]);

echo "Benin (id={$benin->id}) et Togo (id={$togo->id}) crees.\n\n";

echo "=== 2. Creation des villes ===\n";

$citiesData = [

    // ───────── BÉNIN ─────────
    [
        'country_id' => $benin->id, 'name' => 'Cotonou', 'region' => 'Littoral', 'category' => 'urban',
        'lat' => 6.3703, 'lng' => 2.3912, 'distance' => 0, 'days' => 2, 'season' => 'novembre à mars',
        'rating' => 4.6, 'featured' => true, 'order' => 1,
        'description' => "Poumon économique du Bénin, Cotonou bouillonne entre le marché géant de Dantokpa, ses plages de la Route des Pêches et une vie nocturne animée.",
        'highlights' => [
            ['icon' => 'shopping-bag', 'title' => 'Marché Dantokpa', 'description' => "L'un des plus grands marchés à ciel ouvert d'Afrique de l'Ouest"],
            ['icon' => 'sun', 'title' => 'Route des Pêches', 'description' => "Plages et village de pêcheurs à quelques minutes du centre"],
        ],
        'landmarks' => [
            ['emoji' => '⛪', 'name' => 'Cathédrale Notre-Dame', 'description' => "Édifice emblématique du centre-ville"],
            ['emoji' => '🌊', 'name' => 'Plage de Fidjrossè', 'description' => "Plage populaire prisée le week-end"],
        ],
        'howto' => "Aéroport international Cadjehoun en plein cœur de la ville ; taxis et zémidjans partout.",
        'besttime' => "Toute l'année, avec un pic de fréquentation de novembre à mars (saison sèche).",
        'budget' => "15 000 - 40 000 FCFA/jour",
        'facts' => [['fact' => "Dantokpa s'étend sur plus de 20 hectares et accueille des milliers de vendeurs chaque jour."]],
        'image_query' => 'african market colorful',
    ],
    [
        'country_id' => $benin->id, 'name' => 'Porto-Novo', 'region' => 'Ouémé', 'category' => 'historical',
        'lat' => 6.4969, 'lng' => 2.6289, 'distance' => 30, 'days' => 1, 'season' => 'novembre à mars',
        'rating' => 4.4, 'featured' => false, 'order' => 0,
        'description' => "Capitale officielle du Bénin, Porto-Novo séduit par son architecture afro-brésilienne, ses musées royaux et son atmosphère paisible.",
        'highlights' => [
            ['icon' => 'building-museum', 'title' => 'Musée Honmè', 'description' => "Ancien palais royal transformé en musée historique"],
            ['icon' => 'palette', 'title' => 'Architecture afro-brésilienne', 'description' => "Maisons colorées héritées des anciens esclaves affranchis"],
        ],
        'landmarks' => [
            ['emoji' => '🏛️', 'name' => 'Palais Honmè', 'description' => "Résidence des rois de Porto-Novo"],
            ['emoji' => '🕌', 'name' => 'Grande Mosquée', 'description' => "Mosquée à l'architecture brésilienne unique"],
        ],
        'howto' => "30 minutes en voiture depuis Cotonou par la route côtière.",
        'besttime' => "Idéal en demi-journée, toute l'année.",
        'budget' => "10 000 - 25 000 FCFA/jour",
        'facts' => [['fact' => "Porto-Novo doit son nom aux marins portugais qui la comparèrent à Porto, au Portugal."]],
        'image_query' => 'colonial architecture colorful street',
    ],
    [
        'country_id' => $benin->id, 'name' => 'Ouidah', 'region' => 'Atlantique', 'category' => 'cultural',
        'lat' => 6.3628, 'lng' => 2.0852, 'distance' => 42, 'days' => 1, 'season' => 'novembre à mars',
        'rating' => 4.8, 'featured' => true, 'order' => 2,
        'description' => "Capitale spirituelle du Vaudou et lieu de mémoire de la traite négrière, Ouidah offre un voyage historique et spirituel intense.",
        'highlights' => [
            ['icon' => 'door', 'title' => 'Route des Esclaves', 'description' => "Parcours mémoriel jusqu'à la Porte du Non-Retour"],
            ['icon' => 'moon-star', 'title' => 'Temple des Pythons', 'description' => "Sanctuaire sacré du culte vaudou"],
        ],
        'landmarks' => [
            ['emoji' => '🚪', 'name' => 'Porte du Non-Retour', 'description' => "Monument face à l'océan, symbole de la mémoire de l'esclavage"],
            ['emoji' => '🐍', 'name' => 'Temple des Pythons', 'description' => "Sanctuaire vivant abritant des pythons royaux sacrés"],
        ],
        'howto' => "45 minutes en voiture depuis Cotonou.",
        'besttime' => "Le Festival International du Vaudou a lieu en janvier.",
        'budget' => "15 000 - 35 000 FCFA/jour",
        'facts' => [['fact' => "Ouidah accueille chaque 10 janvier la Fête Nationale du Vaudou, reconnue dans le monde entier."]],
        'image_query' => 'african sacred temple ritual',
    ],
    [
        'country_id' => $benin->id, 'name' => 'Abomey', 'region' => 'Zou', 'category' => 'historical',
        'lat' => 7.1828, 'lng' => 1.9911, 'distance' => 145, 'days' => 1, 'season' => 'novembre à mars',
        'rating' => 4.9, 'featured' => true, 'order' => 3,
        'description' => "Ancienne capitale du royaume du Dahomey, Abomey abrite les Palais Royaux classés au patrimoine mondial de l'UNESCO.",
        'highlights' => [
            ['icon' => 'crown', 'title' => 'Palais Royaux', 'description' => "Site UNESCO retraçant 300 ans de règne des rois d'Abomey"],
            ['icon' => 'brush', 'title' => 'Bas-reliefs historiques', 'description' => "Fresques narrant les exploits des douze rois du Dahomey"],
        ],
        'landmarks' => [
            ['emoji' => '👑', 'name' => 'Palais du Roi Ghézo', 'description' => "L'un des palais les mieux conservés du site"],
            ['emoji' => '🎨', 'name' => 'Musée Historique', 'description' => "Trônes, sceptres et objets royaux authentiques"],
        ],
        'howto' => "2h30 de route depuis Cotonou, axe principal bitumé.",
        'besttime' => "Toute l'année, matinée recommandée pour la lumière.",
        'budget' => "12 000 - 30 000 FCFA/jour",
        'facts' => [['fact' => "Les Palais Royaux d'Abomey sont inscrits au patrimoine mondial de l'UNESCO depuis 1985."]],
        'image_query' => 'african royal palace historic',
    ],
    [
        'country_id' => $benin->id, 'name' => 'Ganvié', 'region' => 'Atlantique', 'category' => 'lakeside',
        'lat' => 6.4667, 'lng' => 2.4167, 'distance' => 18, 'days' => 1, 'season' => 'novembre à mars',
        'rating' => 4.7, 'featured' => true, 'order' => 4,
        'description' => "Surnommée la « Venise de l'Afrique », Ganvié est le plus grand village lacustre du continent, bâti entièrement sur pilotis.",
        'highlights' => [
            ['icon' => 'anchor', 'title' => 'Balade en pirogue', 'description' => "Découverte du village sur les eaux du lac Nokoué"],
            ['icon' => 'fish', 'title' => 'Marché flottant', 'description' => "Commerce animé directement depuis les pirogues"],
        ],
        'landmarks' => [
            ['emoji' => '🛶', 'name' => 'Lac Nokoué', 'description' => "Plan d'eau de 16 000 hectares abritant le village"],
            ['emoji' => '🏠', 'name' => 'Maisons sur pilotis', 'description' => "Habitat traditionnel unique en Afrique de l'Ouest"],
        ],
        'howto' => "45 minutes depuis Cotonou puis embarcadère en pirogue.",
        'besttime' => "Tôt le matin pour observer le marché flottant et les oiseaux.",
        'budget' => "10 000 - 25 000 FCFA/jour",
        'facts' => [['fact' => "Fondé au 18e siècle, Ganvié compte aujourd'hui environ 50 000 habitants vivant sur l'eau."]],
        'image_query' => 'stilt village lake africa',
    ],

    // ───────── TOGO ─────────
    [
        'country_id' => $togo->id, 'name' => 'Lomé', 'region' => 'Maritime', 'category' => 'urban',
        'lat' => 6.1319, 'lng' => 1.2228, 'distance' => 130, 'days' => 2, 'season' => 'novembre à mars',
        'rating' => 4.5, 'featured' => true, 'order' => 1,
        'description' => "Capitale animée du Togo, Lomé mélange grands marchés traditionnels, plages urbaines et scène culturelle en plein essor.",
        'highlights' => [
            ['icon' => 'shopping-cart', 'title' => 'Grand Marché', 'description' => "Textiles, artisanat et célèbres Nana Benz"],
            ['icon' => 'flask', 'title' => 'Marché des féticheurs', 'description' => "Marché vaudou d'Akodessewa, unique en son genre"],
        ],
        'landmarks' => [
            ['emoji' => '🗼', 'name' => "Monument de l'Indépendance", 'description' => "Symbole de la souveraineté togolaise"],
            ['emoji' => '🏖️', 'name' => 'Plage de Lomé', 'description' => "Front de mer animé en soirée"],
        ],
        'howto' => "Aéroport international Gnassingbé Eyadéma, à 20 minutes du centre.",
        'besttime' => "Toute l'année, novembre à mars pour la saison sèche.",
        'budget' => "15 000 - 40 000 FCFA/jour",
        'facts' => [['fact' => "Le marché des féticheurs d'Akodessewa est considéré comme le plus grand marché vaudou du monde."]],
        'image_query' => 'west africa capital city street',
    ],
    [
        'country_id' => $togo->id, 'name' => 'Kpalimé', 'region' => 'Plateaux', 'category' => 'nature',
        'lat' => 6.9, 'lng' => 0.6333, 'distance' => 220, 'days' => 2, 'season' => 'juillet à septembre',
        'rating' => 4.7, 'featured' => false, 'order' => 0,
        'description' => "Nichée dans les montagnes verdoyantes de l'ouest togolais, Kpalimé est réputée pour ses plantations de café, ses cascades et sa biodiversité.",
        'highlights' => [
            ['icon' => 'coffee', 'title' => 'Plantations de café et cacao', 'description' => "Visite guidée des exploitations locales"],
            ['icon' => 'droplet', 'title' => 'Chutes de Kpimé', 'description' => "Cascade spectaculaire au cœur de la forêt"],
        ],
        'landmarks' => [
            ['emoji' => '⛰️', 'name' => 'Mont Klouto', 'description' => "Randonnée offrant un panorama sur toute la région"],
            ['emoji' => '🦋', 'name' => "Forêt d'Assevé", 'description' => "Réserve naturelle riche en papillons endémiques"],
        ],
        'howto' => "3h de route depuis Lomé, région montagneuse.",
        'besttime' => "Saison des pluies (juin-septembre) pour des cascades pleines.",
        'budget' => "12 000 - 30 000 FCFA/jour",
        'facts' => [['fact' => "Kpalimé est surnommée la capitale togolaise du papillon en raison de sa biodiversité exceptionnelle."]],
        'image_query' => 'green mountains waterfall tropical',
    ],
    [
        'country_id' => $togo->id, 'name' => 'Kara', 'region' => 'Kara', 'category' => 'cultural',
        'lat' => 9.5511, 'lng' => 1.1861, 'distance' => 500, 'days' => 2, 'season' => 'novembre à février',
        'rating' => 4.8, 'featured' => true, 'order' => 2,
        'description' => "Porte d'entrée du nord togolais, Kara est le point de départ pour explorer la vallée du Koutammakou, classée à l'UNESCO.",
        'highlights' => [
            ['icon' => 'home', 'title' => 'Vallée du Koutammakou', 'description' => "Habitat traditionnel Tamberma, site du patrimoine mondial"],
            ['icon' => 'sword', 'title' => 'Lutte traditionnelle Kabyè', 'description' => "Spectacle de lutte ancestrale de la région"],
        ],
        'landmarks' => [
            ['emoji' => '🏯', 'name' => 'Tata Somba', 'description' => "Maisons-forteresses en terre typiques du Koutammakou"],
            ['emoji' => '🌄', 'name' => "Chaîne de l'Atakora", 'description' => "Massif montagneux traversant la région"],
        ],
        'howto' => "6h de route depuis Lomé ou vols intérieurs réguliers.",
        'besttime' => "Novembre à février, saison sèche et fraîche.",
        'budget' => "15 000 - 35 000 FCFA/jour",
        'facts' => [['fact' => "Le Koutammakou est inscrit au patrimoine mondial de l'UNESCO depuis 2004."]],
        'image_query' => 'traditional mud houses africa village',
    ],
    [
        'country_id' => $togo->id, 'name' => 'Aného', 'region' => 'Maritime', 'category' => 'historical',
        'lat' => 6.2333, 'lng' => 1.6, 'distance' => 160, 'days' => 1, 'season' => 'novembre à mars',
        'rating' => 4.3, 'featured' => false, 'order' => 0,
        'description' => "Ancienne capitale coloniale allemande, Aného conserve un charme historique entre lagune et océan Atlantique.",
        'highlights' => [
            ['icon' => 'building', 'title' => 'Architecture coloniale', 'description' => "Bâtiments hérités de la période allemande puis française"],
            ['icon' => 'sailboat', 'title' => 'Lac Togo', 'description' => "Excursions en pirogue sur une lagune paisible"],
        ],
        'landmarks' => [
            ['emoji' => '⛪', 'name' => "Cathédrale d'Aného", 'description' => "Édifice religieux historique de la ville"],
            ['emoji' => '🌊', 'name' => 'Lagune de Togoville', 'description' => "Berceau spirituel du vaudou togolais"],
        ],
        'howto' => "45 minutes depuis Lomé par la route côtière.",
        'besttime' => "Toute l'année, idéal en excursion à la journée.",
        'budget' => "10 000 - 25 000 FCFA/jour",
        'facts' => [['fact' => "Aného fut la première capitale du Togo sous administration allemande."]],
        'image_query' => 'coastal lagoon africa canoe',
    ],
    [
        'country_id' => $togo->id, 'name' => 'Sokodé', 'region' => 'Centrale', 'category' => 'cultural',
        'lat' => 8.9833, 'lng' => 1.1333, 'distance' => 380, 'days' => 1, 'season' => 'novembre à février',
        'rating' => 4.4, 'featured' => false, 'order' => 0,
        'description' => "Deuxième plus grande ville du Togo, Sokodé est le cœur culturel du peuple Kabyè, connue pour ses festivals traditionnels de masques.",
        'highlights' => [
            ['icon' => 'mask', 'title' => 'Festival Evala', 'description' => "Cérémonies traditionnelles de passage à l'âge adulte"],
            ['icon' => 'building-mosque', 'title' => 'Grande Mosquée', 'description' => "Cœur spirituel de la ville, héritage de longue date"],
        ],
        'landmarks' => [
            ['emoji' => '🕌', 'name' => 'Mosquée centrale', 'description' => "Un des plus anciens lieux de culte musulman du Togo"],
            ['emoji' => '🎭', 'name' => 'Marché artisanal', 'description' => "Tissage traditionnel et poterie locale"],
        ],
        'howto' => "4h30 de route depuis Lomé, sur l'axe national N1.",
        'besttime' => "Juillet pour assister au festival Evala.",
        'budget' => "10 000 - 25 000 FCFA/jour",
        'facts' => [['fact' => "Sokodé est historiquement un carrefour commercial entre le nord et le sud du Togo."]],
        'image_query' => 'african traditional festival mask',
    ],
];

$cities = [];
foreach ($citiesData as $data) {
    $imagePath = "cities/covers/" . \Illuminate\Support\Str::slug($data['name']) . ".jpg";
    pexelsDownload($data['image_query'], $imagePath, $pexelsKey);

    $city = City::create([
        'country_id' => $data['country_id'],
        'name' => $data['name'],
        'slug' => \Illuminate\Support\Str::slug($data['name']),
        'latitude' => $data['lat'],
        'longitude' => $data['lng'],
        'cover_image' => $imagePath,
        'description' => $data['description'],
        'region' => $data['region'],
        'distance_from_cotonou' => $data['distance'],
        'duration_days' => $data['days'],
        'best_season' => $data['season'],
        'category' => $data['category'],
        'average_rating' => $data['rating'],
        'is_featured' => $data['featured'],
        'is_active' => true,
        'featured_order' => $data['order'],
        'highlights' => $data['highlights'],
        'landmarks' => $data['landmarks'],
        'how_to_get_there' => $data['howto'],
        'best_time_detail' => $data['besttime'],
        'budget_range' => $data['budget'],
        'fun_facts' => $data['facts'],
    ]);

    $cities[$data['name']] = $city;
    echo "Ville creee : {$city->name} (id={$city->id})\n";
}

echo "\n=== 3. Creation des offres ===\n";

$offersData = [
    ['city' => 'Cotonou', 'title' => 'Immersion au Marché de Dantokpa', 'category' => 'urban', 'price' => 20000, 'duration' => 180, 'diff' => 'easy',
     'desc' => "Plongez dans l'effervescence du plus grand marché d'Afrique de l'Ouest, guidé par un habitant qui vous fera découvrir épices, tissus et spécialités locales.",
     'query' => 'african market vendor spices'],
    ['city' => 'Cotonou', 'title' => 'Journée plage sur la Route des Pêches', 'category' => 'urban', 'price' => 25000, 'duration' => 300, 'diff' => 'easy',
     'desc' => "Détente sur les plages atlantiques, rencontre avec les pêcheurs locaux et dégustation de poisson grillé face à l'océan.",
     'query' => 'african beach fishermen boat'],
    ['city' => 'Cotonou', 'title' => 'Cotonou by Night : gastronomie et musique live', 'category' => 'gastronomy', 'price' => 30000, 'duration' => 240, 'diff' => 'easy',
     'desc' => "Une soirée pour découvrir la cuisine béninoise dans ses meilleurs restaurants, suivie d'un concert de musique afrobeat dans un bar local.",
     'query' => 'african restaurant night food'],

    ['city' => 'Porto-Novo', 'title' => 'Sur les traces des rois : Palais Honmè', 'category' => 'cultural', 'price' => 18000, 'duration' => 150, 'diff' => 'easy',
     'desc' => "Visite guidée du musée Honmè, ancien palais royal, pour comprendre l'histoire de la royauté de Porto-Novo et son architecture unique.",
     'query' => 'african museum artifacts history'],
    ['city' => 'Porto-Novo', 'title' => 'Balade architecturale afro-brésilienne', 'category' => 'cultural', 'price' => 15000, 'duration' => 120, 'diff' => 'easy',
     'desc' => "Une promenade dans les ruelles colorées de Porto-Novo à la découverte des maisons construites par les anciens esclaves affranchis revenus du Brésil.",
     'query' => 'colorful colonial houses street'],

    ['city' => 'Ouidah', 'title' => "La Route des Esclaves jusqu'à la Porte du Non-Retour", 'category' => 'cultural', 'price' => 22000, 'duration' => 180, 'diff' => 'easy',
     'desc' => "Parcours mémoriel de 4 km retraçant le chemin emprunté par des milliers de captifs, jusqu'au monument face à l'océan Atlantique.",
     'query' => 'african monument memorial coast'],
    ['city' => 'Ouidah', 'title' => 'Temple des Pythons et culte Vaudou', 'category' => 'cultural', 'price' => 20000, 'duration' => 90, 'diff' => 'easy',
     'desc' => "Découverte du sanctuaire sacré abritant des pythons royaux et initiation aux fondements de la spiritualité vaudou.",
     'query' => 'sacred temple ritual africa'],
    ['city' => 'Ouidah', 'title' => "Journée complète : Musée d'Histoire et Fort portugais", 'category' => 'cultural', 'price' => 28000, 'duration' => 270, 'diff' => 'easy',
     'desc' => "Visite approfondie du Musée d'Histoire de Ouidah installé dans l'ancien fort portugais, avec un guide spécialisé en histoire coloniale.",
     'query' => 'old fort colonial museum'],

    ['city' => 'Abomey', 'title' => "Palais Royaux d'Abomey (Site UNESCO)", 'category' => 'cultural', 'price' => 25000, 'duration' => 180, 'diff' => 'easy',
     'desc' => "Visite guidée des palais royaux classés à l'UNESCO, à la découverte de 300 ans d'histoire du royaume du Dahomey.",
     'query' => 'african royal palace unesco'],
    ['city' => 'Abomey', 'title' => "Ateliers d'artisans et bas-reliefs royaux", 'category' => 'cultural', 'price' => 18000, 'duration' => 150, 'diff' => 'easy',
     'desc' => "Rencontre avec les artisans bronziers et brodeurs d'Abomey, héritiers d'un savoir-faire royal transmis de génération en génération.",
     'query' => 'african artisan craft workshop'],
    ['city' => 'Abomey', 'title' => 'Immersion Dahomey : histoire et légendes royales', 'category' => 'cultural', 'price' => 32000, 'duration' => 360, 'diff' => 'moderate',
     'desc' => "Une journée complète pour explorer l'ensemble du site historique d'Abomey, accompagné d'un conteur local partageant les légendes des douze rois.",
     'query' => 'african historic site ruins'],

    ['city' => 'Ganvié', 'title' => 'Balade en pirogue au village lacustre de Ganvié', 'category' => 'nature', 'price' => 25000, 'duration' => 150, 'diff' => 'easy',
     'desc' => "Traversée en pirogue du lac Nokoué à la découverte du plus grand village sur pilotis d'Afrique, ses maisons flottantes et son marché.",
     'query' => 'canoe lake village africa'],
    ['city' => 'Ganvié', 'title' => 'Lever de soleil avec les pêcheurs de Ganvié', 'category' => 'nature', 'price' => 20000, 'duration' => 120, 'diff' => 'easy',
     'desc' => "Départ à l'aube pour vivre la pêche traditionnelle aux côtés des habitants du lac, dans une ambiance authentique et paisible.",
     'query' => 'sunrise fishing lake africa'],

    ['city' => 'Lomé', 'title' => 'Grand Marché et Marché des Féticheurs', 'category' => 'cultural', 'price' => 22000, 'duration' => 180, 'diff' => 'easy',
     'desc' => "Découverte du Grand Marché de Lomé puis du célèbre marché vaudou d'Akodessewa, unique en Afrique de l'Ouest.",
     'query' => 'vodou market fetish africa'],
    ['city' => 'Lomé', 'title' => "Lomé balnéaire et Monument de l'Indépendance", 'category' => 'urban', 'price' => 18000, 'duration' => 150, 'diff' => 'easy',
     'desc' => "Tour de ville le long du front de mer, avec arrêt au Monument de l'Indépendance et détente sur la plage de Lomé.",
     'query' => 'togo beach monument city'],
    ['city' => 'Lomé', 'title' => 'Street art et Nana Benz : Lomé créative', 'category' => 'urban', 'price' => 20000, 'duration' => 180, 'diff' => 'easy',
     'desc' => "Circuit dans les quartiers créatifs de Lomé, à la rencontre des fresques murales et des commerçantes textiles emblématiques, les Nana Benz.",
     'query' => 'street art mural africa colorful'],

    ['city' => 'Kpalimé', 'title' => 'Randonnée café-cacao et Mont Klouto', 'category' => 'nature', 'price' => 30000, 'duration' => 300, 'diff' => 'moderate',
     'desc' => "Trek à travers les plantations de café et de cacao jusqu'au sommet du Mont Klouto, panorama exceptionnel sur les montagnes togolaises.",
     'query' => 'coffee plantation mountain trek'],
    ['city' => 'Kpalimé', 'title' => 'Cascade de Kpimé et forêt aux papillons', 'category' => 'nature', 'price' => 25000, 'duration' => 240, 'diff' => 'moderate',
     'desc' => "Randonnée jusqu'à la cascade de Kpimé puis exploration de la forêt d'Assevé, sanctuaire d'une biodiversité exceptionnelle.",
     'query' => 'waterfall tropical forest africa'],

    ['city' => 'Kara', 'title' => 'Vallée du Koutammakou et Tata Somba (UNESCO)', 'category' => 'cultural', 'price' => 35000, 'duration' => 360, 'diff' => 'moderate',
     'desc' => "Immersion dans la vallée du Koutammakou pour découvrir les célèbres maisons-forteresses Tamberma, classées au patrimoine mondial de l'UNESCO.",
     'query' => 'traditional mud fortress village'],
    ['city' => 'Kara', 'title' => 'Lutte traditionnelle Kabyè et rites ancestraux', 'category' => 'cultural', 'price' => 22000, 'duration' => 150, 'diff' => 'easy',
     'desc' => "Assistez à une démonstration de lutte traditionnelle Kabyè, sport ancestral chargé de symboles et de rituels de passage.",
     'query' => 'traditional wrestling africa culture'],
    ['city' => 'Kara', 'title' => "Trek 4x4 dans la savane de l'Atakora", 'category' => 'adventure', 'price' => 40000, 'duration' => 360, 'diff' => 'challenging',
     'desc' => "Excursion en véhicule tout-terrain à travers les paysages sauvages de la chaîne de l'Atakora, entre savane et collines.",
     'query' => 'savanna safari landscape africa'],

    ['city' => 'Aného', 'title' => 'Aného colonial : histoire et bord de mer', 'category' => 'cultural', 'price' => 16000, 'duration' => 150, 'diff' => 'easy',
     'desc' => "Découverte du patrimoine colonial allemand et français d'Aného, ancienne première capitale du Togo, suivie d'une pause plage.",
     'query' => 'colonial building coastal town'],
    ['city' => 'Aného', 'title' => 'Excursion en pirogue sur le Lac Togo', 'category' => 'nature', 'price' => 20000, 'duration' => 180, 'diff' => 'easy',
     'desc' => "Navigation paisible sur le lac Togo jusqu'à Togoville, berceau spirituel du vaudou togolais, entre lagune et villages traditionnels.",
     'query' => 'lagoon canoe traditional village'],

    ['city' => 'Sokodé', 'title' => 'Culture Kabyè et grande mosquée de Sokodé', 'category' => 'cultural', 'price' => 18000, 'duration' => 180, 'diff' => 'easy',
     'desc' => "Visite du cœur culturel du peuple Kabyè, de sa grande mosquée historique et de son marché artisanal réputé pour le tissage traditionnel.",
     'query' => 'african mosque traditional market'],
    ['city' => 'Sokodé', 'title' => 'Festival des masques et traditions du Centre-Togo', 'category' => 'cultural', 'price' => 24000, 'duration' => 210, 'diff' => 'easy',
     'desc' => "Rencontre avec les artisans de masques traditionnels et découverte des rites ancestraux qui rythment la vie culturelle de Sokodé.",
     'query' => 'african mask ceremony traditional'],
];

$featuredTitles = [
    "Palais Royaux d'Abomey (Site UNESCO)",
    'Balade en pirogue au village lacustre de Ganvié',
    "La Route des Esclaves jusqu'à la Porte du Non-Retour",
    'Vallée du Koutammakou et Tata Somba (UNESCO)',
    'Grand Marché et Marché des Féticheurs',
];

$adminUser = User::where('role', 'super_admin')->orWhere('role', 'admin')->first();
$offers = [];
$counter = 1;

foreach ($offersData as $data) {
    $city = $cities[$data['city']];
    $slug = \Illuminate\Support\Str::slug($data['title']);

    $coverPath = "offers/covers/{$slug}.jpg";
    pexelsDownload($data['query'], $coverPath, $pexelsKey);

    $galleryPaths = [];
    foreach ([1, 2] as $n) {
        $gPath = "offers/gallery/{$slug}-{$n}.jpg";
        $galleryQuery = $data['query'] . ($n === 1 ? ' close up' : ' wide view');
        if (pexelsDownload($galleryQuery, $gPath, $pexelsKey)) {
            $galleryPaths[] = $gPath;
        }
    }

    $offer = Offer::create([
        'title' => $data['title'],
        'slug' => $slug,
        'short_description' => \Illuminate\Support\Str::limit($data['desc'], 100),
        'description' => $data['desc'],
        'long_description' => '<p>' . $data['desc'] . '</p>',
        'category' => $data['category'],
        'city_id' => $city->id,
        'user_id' => $adminUser?->id,
        'guide_type' => 'agency',
        'base_price' => $data['price'],
        'payment_mode' => 'both',
        'currency' => 'XOF',
        'duration_minutes' => $data['duration'],
        'min_participants' => 1,
        'max_participants' => 12,
        'min_age' => 0,
        'difficulty_level' => $data['diff'],
        'languages' => ['Français', 'Anglais'],
        'meeting_point' => "Point de rendez-vous communiqué après réservation à {$city->name}",
        'included_items' => ['Guide local francophone', 'Transport sur place', "Frais d'entrée sur les sites"],
        'excluded_items' => ['Repas non mentionnés', 'Pourboires', 'Transport depuis votre hébergement'],
        'faq' => [
            ['q' => "Faut-il réserver à l'avance ?", 'r' => 'Oui, au moins 24h avant pour garantir la disponibilité du guide.'],
        ],
        'cover_image' => $coverPath,
        'gallery' => $galleryPaths,
        'status' => 'published',
        'published_at' => now()->subDays(rand(1, 30)),
        'is_featured' => in_array($data['title'], $featuredTitles, true),
        'is_instant_booking' => rand(0, 1) === 1,
        'available_spots' => rand(5, 20),
        'views_count' => rand(20, 400),
        'sort_order' => $counter,
        'average_rating' => round(rand(40, 50) / 10, 1),
    ]);

    $offers[] = $offer;
    echo "Offre creee : {$offer->title} (id={$offer->id})\n";
    $counter++;
}

echo "\n=== 4. Creation du spotlight ===\n";

$spotlightOffer = collect($offers)->firstWhere('title', "Palais Royaux d'Abomey (Site UNESCO)");
$spotlightImage = "spotlights/abomey-spotlight.jpg";
pexelsDownload('african heritage royal palace sunset', $spotlightImage, $pexelsKey);

Spotlight::create([
    'offer_id' => $spotlightOffer?->id,
    'title' => 'Voyagez au cœur du royaume du Dahomey',
    'subtitle' => 'Une expérience inoubliable au Bénin et au Togo',
    'description' => "Découvrez les trésors cachés de l'Afrique de l'Ouest : palais royaux classés à l'UNESCO, villages lacustres et traditions ancestrales vous attendent.",
    'image' => $spotlightImage,
    'badge_text' => 'Nouveau',
    'badge_icon' => 'sparkles',
    'highlight_word' => 'Dahomey',
    'stat1_value' => '25+',
    'stat1_label' => 'Expériences',
    'stat2_value' => '10',
    'stat2_label' => 'Villes',
    'stat3_value' => '4.9',
    'stat3_label' => 'Note moyenne',
    'cta1_label' => 'Réserver maintenant',
    'cta1_url' => null,
    'cta2_label' => 'En savoir plus',
    'cta2_url' => '/destinations',
    'is_active' => true,
    'starts_at' => null,
    'ends_at' => null,
    'sort_order' => 1,
]);

echo "Spotlight cree.\n";

echo "\n=== 5. Creation de l'article de blog ===\n";

$blogImage = "blog/covers/decouvrir-benin-togo.jpg";
pexelsDownload('africa travel adventure landscape', $blogImage, $pexelsKey);

BlogPost::create([
    'title' => '5 raisons de découvrir le Bénin et le Togo en 2026',
    'excerpt' => "Entre patrimoine UNESCO, villages lacustres et traditions vaudou, le Bénin et le Togo comptent parmi les destinations les plus authentiques d'Afrique de l'Ouest.",
    'content' => "<p>L'Afrique de l'Ouest recèle des trésors encore méconnus des voyageurs. Le Bénin et le Togo, voisins partageant une riche histoire commune, offrent une diversité d'expériences rares.</p>"
        . "<h2>1. Un patrimoine historique exceptionnel</h2>"
        . "<p>Les Palais Royaux d'Abomey, classés à l'UNESCO, retracent trois siècles de règne du royaume du Dahomey. Plus au nord, la vallée du Koutammakou au Togo dévoile les célèbres maisons-forteresses Tamberma, elles aussi inscrites au patrimoine mondial.</p>"
        . "<h2>2. Des paysages à couper le souffle</h2>"
        . "<p>Du village lacustre de Ganvié bâti entièrement sur pilotis aux montagnes verdoyantes de Kpalimé et ses cascades, la région regorge de panoramas uniques en Afrique de l'Ouest.</p>"
        . "<h2>3. Une spiritualité vivante</h2>"
        . "<p>Berceau du culte vaudou, Ouidah et sa Route des Esclaves offrent un voyage mémoriel intense, tandis que le marché des féticheurs de Lomé reste une expérience culturelle unique au monde.</p>"
        . "<h2>4. Une gastronomie généreuse</h2>"
        . "<p>Poisson braisé sur la Route des Pêches, plats mijotés béninois et spécialités togolaises à base de manioc et d'igname raviront les amateurs de découvertes culinaires.</p>"
        . "<h2>5. Un accueil authentique</h2>"
        . "<p>Guides locaux passionnés, artisans transmettant un savoir-faire ancestral et communautés lacustres accueillantes : chaque expérience DiscovTrip est pensée pour un tourisme responsable et humain.</p>"
        . "<p>Prêt à explorer ces deux joyaux d'Afrique de l'Ouest ? Découvrez toutes nos expériences sur DiscovTrip.</p>",
    'cover_image' => $blogImage,
    'category' => 'destinations',
    'tags' => ['Bénin', 'Togo', 'UNESCO', 'Culture', 'Voyage'],
    'author_id' => $adminUser?->id,
    'status' => 'published',
    'published_at' => now()->subDays(2),
    'meta_title' => '5 raisons de découvrir le Bénin et le Togo en 2026 | DiscovTrip',
    'meta_description' => "Patrimoine UNESCO, villages lacustres, culture vaudou : découvrez pourquoi le Bénin et le Togo sont les destinations montantes d'Afrique de l'Ouest.",
]);

echo "Article de blog cree.\n";

echo "\n=== TERMINE ===\n";
echo Country::count() . " pays, " . City::count() . " villes, " . Offer::count() . " offres, "
    . Spotlight::count() . " spotlight, " . BlogPost::count() . " article de blog.\n";
