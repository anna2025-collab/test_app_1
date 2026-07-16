<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->withHeaders(response()->noContent());
        }

        return $this->withHeaders($next($request));
    }

    private function withHeaders(Response $response): Response
    {
        $response->headers->set('Access-Control-Allow-Origin', env('CORS_ALLOWED_ORIGINS', '*'));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        $response->headers->set('Vary', 'Origin');

        return $response;
    }
}
