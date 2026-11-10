<?php

use App\Http\Middleware\RequireMultipartPutSupport;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [RequireMultipartPutSupport::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->expectsJson()
        );
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $productId = $request->route('product');

            return response()->json([
                'success' => false,
                'message' => $productId !== null
                    ? "Produk dengan ID {$productId} tidak ditemukan."
                    : 'Endpoint API tidak ditemukan.',
            ], 404);
        });
        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if (trim($request->path(), '/') === 'api/products'
                && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => "Method {$request->method()} membutuhkan ID produk. Gunakan /api/products/{id}; DELETE tidak memerlukan request body.",
                    'example' => '/api/products/1',
                    'allowed_methods' => ['GET', 'POST'],
                ], 405);
            }

            return response()->json([
                'success' => false,
                'message' => "Method {$request->method()} tidak didukung untuk endpoint ini.",
                'allowed_methods' => array_values(array_filter(array_map(
                    'trim',
                    explode(',', $exception->getHeaders()['Allow'] ?? '')
                ))),
            ], 405);
        });
    })->create();
