<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remove Finger Print Headers
        $request->headers->remove('X-Powered-By');
        $request->headers->remove('Server');
        $request->headers->remove('x-turbo-charged-by');

        // Add Security Headers
        $response->headers->set('X-Frame-Options', 'deny');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->headers->set('Content-Security-Policy', "default-src 'none'; style-src 'self'; form-action 'self'");
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
