<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ════════════════════════════════════════════════════════════════════════════════
 * TRUSTED PROXIES MIDDLEWARE
 * ════════════════════════════════════════════════════════════════════════════════
 * Gère les proxies de confiance (load balancers, CDN, etc.)
 * Important pour récupérer la vraie IP du client
 * ════════════════════════════════════════════════════════════════════════════════
 */
class TrustProxies
{
    /**
     * The trusted proxies for this application.
     */
    protected array $proxies = [];

    /**
     * The headers that should be used to detect proxies.
     */
    protected int $headers = 
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Configurer les proxies de confiance
        $this->setTrustedProxies($request);

        return $next($request);
    }

    /**
     * Set the trusted proxies for the request.
     */
    protected function setTrustedProxies(Request $request): void
    {
        $proxies = $this->proxies();

        if ($proxies === '*' || $proxies === '**') {
            // Trust all proxies (NOT recommended for production)
            $request->setTrustedProxies(
                [$request->server->get('REMOTE_ADDR')],
                $this->headers
            );
        } elseif (is_array($proxies)) {
            // Trust specific proxies
            $request->setTrustedProxies($proxies, $this->headers);
        }
    }

    /**
     * Get the trusted proxies.
     */
    protected function proxies(): array|string
    {
        // Check if we should trust all proxies (dev/staging only)
        if (config('security.proxies.trust_all', false)) {
            return '*';
        }

        // Get proxies from config
        $configProxies = config('security.proxies.proxies', []);

        // Common cloud provider proxy ranges
        $cloudProxies = $this->getCloudProviderProxies();

        return array_merge($configProxies, $cloudProxies);
    }

    /**
     * Get common cloud provider proxy IP ranges
     */
    protected function getCloudProviderProxies(): array
    {
        $proxies = [];

        // CloudFlare
        if (config('security.proxies.cloudflare', false)) {
            $proxies = array_merge($proxies, [
                '173.245.48.0/20',
                '103.21.244.0/22',
                '103.22.200.0/22',
                '103.31.4.0/22',
                '141.101.64.0/18',
                '108.162.192.0/18',
                '190.93.240.0/20',
                '188.114.96.0/20',
                '197.234.240.0/22',
                '198.41.128.0/17',
                '162.158.0.0/15',
                '104.16.0.0/13',
                '104.24.0.0/14',
                '172.64.0.0/13',
                '131.0.72.0/22',
            ]);
        }

        // AWS ELB
        if (config('security.proxies.aws', false)) {
            // Note: AWS IP ranges change frequently
            // Better to use specific IPs from your load balancer
            $proxies[] = '10.0.0.0/8'; // Private network
        }

        return $proxies;
    }

    /**
     * Get the real client IP address
     */
    public static function getRealIp(Request $request): string
    {
        // Check various headers in order of preference
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // CloudFlare
            'HTTP_X_REAL_IP',             // Nginx
            'HTTP_X_FORWARDED_FOR',       // Standard
            'HTTP_CLIENT_IP',             // Some proxies
            'REMOTE_ADDR',                // Fallback
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server->get($header);

            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                // Si X-Forwarded-For contient plusieurs IPs, prendre la première
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                return $ip;
            }
        }

        return $request->ip() ?? '0.0.0.0';
    }
}
