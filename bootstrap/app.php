<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\AdminAuthMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\AdminLogMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            \App\Http\Middleware\TrackFrontVisit::class,
        ]);

        $middleware->alias([
            'admin.auth' => AdminAuthMiddleware::class,
            'admin.super' => SuperAdminMiddleware::class,
            'admin.log' => AdminLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }

            if ($request->expectsJson() || $request->is('api/*') || $request->is('admin*') || $request->is('livewire/*')) {
                return null;
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
            } else {
                $status = 500;
            }

            if (in_array($status, [403, 404, 500, 503, 419])) {
                $isDebug = (bool) config('app.debug', false);
                $component = match ($status) {
                    403 => 'Errors/403',
                    404 => 'Errors/404',
                    default => 'Errors/500',
                };

                return inertia($component, [
                    'status' => $status,
                    'message' => $e->getMessage() ?: null,
                    'debug' => $isDebug,
                    'file' => $isDebug ? $e->getFile() : null,
                    'line' => $isDebug ? $e->getLine() : null,
                    'trace' => $isDebug ? $e->getTraceAsString() : null,
                ])->toResponse($request)->setStatusCode($status);
            }

            return null;
        });
    })->create();
