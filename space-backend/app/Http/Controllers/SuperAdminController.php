<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Config;

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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'array|exists:roles,id',
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

        if ($request->has('roles')) {
            $user->roles()->attach($request->roles);
        }

        return response()->json(['message' => 'Usuário criado com sucesso!', 'user' => $user], 201);
    }

    public function config(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'custo_tecido' => 'required|numeric',
            'custo_tinta' => 'required|numeric',
            'custo_papel' => 'required|numeric',
            'custo_imposto' => 'required|numeric',
        ]);

        $config = Config::updateOrCreate(
            ['user_id' => $request->user_id],
            [
                'custo_tecido' => $request->custo_tecido,
                'custo_tinta' => $request->custo_tinta,
                'custo_papel' => $request->custo_papel,
                'custo_imposto' => $request->custo_imposto,
                'custo_final' => $request->custo_tecido + $request->custo_tinta + $request->custo_papel + $request->custo_imposto, // Calculando o custo final
            ]
        );

        return response()->json([
            'message' => 'Configuração atualizada ou criada com sucesso!',
            'config' => $config,
        ], 200);
    }

    public function getConfig()
    {
        // Buscar as configurações do usuário
        $config = \App\Models\Config::first();

        if (!$config) {
            return response()->json(['message' => 'Configuração não encontrada para o usuário.'], 404);
        }

        // Retornar as configurações
        return response()->json($config, 200);
    }



    public function deleteModule($id) {
        $module = \App\Models\Module::find($id);

        if (!$module) {
            return response()->json(['message' => 'Módulo não encontrado.'], 404);
        }
        $module->delete();

        return response()->json(['message' => 'Módulo excluído com sucesso.'], 200);
    }

    public function upsertModule() {}

    public function deleteRole($id) {
        $role = \App\Models\Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Papel não encontrado.'], 404);
        }
        $role->delete();

        return response()->json(['message' => 'Papel excluído com sucesso.'], 200);
    }


    public function upsertRole() {}


    public function deleteUserRoles($userId, $roleId) {
        $user = \App\Models\User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'Usuário não encontrado.'], 404);
    }

    // Detach the specified role from the user
    $user->roles()->detach($roleId);

    return response()->json(['message' => 'Permissão do usuário excluída com sucesso.'], 200);
    }


    public function upsertUserRoles(Request $request) {
        $userId = $request->input('user_id');
        $roleIds = $request->input('role_ids');
    
        $user = \App\Models\User::find($userId);
    
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
    
        // Sync the user's roles with the provided role IDs
        $user->roles()->sync($roleIds);
    
        return response()->json(['message' => 'Permissões do usuário atualizadas com sucesso.'], 200);
    }

    public function deleteRoleModule($roleId, $moduleId) {
        $role = \App\Models\Role::find($roleId);

        if (!$role) {
            return response()->json(['message' => 'Papel não encontrado.'], 404);
        }
    
        // Detach the specified role from the user
        $role->modules()->detach($moduleId);
    
        return response()->json(['message' => 'Módulo do papel excluído com sucesso.'], 200); 
    }

    public function upsertRoleModule() {}
}
