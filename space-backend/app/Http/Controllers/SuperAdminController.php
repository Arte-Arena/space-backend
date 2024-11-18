<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function getAllUsers()
    {
        return response()->json(\App\Models\User::all());
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
}
