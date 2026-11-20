<?php

use App\Exceptions\Wallet\WalletException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Errores de negocio de la wallet (monto inválido, saldo insuficiente):
        // no son fallos del servidor, así que no se registran en el log como
        // errores. Se devuelven al usuario como error de validación (422 en JSON,
        // o redirect atrás con el mensaje en $errors para formularios Blade).
        $exceptions->dontReport(WalletException::class);
        $exceptions->render(function (WalletException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['wallet' => $e->getMessage()]);
        });
    })->create();
