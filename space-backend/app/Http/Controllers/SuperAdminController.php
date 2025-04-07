<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\{User, Config, Role, Module, ConfigPrazos};
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    public function getAllUsers()
    {
        return response()->json(User::all());
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
        $user->delete();

        return response()->json(['message' => 'Usuário excluído com sucesso.'], 200);
    }


    public function getAllRoles()
    {
        return response()->json(Role::all());
    }

    public function getAllModules()
    {
        return response()->json(Module::all());
    }

    public function getAllUsersRoles()
    {
        $usersWithRoles = User::with('roles')->get();

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
        $rolesWithModules = Role::with('modules')->get();

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

    public function upsertConfig(Request $request)
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
        $config = Config::first();

        if (!$config) {
            return response()->json(['message' => 'Configuração não encontrada para o usuário.'], 404);
        }

        // Retornar as configurações
        return response()->json($config, 200);
    }


    public function deleteModule($id)
    {
        $module = Module::find($id);

        if (!$module) {
            return response()->json(['message' => 'Módulo não encontrado.'], 404);
        }
        $module->delete();

        return response()->json(['message' => 'Módulo excluído com sucesso.'], 200);
    }

    public function upsertModule(Request $request)
    {
        $moduleId = $request->input('module_id');
        $moduleName = $request->input('module_name');

        $module = Module::find($moduleId);

        if (!$module) {
            $module = Module::create([
                'name' => $moduleName,
            ]);
        } else {
            $module->name = $moduleName;
            $module->save();
        }

        return response()->json(['message' => 'Módulo atualizado ou criado com sucesso!', 'module' => $module], 200);
    }

    public function deleteRole($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Papel não encontrado.'], 404);
        }
        $role->delete();

        return response()->json(['message' => 'Papel excluído com sucesso.'], 200);
    }


    public function upsertRole(Request $request)
    {
        $roleId = $request->input('role_id');
        $roleName = $request->input('role_name');
        $role = Role::find($roleId);
        if (!$role) {
            $role = Role::create([
                'name' => $roleName,
            ]);
        } else {
            $role->name = $roleName;
            $role->save();
        }

        return response()->json(['message' => 'Papel atualizado ou criado com sucesso!', 'role' => $role], 200);
    }


    public function deleteUserRoles($userId, $roleId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $user->roles()->detach($roleId);

        return response()->json(['message' => 'Permissão do usuário excluída com sucesso.'], 200);
    }


    public function upsertUserRoles(Request $request)
    {
        $userId = $request->input('user_id');
        $roleIds = $request->input('role_ids');

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $user->roles()->sync($roleIds);

        return response()->json(['message' => 'Permissões do usuário atualizadas com sucesso.'], 200);
    }

    public function deleteRoleModule($roleId, $moduleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json(['message' => 'Papel não encontrado.'], 404);
        }

        $role->modules()->detach($moduleId);

        return response()->json(['message' => 'Módulo do papel excluído com sucesso.'], 200);
    }

    public function upsertRoleModule(Request $request)
    {
        $roleId = $request->input('role_id');
        $moduleIds = $request->input('module_ids');

        $role = Role::find($roleId);

        if (!$role) {
            return response()->json(['message' => 'Papel não encontrado.'], 404);
        }

        $role->modules()->sync($moduleIds);

        return response()->json(['message' => 'Módulos do papel atualizados com sucesso.'], 200);
    }

    public function getConfigPrazos()
    {
        $diasAntecipacao = ConfigPrazos::first();

        if (!$diasAntecipacao) {
            return response()->json(['message' => 'Dias de antecipação não encontrados.'], 204);
        }

        return response()->json($diasAntecipacao);
    }

    public function upsertConfigPrazos(Request $request)
    {

        Log::info($request->all());

        $diasAntecipacaoArteFinal = $request->input('dias_antecipa_producao_arte_final');
        $diasAntecipacaoImpressao = $request->input('dias_antecipa_producao_impressao');
        $diasAntecipacaoConfeccaoSublimacao = $request->input('dias_antecipa_producao_confeccao_sublimacao');
        $diasAntecipacaoConfeccaoCostura = $request->input('dias_antecipa_producao_confeccao_costura');

        if (!$diasAntecipacaoArteFinal || !$diasAntecipacaoImpressao || !$diasAntecipacaoConfeccaoSublimacao || !$diasAntecipacaoConfeccaoCostura) {
            return response()->json(['message' => 'Dias de antecipação precisam ser informados.'], 422);
        }

        if ($diasAntecipacaoArteFinal < 0 || $diasAntecipacaoImpressao < 0 || $diasAntecipacaoConfeccaoSublimacao < 0 || $diasAntecipacaoConfeccaoCostura < 0) {
            return response()->json(['message' => 'Dias de antecipação não podem ser menor que 0.'], 422);
        }

        if ($diasAntecipacaoArteFinal > 15 || $diasAntecipacaoImpressao > 15 || $diasAntecipacaoConfeccaoSublimacao > 15 || $diasAntecipacaoConfeccaoCostura > 15) {
            return response()->json(['message' => 'Dias de antecipação não podem ser maior que 15.'], 422);
        }

        ConfigPrazos::updateOrCreate(
            ['id' => 1],
            [
                'dias_antecipa_producao_arte_final' => $diasAntecipacaoArteFinal,
                'dias_antecipa_producao_impressao' => $diasAntecipacaoImpressao,
                'dias_antecipa_producao_confeccao_sublimacao' => $diasAntecipacaoConfeccaoSublimacao,
                'dias_antecipa_producao_confeccao_costura' => $diasAntecipacaoConfeccaoCostura
            ]
        );

        Log::info('Configurações de Prazo atualizadas com sucesso.');
    
        return response()->json(['message' => 'Configurações de Prazo atualizadas com sucesso.'], 200);
    }

}
