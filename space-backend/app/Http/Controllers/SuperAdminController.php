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

}


