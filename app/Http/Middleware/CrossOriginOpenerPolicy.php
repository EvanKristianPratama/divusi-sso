<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CrossOriginOpenerPolicy
{
    /**
     * Handle an incoming request.
     * 
     * This middleware sets COOP headers to allow Firebase popup authentication
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Allow popups for Firebase Auth
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Cross-Origin-Embedder-Policy', 'unsafe-none');
        
        return $response;
    }
}
