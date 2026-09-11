# Déploiement DiscovTrip sur Hostinger

Hostinger (hébergement mutualisé/Business) ne permet pas de changer le dossier racine du site (`public_html`). La méthode standard, utilisée dans ce guide : le projet Laravel complet vit **à côté** de `public_html`, et seul le contenu de `public/` est copié dedans.

```
/home/VOTRE_USER/
├── discovtrip_app/     ← tout le projet Laravel (ce zip corrigé)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── vendor/         ← créé par composer install, pas dans le zip
│   ├── .env             ← créé à partir de .env.production, jamais commité
│   └── ...
└── public_html/        ← SEULEMENT le contenu de discovtrip_app/public/
    ├── index.php        ← version adaptée, voir deploy/public_html/index.php
    ├── .htaccess
    ├── build/
    └── ...
```

## 1. Créer la base de données MySQL

hPanel → **Bases de données** → **Bases de données MySQL**. Notez bien : nom, utilisateur et mot de passe (le nom aura un préfixe du type `u123456789_`). Reportez ces valeurs dans `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

## 2. Envoyer le projet sur le serveur

Deux options :
- **Via Git + SSH** (recommandé si le plan inclut SSH) : `git clone` votre dépôt directement dans `~/discovtrip_app`.
- **Via l'archive** : uploadez le zip corrigé dans le File Manager hPanel, à la racine du compte (pas dans `public_html`), puis extrayez-le et renommez le dossier en `discovtrip_app`.

## 3. Préparer `public_html`

Copiez **le contenu** de `discovtrip_app/public/` (pas le dossier `public` lui-même) dans `public_html/` : `index.php`, `.htaccess`, `.user.ini`, `build/`, `images/`, `favicon.ico`, `robots.txt`, etc.

Puis **remplacez** le `index.php` copié par celui fourni dans `deploy/public_html/index.php` de ce projet — c'est le même fichier, avec deux lignes modifiées pour aller chercher `vendor/` et `bootstrap/app.php` dans `discovtrip_app/` au lieu du dossier parent direct.

⚠️ Si vous nommez votre dossier autrement que `discovtrip_app`, changez la ligne `$appPath = __DIR__.'/../discovtrip_app';` en conséquence.

## 4. Configurer `.env`

Sur le serveur (File Manager ou SSH), copiez `discovtrip_app/.env.production` vers `discovtrip_app/.env`, puis complétez les valeurs marquées `A_REMPLACER_PAR_...` :
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (étape 1)
- `MAIL_PASSWORD` (mot de passe de la boîte `noreply@discovtrip.com` créée dans hPanel → Emails)
- `KKIAPAY_SECRET, KKIAPAY_PUBLIC_KEY, KKIAPAY_PRIVATE_KEY, KKIAPAY_SANDBOX (mettre à false en prod)`, `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` (vos vraies clés de production)
- `GROQ_API_KEY` (la **nouvelle** clé, après avoir révoqué l'ancienne sur console.groq.com)

**Ne collez jamais ces valeurs ailleurs que directement sur le serveur** (pas dans le zip qu'on échange, pas dans Git).

## 5. Installer les dépendances (SSH)

```bash
cd ~/discovtrip_app
composer install --no-dev --optimize-autoloader
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`storage:link` crée le lien symbolique `public/storage` → nécessite SSH (le File Manager ne sait pas créer de vrais symlinks).

**Sur les données existantes :** la base SQLite actuelle (`database/database.sqlite`) n'est pas transférée automatiquement. Si son contenu n'est que des données de test, `migrate --force` sur une base MySQL vide suffit. Si vous voulez conserver de vraies données déjà saisies, dites-le-moi avant cette étape — un export/import demande une commande à part qu'on préparera ensemble.

## 6. Cron job (scheduler Laravel)

hPanel → **Avancé** → **Tâches CRON** → nouvelle tâche, toutes les minutes :

```bash
* * * * * cd /home/VOTRE_USER/discovtrip_app && php artisan schedule:run >> /dev/null 2>&1
```

Vérifiez le chemin exact du binaire PHP avec `which php` en SSH si la tâche ne semble pas s'exécuter (Hostinger utilise parfois un chemin versionné du type `/opt/alt/php84/usr/bin/php`).

## 7. Permissions

```bash
find discovtrip_app -type f -exec chmod 644 {} \;
find discovtrip_app -type d -exec chmod 755 {} \;
chmod -R 755 discovtrip_app/storage discovtrip_app/bootstrap/cache
```

Jamais `777` (voir audit) — `755` suffit, le process PHP de Hostinger tourne déjà avec le compte propriétaire des fichiers.

## 8. SSL

hPanel → **Sécurité** → **SSL** → activer le certificat Let's Encrypt gratuit sur `discovtrip.com` et `www.discovtrip.com`. Une fois confirmé que le site charge bien en `https://`, décommentez la ligne HSTS dans `public_html/.htaccess` (elle est présente mais désactivée par précaution tant que le certificat n'est pas confirmé).

## 9. Vérification finale

- `APP_DEBUG=false` dans `.env` (jamais `true` en production — sinon la moindre erreur affiche la stack trace complète au public)
- Ouvrir le site, tester une réservation de bout en bout, vérifier `discovtrip_app/storage/logs/` en cas d'erreur 500
- Vérifier que `discovtrip_app/.env` n'est PAS accessible publiquement : essayez `https://discovtrip.com/.env` dans un navigateur — ça doit renvoyer une erreur 403/404 (le `.htaccess` de `public_html` le bloque déjà, mais autant vérifier)
