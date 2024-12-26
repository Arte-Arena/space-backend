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
            $response = $next($request);

            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Origin, X-Requested-With');

            return $response;
        }

        // Se a origem não for permitida, você pode retornar uma resposta de erro ou simplesmente continuar sem adicionar os cabeçalhos CORS.
        return $next($request);
    }
}