<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // X-Frame-Options: Mencegah serangan Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options: Mencegah MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer-Policy: Melindungi data rujukan URL sensitif
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy: Membatasi fitur browser hanya untuk self (Kamera & GPS diperlukan untuk presensi)
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=(), payment=()');

        // X-XSS-Protection: Perlindungan filter XSS untuk browser legacy
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // HSTS: Strict-Transport-Security jika koneksi HTTPS
        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
