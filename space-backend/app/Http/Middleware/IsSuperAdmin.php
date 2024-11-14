<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifique se o usuário autenticado possui a role de super-admin
        if ($request->user() && $request->user()->roles()->where('name', 'super-admin')->exists()) {
            return $next($request);
        }

        return response()->json(['message' => 'Acesso negado. Apenas super-admins podem realizar esta ação.'], 403);
    }
}
