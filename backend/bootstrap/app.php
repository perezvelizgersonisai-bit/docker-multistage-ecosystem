<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recurso no encontrado.',
                ], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los datos enviados no son válidos.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'El token ha expirado.'], 401);
            }
        });

        $exceptions->render(function (TokenInvalidException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'El token proporcionado no es válido.'], 401);
            }
        });

        $exceptions->render(function (JWTException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'No se encontró un token en la petición.'], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado. Debes iniciar sesión para acceder a este recurso.',
                ], 401);
            }
        });

        // Errores propios de Stripe (tarjeta rechazada, credenciales invalidas, etc.)
        $exceptions->render(function (\Stripe\Exception\ApiErrorException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo procesar el pago: '.$e->getMessage(),
                ], 402);
            }
        });
    })->create();
