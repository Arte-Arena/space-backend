<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, SuperAdminController, ContaController, CustoBandeiraController, PedidoController, ConfigController };
use App\Models\User;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/validate-token', [AuthController::class, 'validateToken']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/{user}', function (User $user) {
        return $user->load('roles');
    });
});

Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::post('/super-admin/create-user', [SuperAdminController::class, 'createUser']);
    Route::get('/super-admin/get-all-users', [SuperAdminController::class, 'getAllUsers']);
    Route::get('/super-admin/get-all-roles', [SuperAdminController::class, 'getAllRoles']);
    Route::get('/super-admin/get-all-modules', [SuperAdminController::class, 'getAllModules']);
    Route::get('/super-admin/get-all-users-roles', [SuperAdminController::class, 'getAllUsersRoles']);
    Route::get('/super-admin/get-all-roles-modules', [SuperAdminController::class, 'getAllRolesModules']);
    Route::delete('/super-admin/delete-user/{id}', [SuperAdminController::class, 'deleteUser']);
    Route::delete('/super-admin/delete-module/{id}', [SuperAdminController::class, 'deleteModule']);
    Route::put('/super-admin/upsert-module', [SuperAdminController::class, 'upsertModule']);
    Route::delete('/super-admin/delete-role/{id}', [SuperAdminController::class, 'deleteRole']);
    Route::put('/super-admin/upsert-role', [SuperAdminController::class, 'upsertRole']);
    Route::delete('/super-admin/delete-user-roles/{userId}/{roleId}', [SuperAdminController::class, 'deleteUserRoles']);
    Route::put('/super-admin/upsert-user-roles', [SuperAdminController::class, 'upsertUserRoles']);
    Route::delete('/super-admin/delete-role-module/{roleId}/{moduleId}', [SuperAdminController::class, 'deleteRoleModule']);
    Route::put('/super-admin/upsert-role-module', [SuperAdminController::class, 'upsertRoleModule']);
    Route::get('/super-admin/get-config', [SuperAdminController::class, 'getConfig']);
    Route::put('/super-admin/upsert-config', [SuperAdminController::class, 'upsertConfig']);
});

Route::middleware(['auth:sanctum', 'role:super-admin,admin'])->group(function () {
    Route::post('/custo-bandeira', [CustoBandeiraController::class, 'upsertCustoBandeira']);
    Route::get('/conta', [ContaController::class, 'getAllContas']);
    Route::get('/conta/{id}', [ContaController::class, 'getConta']);
    Route::put('/conta', [ContaController::class, 'upsertConta']);
    Route::delete('/conta/{id}', [ContaController::class, 'deleteConta']);
    // Route::get('/conta/status/{status}', [ContaController::class, 'listarPorStatus']);
    // Route::get('/conta/tipo/{tipo}', [ContaController::class, 'listarPorTipo']);
});

Route::middleware(['auth:sanctum', 'role:super-admin,admin,vendedor'])->group(function () {
    Route::get('/pedido', [PedidoController::class, 'getAllPedidos']);
    Route::put('/pedido', [PedidoController::class, 'upsertPedido']);
    // Route::get('/pedido/{id}', [PedidoController::class, 'show']);
    // Route::put('/pedido/{id}', [PedidoController::class, 'update']);
    // Route::delete('/pedido/{id}', [PedidoController::class, 'destroy']);
});


Route::middleware(['auth:sanctum', 'role:super-admin,admin,vendedor,designer,producao'])->group(function () {
    Route::get('/impressao', [PedidoController::class, 'index']);
});



