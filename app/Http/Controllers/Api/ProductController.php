<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sorts = [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'title_asc' => ['title', 'asc'],
        ];
        [$column, $direction] = $sorts[$request->query('sort', 'latest')] ?? $sorts['latest'];
        $perPage = min(max((int) $request->query('per_page', 9), 1), 24);

        $products = $query
            ->orderBy($column, $direction)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return ProductResource::collection($products)->additional([
            'success' => true,
            'message' => 'Daftar produk berhasil dimuat.',
        ]);
    }

    public function summary(): JsonResponse
    {
        $summary = Product::query()
            ->selectRaw('COUNT(*) as product_count')
            ->selectRaw('COALESCE(SUM(stock), 0) as total_stock')
            ->selectRaw('COALESCE(SUM(price * stock), 0) as inventory_value')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan inventaris berhasil dimuat.',
            'data' => [
                'product_count' => (int) $summary->product_count,
                'total_stock' => (int) $summary->total_stock,
                'inventory_value' => (int) $summary->inventory_value,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0', 'max:999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $image = $validated['image'];
        $validated['image'] = $image->store('products', 'public');

        try {
            $product = Product::create($validated);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($validated['image']);
            throw $exception;
        }

        return (new ProductResource($product))
            ->additional(['success' => true, 'message' => 'Produk berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return (new ProductResource($product))->additional([
            'success' => true,
            'message' => 'Detail produk berhasil dimuat.',
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'image' => ['sometimes', 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'title' => ['sometimes', 'required', 'string', 'max:120'],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'price' => ['sometimes', 'required', 'integer', 'min:0', 'max:999999999'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000000'],
        ]);

        if ($validated === []) {
            throw ValidationException::withMessages([
                'product' => 'Kirim setidaknya satu nilai yang ingin diperbarui.',
            ]);
        }

        $oldImage = $product->image;
        $replacedImage = isset($validated['image']);

        if ($replacedImage) {
            $image = $validated['image'];
            $validated['image'] = $image->store('products', 'public');
        }

        try {
            $product->update($validated);
        } catch (\Throwable $exception) {
            if ($replacedImage) {
                Storage::disk('public')->delete($validated['image']);
            }

            throw $exception;
        }

        if ($replacedImage && str_starts_with($oldImage, 'products/')) {
            Storage::disk('public')->delete($oldImage);
        }

        return (new ProductResource($product->refresh()))->additional([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $image = $product->image;
        $product->delete();

        if (str_starts_with($image, 'products/')) {
            Storage::disk('public')->delete($image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
            'data' => null,
        ]);
    }
}
