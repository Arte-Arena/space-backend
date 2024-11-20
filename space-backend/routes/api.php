<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, SuperAdminController, ContaController, CustoBandeiraController, PedidoController, ConfigController };
use App\Models\User;

// Rotas públicas
//Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas privadas para qualquer usuário logado
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/{user}', function (User $user) {
        return $user->load('roles');
    });
    Route::get('/conta', [ContaController::class, 'index']);
    Route::post('/conta', [ContaController::class, 'store']);
    Route::get('/conta/{id}', [ContaController::class, 'show']);
    Route::put('/conta/{id}', [ContaController::class, 'update']);
    Route::delete('/conta/{id}', [ContaController::class, 'destroy']);
    Route::get('/conta/status/{status}', [ContaController::class, 'listarPorStatus']);
    Route::get('/conta/tipo/{tipo}', [ContaController::class, 'listarPorTipo']);

    Route::get('/pedido', [PedidoController::class, 'index']);
    Route::post('/pedido', [PedidoController::class, 'store']);
    Route::get('/pedido/{id}', [PedidoController::class, 'show']);
    Route::put('/pedido/{id}', [PedidoController::class, 'update']);
    Route::delete('/pedido/{id}', [PedidoController::class, 'destroy']);

    // Outras rotas protegidas aqui

    
    // Rota para salvar o cálculo de bandeiras
    Route::post('/custo-bandeira', [CustoBandeiraController::class, 'store']);

    Route::post('configs', [ConfigController::class, 'store']);
    Route::get('configs', [ConfigController::class, 'index']);
    Route::put('configs/{id}', [ConfigController::class, 'update']);
    Route::delete('configs/{id}', [ConfigController::class, 'destroy']);

});

//Rotas para usuários logados definidos com role super-admin
Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    // Rotas apenas para admins aqui
    Route::get('/super-admin/get-all-users', [SuperAdminController::class, 'getAllUsers']);
    Route::get('/super-admin/get-all-roles', [SuperAdminController::class, 'getAllRoles']);
    Route::get('/super-admin/get-all-modules', [SuperAdminController::class, 'getAllModules']);
    Route::get('/super-admin/get-all-users-roles', [SuperAdminController::class, 'getAllUsersRoles']);
    Route::get('/super-admin/get-all-roles-modules', [SuperAdminController::class, 'getAllRolesModules']);
    Route::post('/super-admin/create-user', [SuperAdminController::class, 'createUser']);
    Route::delete('/super-admin/delete-user/{id}', [SuperAdminController::class, 'deleteUser']);
    Route::put('/super-admin/upsert-config', [SuperAdminController::class, 'upsertConfig']);
    Route::get('/super-admin/get-config', [SuperAdminController::class, 'getConfig']);

    Route::delete('/super-admin/delete-module/{id}', [SuperAdminController::class, 'deleteModule']);
    Route::put('/super-admin/upsert-module', [SuperAdminController::class, 'upsertModule']);
    Route::delete('/super-admin/delete-role/{id}', [SuperAdminController::class, 'deleteRole']);
    Route::put('/super-admin/upsert-role', [SuperAdminController::class, 'upsertRole']);
    Route::delete('/super-admin/delete-user-roles/{userId}/{roleId}', [SuperAdminController::class, 'deleteUserRoles']);
    Route::put('/super-admin/upsert-user-roles', [SuperAdminController::class, 'upsertUserRoles']);
    Route::delete('/super-admin/delete-role-module/{roleId}/{moduleId}', [SuperAdminController::class, 'deleteRoleModule']);
    Route::put('/super-admin/upsert-role-module', [SuperAdminController::class, 'upsertRoleModule']);


});

//Rotas para usuários logados definidos com role admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Rotas apenas para admins aqui
    Route::get('/teste', [ContaController::class, 'index']);
});



