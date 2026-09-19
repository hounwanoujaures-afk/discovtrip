<?php
require __DIR__ . '/vendor/autoload.php';
$app    = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Offer;

echo "\n=== Promos sur les bonnes colonnes ===\n";

$promos = [
    1  => ['discount' => 15, 'label' => 'Offre de lancement -15%',   'ends' => '2026-12-31'],
    7  => ['discount' => 10, 'label' => 'Découverte Vaudou -10%',    'ends' => '2026-12-31'],
    12 => ['discount' => 20, 'label' => 'Spécial Ganvié -20%',       'ends' => '2026-11-30'],
    29 => ['discount' => 15, 'label' => 'Grand-Popo Découverte -15%','ends' => '2026-12-31'],
    28 => ['discount' => 10, 'label' => 'Collines Sacrées -10%',     'ends' => '2026-12-31'],
];

foreach ($promos as $id => $p) {
    $offer = Offer::find($id);
    if (!$offer) { echo "  [skip] offre {$id} introuvable\n"; continue; }

    $discounted = round((float) $offer->base_price * (1 - $p['discount'] / 100));

    $offer->update([
        'promotional_price'   => $discounted,
        'discount_percentage' => $p['discount'],
        'promo_description'   => $p['label'],
        'promotion_starts_at' => now(),
        'promotion_ends_at'   => $p['ends'],
    ]);

    // Vérifier que c'est bien sauvegardé
    $offer->refresh();
    echo "  ✓ {$offer->title}\n";
    echo "    Base: {$offer->base_price} → Promo: {$offer->promotional_price} FCFA (-{$p['discount']}%)\n";
    echo "    Expire: {$offer->promotion_ends_at}\n";
}

echo "\n=== Vérification finale ===\n";
$actives = Offer::whereNotNull('promotional_price')
    ->where('promotion_ends_at', '>=', now())
    ->count();
echo "Promos actives en base : {$actives}\n";

echo "\n✓ Terminé !\n";
