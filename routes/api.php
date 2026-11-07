<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Nusa Goods REST API',
        'data' => [
            'health' => url('/api/health'),
            'products' => [
                'list' => url('/api/products'),
                'summary' => url('/api/products/summary'),
                'detail' => url('/api/products/{id}'),
                'create' => [
                    'method' => 'POST',
                    'url' => url('/api/products'),
                ],
                'update' => [
                    'methods' => ['PUT', 'PATCH'],
                    'url' => url('/api/products/{id}'),
                ],
                'delete' => [
                    'method' => 'DELETE',
                    'url' => url('/api/products/{id}'),
                ],
            ],
            'documentation' => url('/api-docs'),
        ],
    ]);
})->name('api.index');

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API berjalan dengan baik.',
        'data' => [
            'status' => 'ok',
            'framework' => 'Laravel',
            'version' => app()->version(),
        ],
    ]);
})->name('api.health');

Route::get('/products/summary', [ProductController::class, 'summary'])->name('products.summary');
Route::apiResource('products', ProductController::class);
