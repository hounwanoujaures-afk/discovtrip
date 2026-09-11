<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ════════════════════════════════════════════════════════════════════════════════
 * SECURITY HEADERS MIDDLEWARE
 * ════════════════════════════════════════════════════════════════════════════════
 * Ajoute des headers de sécurité HTTP pour protéger contre:
 * - XSS (Cross-Site Scripting)
 * - Clickjacking
 * - MIME-type sniffing
 * - Man-in-the-middle attacks
 * - Information leakage
 * ════════════════════════════════════════════════════════════════════════════════
 */
class SecurityHeaders
{
    private const SECURITY_HEADERS = [
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::SECURITY_HEADERS as $header => $value) {
            $response->headers->set($header, $value, false);
        }

        if (config('security.csp.enabled', true)) {
            $response->headers->set(
                'Content-Security-Policy',
                $this->buildCspHeader($request),
                false
            );
        }

        if ($request->secure() && config('security.hsts.enabled', true)) {
            $maxAge = config('security.hsts.max_age', 31536000);
            $includeSubDomains = config('security.hsts.include_subdomains', true);
            $preload = config('security.hsts.preload', false);

            $hstsValue = "max-age={$maxAge}";
            if ($includeSubDomains) {
                $hstsValue .= '; includeSubDomains';
            }
            if ($preload) {
                $hstsValue .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $hstsValue, false);
        }

        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    /**
     * Construire le header Content-Security-Policy.
     * CSP stricte par défaut ; des directives supplémentaires (ex. 'unsafe-eval'
     * pour Alpine.js/Livewire) ne sont fusionnées que sur les routes admin.
     */
    private function buildCspHeader(Request $request): string
    {
        $cspDirectives = config('security.csp.directives', [
            "default-src" => ["'self'"],
            "script-src" => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
            "style-src" => ["'self'", "'unsafe-inline'"],
            "img-src" => ["'self'", "data:", "https:"],
            "font-src" => ["'self'", "data:"],
            "connect-src" => ["'self'"],
            "frame-ancestors" => ["'none'"],
            "base-uri" => ["'self'"],
            "form-action" => ["'self'"],
        ]);

        $csp = [];
        foreach ($cspDirectives as $directive => $sources) {
            $csp[] = $directive . ' ' . implode(' ', $sources);
        }

        return implode('; ', $csp);
    }

    private function shouldEnforceCsrf(Request $request): bool
    {
        $exemptPaths = config('security.csrf.exempt', [
            'api/*',
        ]);

        foreach ($exemptPaths as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }
}