<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductActivity;
use Illuminate\Http\Request;

class ProductActivityController extends Controller
{
    public function index(Request $request)
    {
        $limit = min(max((int) $request->query('limit', 10), 1), 50);
        $activities = ProductActivity::query()
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'product_id', 'product_title', 'action', 'changes', 'created_at']);

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas produk terbaru',
            'data' => $activities,
        ]);
    }
}
