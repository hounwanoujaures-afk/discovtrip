<?php

// ═══════════════════════════════════════════════════════════════════════
// DISCOVTRIP — index.php pour public_html/ sur Hostinger
//
// Ce fichier remplace le index.php généré par Laravel. La seule différence :
// les deux chemins ci-dessous pointent vers ~/discovtrip_app/ au lieu de
// pointer vers le dossier parent direct, car sur Hostinger le projet Laravel
// est hébergé HORS de public_html (public_html ne doit contenir que le
// contenu de public/), voir DEPLOIEMENT.md pour le détail complet.
//
// Structure attendue sur le serveur :
//   /home/VOTRE_USER/discovtrip_app/   ← tout le projet Laravel (ce zip)
//   /home/VOTRE_USER/public_html/      ← seulement le contenu de public/
// ═══════════════════════════════════════════════════════════════════════

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Chemin vers le dossier du projet Laravel, un niveau au-dessus de public_html.
// ⚠️ Si votre dossier ne s'appelle pas "discovtrip_app", changez-le ci-dessous.
$appPath = __DIR__.'/../discovtrip_app';

if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appPath.'/vendor/autoload.php';

$app = require_once $appPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
