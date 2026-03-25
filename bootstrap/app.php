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

        // ── Middleware globaux (toutes les requêtes web) ──────────
        $middleware->web(append: [
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);

        // ── Alias utilisables dans les routes ─────────────────────
        $middleware->alias([
            'not.banned' => EnsureUserIsNotBanned::class,
            'role'       => \App\Http\Middleware\CheckRole::class,
            'admin'      => \App\Http\Middleware\AdminMiddleware::class,
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