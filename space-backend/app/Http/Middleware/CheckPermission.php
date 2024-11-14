<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CheckPermission
{

    public function handle(Request $request, Closure $next, string $permissionName, string $moduleName)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuário não autenticado'], 401);
        }
        
        if (!$user->roles()->whereHas('permissions', function ($query) use ($permissionName, $moduleName) {
            $query->where('permissions.name', $permissionName)
                  ->where('modules.name', $moduleName);
        })->exists()) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        return $next($request);
    }
}


