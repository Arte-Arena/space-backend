<?php
namespace App\Http\Controllers;

use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function getUsersByRole(Request $request)
    {
        $roleName = $request->query('role');
        
        if (!$roleName) {
            return response()->json(['message' => 'Role não fornecida.'], 400);
        }

        $roles = [
            1 => 'Super Admin',
            2 => 'TI',
            3 => 'Admin',
            4 => 'Lider',
            5 => 'Colaborador',
            6 => 'Designer',
            7 => 'Designer Coordenador',
            8 => 'Produção',
            9 => 'Comercial',
        ];

        if (!in_array($roleName, $roles)) {
            return response()->json(['message' => 'Role não encontrada.'], 404);
        }

        $roleId = array_search($roleName, $roles);

        $userRole = RoleUser::where('role_id', $roleId)->pluck('user_id');

        $users = User::whereIn('id', $userRole)->get();

        return response()->json($users, 200);
    }
}
