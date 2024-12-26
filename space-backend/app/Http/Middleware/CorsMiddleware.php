<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            'http://localhost:3000',
            'https://spacearena.net',
            'https://www.spacearena.net',
            'https://homolog.spacearena.net',
        ];

        $origin = $request->headers->get('Origin');

        if (in_array($origin, $allowedOrigins)) {
            $response = $this->handleCorsRequest($request, $next, $origin); // Passa $origin como argumento
            return $response;
        }

        return $next($request);
    }

    private function handleCorsRequest(Request $request, Closure $next, $origin) // Recebe $origin como argumento
    {
        $response = $next($request);

        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        }

        $response->headers->set('Access-Control-Allow-Origin', $origin); // Agora $origin está definido
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Origin, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}

