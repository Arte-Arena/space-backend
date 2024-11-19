<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class SuperAdminController extends Controller
{
    public function getAllUsers()
    {
        return response()->json(\App\Models\User::all());
    }

    public function deleteUser($id)
{
    $user = \App\Models\User::find($id);

    if (!$user) {
        return response()->json(['message' => 'Usuário não encontrado.'], 404);
    }
    $user->delete();

    return response()->json(['message' => 'Usuário excluído com sucesso.'], 200);
}


    public function getAllRoles()
    {
        return response()->json(\App\Models\Role::all());
    }

    public function getAllModules()
    {
        return response()->json(\App\Models\Module::all());
    }

    public function getAllUsersRoles()
    {
        $usersWithRoles = \App\Models\User::with('roles')->get();

        $simplifiedResponse = $usersWithRoles->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                }),
            ];
        });

        return response()->json($simplifiedResponse);
    }

    public function getAllRolesModules()
    {
        $rolesWithModules = \App\Models\Role::with('modules')->get();

        $simplifiedResponse = $rolesWithModules->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'modules' => $role->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'name' => $module->name,
                    ];
                }),
            ];
        });

        return response()->json($simplifiedResponse);
    }

    public function createUser(Request $request)
    {
        // Validação dos dados recebidos
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed', // Supondo que você tenha um campo de confirmação
            'roles' => 'array|exists:roles,id', // Verifica se os IDs das roles existem
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Criação do usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Se houver roles fornecidas, atribui-las ao usuário
        if ($request->has('roles')) {
            $user->roles()->attach($request->roles);
        }

        return response()->json(['message' => 'Usuário criado com sucesso!', 'user' => $user], 201);
    }
}


