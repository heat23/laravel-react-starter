<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\RequestIdMiddleware::class);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Add request context to all exception logs
        $exceptions->context(fn () => [
            'request_id' => request()?->attributes?->get('request_id'),
            'user_id' => auth()->id(),
            'url' => request()?->path(),
            'method' => request()?->method(),
        ]);

        // JSON error envelope for API requests
        $exceptions->renderable(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            // Let Inertia handle its own requests
            if ($request->header('X-Inertia')) {
                return null;
            }

            // Let Laravel handle validation errors (already returns correct JSON)
            if ($e instanceof ValidationException) {
                return null;
            }

            $isModelNotFound = $e instanceof ModelNotFoundException
                || ($e instanceof NotFoundHttpException && $e->getPrevious() instanceof ModelNotFoundException);

            $status = match (true) {
                $e instanceof AuthenticationException => 401,
                $e instanceof AuthorizationException => 403,
                $isModelNotFound => 404,
                $e instanceof NotFoundHttpException => 404,
                $e instanceof ThrottleRequestsException => 429,
                $e instanceof HttpException => $e->getStatusCode(),
                default => 500,
            };

            $message = match (true) {
                $e instanceof AuthenticationException => 'Unauthenticated',
                $e instanceof AuthorizationException => 'Forbidden',
                $isModelNotFound => 'Resource not found',
                $e instanceof NotFoundHttpException => 'Not found',
                $e instanceof ThrottleRequestsException => 'Too many requests',
                $e instanceof HttpException => $e->getMessage() ?: 'Error',
                $status === 500 && config('app.debug') => $e->getMessage(),
                default => 'Internal server error',
            };

            return response()->json([
                'message' => $message,
                'errors' => (object) [],
                'status' => $status,
            ], $status);
        });

        // Inertia error pages for web requests
        $exceptions->respond(function ($response, $e, $request) {
            $status = $response->getStatusCode();

            if ($request->expectsJson()) {
                return $response;
            }

            if (! in_array($status, [403, 404, 419, 429, 500, 503])) {
                return $response;
            }

            if (in_array($status, [500, 503]) && app()->isLocal()) {
                return $response;
            }

            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
