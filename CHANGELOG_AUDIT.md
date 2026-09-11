# Changelog — Corrections post-audit (septembre 2026)

## Sécurité
- **`.env.production`** réécrit : nouvelle `APP_KEY` (l'ancienne était exposée), `GROQ_API_KEY` vidée (ancienne clé exposée en clair, à révoquer sur console.groq.com), `DB_CONNECTION` basculé sur `mysql` avec placeholders clairs.
- **`bootstrap/app.php`** : `SecurityHeaders`, `SanitizeInput`, `IpFiltering` rebranchés en middleware globaux — ils n'étaient enregistrés que dans `app/Http/Kernel.php`, un fichier que Laravel 11/12 n'exécute plus. Alias `rate.advanced` et `audit` ajoutés pour usage ciblé sur les routes sensibles. Correction du trimming automatique pour exclure les champs mot de passe.
- **`app/Http/Kernel.php`** supprimé (dead code — Laravel 12 ne le lit pas).
- **`app/Support/Html.php`** (nouveau) : sanitiseur HTML sans dépendance externe, retire les attributs dangereux (`onclick`, `javascript:`...) des balises autorisées.
- **`resources/views/pages/blog/show.blade.php`**, **`faq.blade.php`**, **`offers/show.blade.php`** : sorties HTML non échappées (`{!! !!}` brut ou `strip_tags()` seul) remplacées par `Html::clean()`.
- **`config/security.php`** : section 2FA retirée — elle affichait `enabled => true` par défaut alors qu'aucun code ne l'implémente réellement (faux sentiment de sécurité).

## Déploiement (migration Railway → Hostinger)
- `Dockerfile`, `nixpacks.toml` (corrompu — contenait du texte parasite), `start.sh`, `.dockerignore` supprimés — inutiles sur un hébergement mutualisé.
- `public/.htaccess` : forçage HTTPS et redirection `www→discovtrip.com` activés (étaient prêts mais commentés).
- `public/.user.ini` : chemin de log corrigé (pointait vers `/var/log/`, inaccessible en écriture sur du mutualisé).
- **`deploy/public_html/index.php`** (nouveau) + **`deploy/DEPLOIEMENT.md`** (nouveau) : structure et guide complet pour héberger un projet Laravel hors de `public_html` sur Hostinger.

## Qualité / nettoyage
- `tests/{Unit/Domain,Feature/Api}/` supprimé (résidu d'une commande `mkdir` avec expansion d'accolades ratée, dossier vide).
- `public/images/logo1.png` et `logo1.jpg` supprimés (fichiers orphelins, non référencés nulle part).
- `public/images/logo.jpg` supprimé — c'était en réalité un PNG (RGBA) renommé en `.jpg`, une source de bug latent. Les 3 références dans le code pointent maintenant vers `logo.png` (le vrai PNG).
- 56 fichiers texte (`.php`, `.blade.php`, `.css`, `.js`...) avec fins de ligne CRLF (Windows) normalisés en LF, cohérent avec `.gitattributes` qui l'impose déjà.
- `storage/logs/*.log` (38 Mo de logs obsolètes) et les fichiers de session/cache/vues compilées dans `storage/framework/` purgés — données runtime, jamais du code, sans valeur à conserver.
- Ajout de `loading="lazy"` sur l'avatar témoignage en page d'accueil (`home.blade.php`) — les autres images de la page géraient déjà ça correctement (chargement conditionnel eager/lazy selon la position).

## Restant à faire par vous (pas automatisable depuis ici)
- Révoquer la clé Groq exposée sur console.groq.com, en générer une nouvelle, la coller directement dans le `.env` sur le serveur Hostinger (jamais dans un fichier échangé).
- Purger l'historique Git des fichiers de session commités (`git filter-repo`), puis force-push.
- Tester `App\Support\Html::clean()` en local (`php artisan tinker`) avant déploiement — non exécutable depuis l'environnement où ce correctif a été écrit.
- `composer audit` et `npm audit` avant mise en production.
- Décider si les données de `database.sqlite` doivent être migrées vers MySQL ou si un `migrate --force` sur une base neuve suffit.
