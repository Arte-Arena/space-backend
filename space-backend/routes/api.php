<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\CustoBandeiraController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();

        Route::get('/contas', [ContaController::class, 'index']);
    Route::post('/contas', [ContaController::class, 'store']);
    Route::get('/contas/{id}', [ContaController::class, 'show']);
    Route::put('/contas/{id}', [ContaController::class, 'update']);
    Route::delete('/contas/{id}', [ContaController::class, 'destroy']);
    Route::get('/contas/status/{status}', [ContaController::class, 'listarPorStatus']);
    Route::get('/contas/tipo/{tipo}', [ContaController::class, 'listarPorTipo']);
    });
    // Outras rotas protegidas aqui

    
    // Rota para salvar o cálculo de bandeiras
    Route::post('/custo-bandeira', [CustoBandeiraController::class, 'store']);

});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Rotas apenas para admins aqui
});
