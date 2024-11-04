<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContaController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Outras rotas protegidas aqui

    Route::post('/conta', [ContaController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Rotas apenas para admins aqui
});
