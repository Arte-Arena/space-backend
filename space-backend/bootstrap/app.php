<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Dotenv\Dotenv;

$basePath = dirname(__DIR__);

// Determine o ambiente com base na variável de ambiente APP_ENV
$env = getenv('APP_ENV') ?: 'production';

// Defina o caminho do arquivo `.env` baseado no ambiente
$envFile = ".env.{$env}";

// Carregar o arquivo .env específico do ambiente
$dotenv = Dotenv::createImmutable($basePath, $envFile);
$dotenv->safeLoad();

return Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
            'is_super_admin' => App\Http\Middleware\IsSuperAdmin::class,
        ]);

        // Add Sanctum middleware for API requests
        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
