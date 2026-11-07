<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

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
