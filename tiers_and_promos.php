<?php

require __DIR__ . '/vendor/autoload.php';
$app    = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Offer;
use App\Models\OfferTier;
use Illuminate\Support\Facades\Schema;

echo "\n=== 1. Correction is_circuit ===\n";
$updated = Offer::whereIn('id', [34, 35, 36, 37, 38, 39])->update(['is_circuit' => true]);
echo "  ✓ {$updated} offres marquées is_circuit = true\n";

echo "\n=== 2. Niveaux Découverte / Confort / Exception ===\n";

$tiersData = [
    9 => [
        ['type'=>'discovery','label'=>'Découverte','tagline'=>"L'essentiel de l'expérience",'price'=>25000,'price_is_indicative'=>false,'description'=>"Visite guidée des Palais Royaux d'Abomey avec guide local. Découverte des bas-reliefs, trônes et musée historique.",'included_items'=>['Guide local francophone','Entrée Palais Royaux UNESCO','Eau minérale'],'excluded_items'=>['Transport depuis Cotonou','Déjeuner','Pourboires'],'whatsapp_only'=>false,'sort_order'=>1],
        ['type'=>'comfort','label'=>'Confort','tagline'=>'Transport, repas et confort inclus','price'=>38000,'price_is_indicative'=>false,'description'=>"Visite complète avec transport climatisé depuis Cotonou, guide expert, déjeuner traditionnel béninois et visite des ateliers d'artisans bronziers.",'included_items'=>['Transport climatisé depuis Cotonou','Guide expert patrimoine','Entrée Palais Royaux UNESCO','Visite ateliers bronziers','Déjeuner traditionnel','Eau minérale'],'excluded_items'=>['Pourboires','Achats artisanaux','Boissons alcoolisées'],'whatsapp_only'=>false,'sort_order'=>2],
        ['type'=>'exception','label'=>'Exception','tagline'=>'Expérience VIP entièrement personnalisée','price'=>65000,'price_is_indicative'=>true,'description'=>"Journée privée à Abomey avec historien spécialisé, audience protocolaire avec la cour royale, accès aux zones réservées et dîner gastronomique béninois.",'included_items'=>['Transport VIP privatisé','Historien spécialisé Dahomey','Entrée Palais Royaux UNESCO','Audience protocolaire cour royale','Accès zones réservées','Déjeuner + dîner gastronomiques','Eau minérale premium','Séance photo professionnelle'],'excluded_items'=>['Vol international','Hébergement sur demande','Assurance voyage'],'whatsapp_only'=>false,'sort_order'=>3],
    ],
    6 => [
        ['type'=>'discovery','label'=>'Découverte','tagline'=>"L'essentiel de l'expérience",'price'=>22000,'price_is_indicative'=>false,'description'=>"Parcours mémoriel de la Route des Esclaves jusqu'à la Porte du Non-Retour avec guide culturel.",'included_items'=>['Guide culturel','Parcours Route des Esclaves','Accès Porte du Non-Retour','Eau minérale'],'excluded_items'=>['Transport depuis Cotonou','Temple des Pythons','Déjeuner'],'whatsapp_only'=>false,'sort_order'=>1],
        ['type'=>'comfort','label'=>'Confort','tagline'=>'Transport, repas et confort inclus','price'=>35000,'price_is_indicative'=>false,'description'=>"Journée complète à Ouidah : Route des Esclaves, Temple des Pythons, Fort portugais et Forêt sacrée de Kpassè avec transport depuis Cotonou.",'included_items'=>['Transport climatisé depuis Cotonou','Guide expert histoire coloniale','Route des Esclaves + Porte du Non-Retour','Temple des Pythons','Forêt sacrée de Kpassè','Déjeuner traditionnel','Eau minérale'],'excluded_items'=>['Pourboires','Achats personnels'],'whatsapp_only'=>false,'sort_order'=>2],
        ['type'=>'exception','label'=>'Exception','tagline'=>'Expérience VIP entièrement personnalisée','price'=>58000,'price_is_indicative'=>true,'description'=>"Immersion privée à Ouidah : cérémonie vodoun exclusive au coucher du soleil sur la plage, guide historien et dîner face à l'Atlantique.",'included_items'=>['Transport VIP privatisé','Historien spécialisé traite négrière','Route des Esclaves + Porte du Non-Retour','Cérémonie vodoun privée au coucher du soleil','Accès sanctuaires privés','Déjeuner + dîner face Atlantique','Eau minérale premium'],'excluded_items'=>['Assurance voyage','Hébergement sur demande'],'whatsapp_only'=>false,'sort_order'=>3],
    ],
    12 => [
        ['type'=>'discovery','label'=>'Découverte','tagline'=>"L'essentiel de l'expérience",'price'=>20000,'price_is_indicative'=>false,'description'=>"Traversée en pirogue du lac Nokoué et découverte du village lacustre de Ganvié, le plus grand d'Afrique.",'included_items'=>['Pirogue aller-retour','Guide local','Eau minérale'],'excluded_items'=>['Transport depuis Cotonou','Déjeuner','Marché flottant selon heure'],'whatsapp_only'=>false,'sort_order'=>1],
        ['type'=>'comfort','label'=>'Confort','tagline'=>'Transport, repas et confort inclus','price'=>32000,'price_is_indicative'=>false,'description'=>"Découverte complète de Ganvié avec transport depuis Cotonou, pirogue, visite du marché flottant et déjeuner de poisson frais sur le lac.",'included_items'=>['Transport depuis Cotonou','Pirogue aller-retour','Guide local','Visite marché flottant','Déjeuner poisson frais sur le lac','Eau minérale'],'excluded_items'=>['Pourboires','Achats personnels'],'whatsapp_only'=>false,'sort_order'=>2],
        ['type'=>'exception','label'=>'Exception','tagline'=>'Expérience VIP entièrement personnalisée','price'=>55000,'price_is_indicative'=>true,'description'=>"Lever de soleil privé sur le lac Nokoué, pirogue exclusive avec pêcheur local, immersion dans une famille de Ganvié et déjeuner gastronomique sur pilotis.",'included_items'=>['Transport VIP privatisé','Pirogue privée exclusive','Guide + interprète local','Lever de soleil sur le lac','Immersion famille Ganvié','Déjeuner gastronomique sur pilotis','Eau minérale premium','Séance photo'],'excluded_items'=>['Assurance voyage'],'whatsapp_only'=>false,'sort_order'=>3],
    ],
    38 => [
        ['type'=>'discovery','label'=>'Découverte','tagline'=>"L'essentiel de l'expérience",'price'=>165000,'price_is_indicative'=>false,'description'=>"7 jours à travers le Bénin en groupe : Cotonou, Ouidah, Abomey, Dassa-Zoumè, Natitingou et Tata Somba avec hébergement 3 étoiles.",'included_items'=>['Transport climatisé tout le circuit','Guide francophone 7 jours','Entrées sites majeurs','6 nuitées hôtel 3 étoiles chambre double','Petits-déjeuners','7 déjeuners','Eau minérale'],'excluded_items'=>['Dîners sauf mention','Vol international','Pourboires','Achats','Assurance'],'whatsapp_only'=>false,'sort_order'=>1],
        ['type'=>'comfort','label'=>'Confort','tagline'=>'Transport, repas et confort inclus','price'=>215000,'price_is_indicative'=>false,'description'=>"7 jours premium avec hôtels 4 étoiles, tous repas inclus, guide expert patrimoine UNESCO et activités enrichies : pirogue Ganvié, démonstration artisanale à Abomey.",'included_items'=>['Transport climatisé VIP','Guide expert patrimoine','Entrées tous sites','6 nuitées hôtel 4 étoiles chambre double','Tous repas inclus','Pirogue Ganvié','Démonstration artisanale Abomey','Eau minérale premium'],'excluded_items'=>['Vol international','Pourboires','Achats','Assurance'],'whatsapp_only'=>false,'sort_order'=>2],
        ['type'=>'exception','label'=>'Exception','tagline'=>'Expérience VIP entièrement personnalisée','price'=>350000,'price_is_indicative'=>true,'description'=>"7 jours entièrement sur mesure : véhicule privé, historien dédié, lodges de charme, accès aux cérémonies privées et séance photo professionnelle.",'included_items'=>['Véhicule privé 4x4 climatisé','Historien et guide privé dédié','Entrées tous sites + zones réservées','6 nuitées lodges de charme ou hôtels 5 étoiles','Tous repas gastronomiques','Accès cérémonies privées','Séance photo professionnelle','Eau minérale premium','Assistance 24h sur 24'],'excluded_items'=>['Vol international','Assurance voyage'],'whatsapp_only'=>false,'sort_order'=>3],
    ],
    36 => [
        ['type'=>'discovery','label'=>'Découverte','tagline'=>"L'essentiel de l'expérience",'price'=>55000,'price_is_indicative'=>false,'description'=>"3 jours pour vivre les Vodoun Days : cérémonie du 10 janvier à Ouidah, Route des Esclaves et Porte du Non-Retour.",'included_items'=>['Transport climatisé','Guide culturel vodoun','Accès cérémonie du 10 janvier','Route des Esclaves + Porte du Non-Retour','2 nuitées hôtel 3 étoiles','Petits-déjeuners','Eau minérale'],'excluded_items'=>['Déjeuners et dîners','Dons rituels','Pourboires','Achats','Assurance'],'whatsapp_only'=>false,'sort_order'=>1],
        ['type'=>'comfort','label'=>'Confort','tagline'=>'Transport, repas et confort inclus','price'=>85000,'price_is_indicative'=>false,'description'=>"3 jours immersifs : veillée spirituelle la nuit du 9, cérémonie au lever du soleil le 10 janvier, Route des Esclaves, Grand-Popo et tous repas inclus.",'included_items'=>['Transport climatisé','Guide expert vodoun','Veillée spirituelle nuit du 9 janvier','Cérémonie lever du soleil 10 janvier','Route des Esclaves + Porte du Non-Retour','Grand-Popo','2 nuitées hôtel 3 étoiles','Tous repas 3 déj + 2 dîners','Eau minérale'],'excluded_items'=>['Dons rituels','Pourboires','Achats','Assurance'],'whatsapp_only'=>false,'sort_order'=>2],
        ['type'=>'exception','label'=>'Exception','tagline'=>'Expérience VIP entièrement personnalisée','price'=>145000,'price_is_indicative'=>true,'description'=>"3 jours VIP pour les Vodoun Days : accès aux cérémonies privées, rencontre exclusive avec un Hounongan grand prêtre vodoun, lodge de charme face à l'Atlantique.",'included_items'=>['Transport VIP privatisé','Anthropologue spécialisé vodoun','Accès cérémonies privées','Rencontre exclusive Hounongan','Route des Esclaves + Porte du Non-Retour','Veillée et cérémonie lever du soleil','Lodge de charme face Atlantique 2 nuits','Tous repas gastronomiques','Eau minérale premium','Séance photo'],'excluded_items'=>['Vol international','Assurance voyage'],'whatsapp_only'=>false,'sort_order'=>3],
    ],
];

