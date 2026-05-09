<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return match (true) {
                    $e instanceof AuthenticationException => response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated',
                        'data'    => null,
                    ], 401),
                    $e instanceof AuthorizationException => response()->json([
                        'success' => false,
                        'message' => 'Forbidden',
                        'data'    => null,
                    ], 403),
                    $e instanceof ModelNotFoundException => response()->json([
                        'success' => false,
                        'message' => 'Not found',
                        'data'    => null,
                    ], 404),
                    $e instanceof ValidationException => response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'data'    => $e->errors(),
                    ], 422),
                    default => response()->json([
                        'success' => false,
                        'message' => 'Server error',
                        'data'    => null,
                    ], 500),
                };
            }

            return null; // Fall through to default Laravel/Inertia handling
        });
    })->create();
