<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\CustoBandeiraController;
use App\Http\Controllers\ConfigController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/conta', [ContaController::class, 'index']);
    Route::post('/conta', [ContaController::class, 'store']);
    Route::get('/conta/{id}', [ContaController::class, 'show']);
    Route::put('/conta/{id}', [ContaController::class, 'update']);
    Route::delete('/conta/{id}', [ContaController::class, 'destroy']);
    Route::get('/conta/status/{status}', [ContaController::class, 'listarPorStatus']);
    Route::get('/conta/tipo/{tipo}', [ContaController::class, 'listarPorTipo']);

    // Outras rotas protegidas aqui

    
    // Rota para salvar o cálculo de bandeiras
    Route::post('/custo-bandeira', [CustoBandeiraController::class, 'store']);

    Route::post('configs', [ConfigController::class, 'store']);
    Route::get('configs', [ConfigController::class, 'index']);
    Route::put('configs/{id}', [ConfigController::class, 'update']);
    Route::delete('configs/{id}', [ConfigController::class, 'destroy']);

});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Rotas apenas para admins aqui
});
