<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\{CheckRole, CorsMiddleware};
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Dotenv\Dotenv;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

$basePath = dirname(__DIR__);

$env = getenv('APP_ENV') ?: 'production';
$envFile = ".env.{$env}";
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

        // Grupo 'api' com Sanctum e CORS
        $middleware->group('api', [
            CorsMiddleware::class,
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                if ($e instanceof NotFoundHttpException) {
                    return response()->json(['error' => 'Não encontrado.'], 404);
                }

                if ($e instanceof AuthenticationException) {
                    return response()->json(['error' => 'Não autenticado'], 401);
                }

                if (config('app.debug')) {
                    throw $e;
                }

                return response()->json(['error' => 'Erro interno do servidor.'], 500);
            }

            throw $e;
        });
    })->create();