foreach ($tiersData as $offerId => $tiers) {
    $offer = Offer::find($offerId);
    if (!$offer) { echo "  [skip] offre {$offerId} introuvable\n"; continue; }
    OfferTier::where('offer_id', $offerId)->delete();
    foreach ($tiers as $t) {
        OfferTier::create(array_merge($t, ['offer_id'=>$offerId,'currency'=>'XOF','is_active'=>true]));
    }
    echo "  ✓ {$offer->title} — 3 niveaux créés\n";
}

echo "\n=== 3. Colonnes promo ===\n";
if (!Schema::hasColumn('offers', 'discount_percent')) {
    Schema::table('offers', function ($table) {
        $table->unsignedTinyInteger('discount_percent')->nullable()->after('base_price');
        $table->decimal('discounted_price', 10, 2)->nullable()->after('discount_percent');
        $table->string('promo_label')->nullable()->after('discounted_price');
        $table->date('promo_ends_at')->nullable()->after('promo_label');
    });
    echo "  ✓ Colonnes promo créées\n";
} else {
    echo "  ✓ Colonnes promo déjà présentes\n";
}

echo "\n=== 4. Promos sur 5 offres béninoises ===\n";
$promos = [
    1  => ['discount'=>15,'label'=>'Offre de lancement -15%','ends'=>'2026-12-31'],
    7  => ['discount'=>10,'label'=>'Découverte Vaudou -10%','ends'=>'2026-12-31'],
    12 => ['discount'=>20,'label'=>'Spécial Ganvié -20%','ends'=>'2026-11-30'],
    29 => ['discount'=>15,'label'=>'Grand-Popo Découverte -15%','ends'=>'2026-12-31'],
    28 => ['discount'=>10,'label'=>'Collines Sacrées -10%','ends'=>'2026-12-31'],
];

foreach ($promos as $offerId => $promo) {
    $offer = Offer::find($offerId);
    if (!$offer) { echo "  [skip] offre {$offerId} introuvable\n"; continue; }
    $original   = (float) $offer->base_price;
    $discounted = round($original * (1 - $promo['discount'] / 100));
    $offer->update([
        'discount_percent' => $promo['discount'],
        'discounted_price' => $discounted,
        'promo_label'      => $promo['label'],
        'promo_ends_at'    => $promo['ends'],
    ]);
    echo "  ✓ {$offer->title} : {$original} → {$discounted} FCFA (-{$promo['discount']}%)\n";
}

echo "\n=== RÉSUMÉ ===\n";
echo "Tiers créés    : " . OfferTier::count() . "\n";
echo "Promos actives : " . Offer::whereNotNull('discount_percent')->count() . "\n";
echo "\n✓ Terminé !\n";
