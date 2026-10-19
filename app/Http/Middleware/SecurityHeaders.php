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

        // Stops other websites from showing Connectly inside a hidden frame (clickjacking).
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Stops the browser from guessing file types, so an upload can never run as a script.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Shares only the site name (not the full address) when a user follows an outside link.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Connectly never needs the camera, microphone or location.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
