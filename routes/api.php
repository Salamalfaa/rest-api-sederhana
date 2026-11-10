<?php

use App\Http\Controllers\Api\ProductActivityController;
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
                'update' => ['methods' => ['PUT', 'PATCH']],
            ],
            'activities' => url('/api/activities'),
            'documentation' => url('/api-docs'),
        ],
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API tersedia.',
        'data' => ['status' => 'ok'],
    ]);
})->name('api.health');

Route::get('/products/summary', [ProductController::class, 'summary']);
Route::get('/activities', [ProductActivityController::class, 'index']);
Route::apiResource('/products', ProductController::class);
