<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserIsNotBanned;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        // ── Trust Proxies (reverse proxy / CDN devant l'appli, ex. Cloudflare sur Hostinger) ──
        // CRITIQUE : sans ça, les requêtes arrivent en http:// en interne
        // → détection HTTPS faussée, cookies "secure" et signatures Livewire cassés.
        // Kernel.php est ignoré en Laravel 11/12 pour les proxies —
        // seul bootstrap/app.php est pris en compte.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR      |
                     Request::HEADER_X_FORWARDED_HOST     |
                     Request::HEADER_X_FORWARDED_PORT     |
                     Request::HEADER_X_FORWARDED_PROTO
        );

        // ── Sécurité globale (toutes les requêtes, web + futur api) ──
        // NOTE AUDIT : ces 3 middlewares existaient déjà mais n'étaient enregistrés
        // que dans app/Http/Kernel.php, un fichier que Laravel 11/12 n'exécute plus
        // du tout. Ils étaient donc écrits mais jamais réellement actifs. Rebranchés ici.
        $middleware->append([
            \App\Http\Middleware\SecurityHeaders::class,   // CSP, X-Frame-Options, HSTS
            \App\Http\Middleware\SanitizeInput::class,     // Détection XSS (sans double-encodage)
            \App\Http\Middleware\IpFiltering::class,       // Blacklist IP (no-op tant que désactivé en config)
        ]);
        // ForceHttps n'est PAS rebranché ici : URL::forceScheme('https') dans
        // AppServiceProvider fait déjà ce travail et fonctionne réellement en prod.
        // L'ajouter en plus créerait une double redirection HTTP → HTTPS.

        // ── Middleware globaux (toutes les requêtes web) ──────────
        $middleware->web(append: [
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);

        // ── Corrige le trimming automatique : ne pas trimmer les mots de passe ──
        // (App\Http\Middleware\TrimStrings existait déjà avec cette exception,
        // mais n'était pas non plus branché — Laravel utilisait sa version par défaut)
        $middleware->trimStrings(except: [
            'current_password',
            'password',
            'password_confirmation',
        ]);

        // ── Alias utilisables dans les routes ─────────────────────
        $middleware->alias([
            'not.banned'    => EnsureUserIsNotBanned::class,
            'role'          => \App\Http\Middleware\CheckRole::class,
            'admin'         => \App\Http\Middleware\AdminMiddleware::class,
            'rate.advanced' => \App\Http\Middleware\AdvancedRateLimiting::class,
            'audit'         => \App\Http\Middleware\AuditLog::class,
        ]);

        // ── Exclusions CSRF ────────────────────────────────────────
        // Le webhook Stripe doit recevoir le payload brut non modifié
        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions) {

        // ── Page 404 personnalisée ─────────────────────────────────
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->expectsJson()) {
                return response()->view('errors.404', [], 404);
            }
        });

        // ── Pages d'erreur HTTP génériques (500, 403, etc.) ────────
        $exceptions->render(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            $view   = "errors.{$status}";

            if (! $request->expectsJson() && view()->exists($view)) {
                return response()->view($view, [], $status);
            }
        });

    })

    ->create();