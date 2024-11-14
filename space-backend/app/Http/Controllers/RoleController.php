<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Role, Module, Permission};

class RoleController extends Controller
{
    public function assignPermission(Request $request) {
        $role = Role::find($request->role_id);
        $module = Module::find($request->module_id);
        $permission = Permission::find($request->permission_id);
    
        $role->permissions()->attach($permission->id, ['module_id' => $module->id]);
        
        return response()->json(['message' => 'Permissão atribuída com sucesso']);
    }
}
