<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $scriptSources = ["'self'"];
        $styleSources = ["'self'"];
        $connectSources = ["'self'"];

        if (app()->isLocal() && is_file(public_path('hot'))) {
            $viteOrigin = $this->safeDevelopmentOrigin((string) config('frontend.vite_dev_server_origin'));

            if ($viteOrigin !== null) {
                $scriptSources[] = $viteOrigin;
                $styleSources[] = $viteOrigin;
                $connectSources[] = $viteOrigin;
                $connectSources[] = preg_replace('/^http/', 'ws', $viteOrigin) ?? $viteOrigin;
            }
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; img-src 'self' data:; script-src ".implode(' ', $scriptSources).'; style-src '.implode(' ', $styleSources)."; font-src 'self'; connect-src ".implode(' ', $connectSources),
        );
        $response->headers->set('Cache-Control', 'no-store, private');

        if ($request->isSecure() && app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function safeDevelopmentOrigin(string $origin): ?string
    {
        $parts = parse_url($origin);

        if (! is_array($parts) || ! in_array($parts['scheme'] ?? null, ['http', 'https'], true)) {
            return null;
        }

        $host = $parts['host'] ?? null;

        if (! is_string($host) || ! in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            return null;
        }

        $formattedHost = str_contains($host, ':') ? "[{$host}]" : $host;
        $port = isset($parts['port']) ? ':'.(int) $parts['port'] : '';

        return $parts['scheme'].'://'.$formattedHost.$port;
    }
}
