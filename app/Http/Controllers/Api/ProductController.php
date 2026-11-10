<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($products) use ($search) {
                $products->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        match ($request->query('sort', 'latest')) {
            'oldest' => $query->oldest(),
            'price_asc' => $query->orderBy('price')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'title_asc' => $query->orderBy('title')->orderByDesc('id'),
            default => $query->latest(),
        };

        $perPage = min(max((int) $request->query('per_page', 9), 1), 50);

        return new ProductResource(true, 'List Data Products', $query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $validated['image'] = $request->file('image')->store('products', 'public');
        $product = DB::transaction(function () use ($validated) {
            $product = Product::create($validated);
            ProductActivity::create([
                'product_id' => $product->id,
                'product_title' => $product->title,
                'action' => 'created',
                'changes' => [],
            ]);

            return $product;
        });

        return (new ProductResource(true, 'Data Product Berhasil Ditambahkan!', $product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new ProductResource(true, 'Detail Data Product!', Product::findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $input = $request->except('_method');
        $validator = Validator::make($input, [
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'title' => ['sometimes', 'required', 'string', 'max:120'],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'price' => ['sometimes', 'required', 'integer', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $validator->after(function ($validator) use ($input) {
            if ($input === []) {
                $validator->errors()->add('product', 'Tambahkan minimal satu perubahan produk.');
            }
        });

        $validated = $validator->validate();
        $previousValues = $product->only(['title', 'description', 'price', 'stock']);
        $oldImage = $product->image;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($validated['image']);
        }

        $product = DB::transaction(function () use ($product, $validated, $previousValues, $oldImage) {
            $product->update($validated);
            $changes = [];

            foreach (array_keys($validated) as $field) {
                if (array_key_exists($field, $previousValues) && $previousValues[$field] != $product->$field) {
                    $changes[$field] = [
                        'from' => $previousValues[$field],
                        'to' => $product->$field,
                    ];
                } elseif ($field === 'image') {
                    $changes['image'] = ['from' => $oldImage, 'to' => $product->image];
                }
            }

            ProductActivity::create([
                'product_id' => $product->id,
                'product_title' => $product->title,
                'action' => 'updated',
                'changes' => $changes,
            ]);

            return $product->refresh();
        });

        if ($oldImage !== $product->image) {
            Storage::disk('public')->delete($oldImage);
        }

        return new ProductResource(true, 'Data Product Berhasil Diubah!', $product);
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);

        $image = $product->image;
        DB::transaction(function () use ($product) {
            ProductActivity::create([
                'product_id' => $product->id,
                'product_title' => $product->title,
                'action' => 'deleted',
                'changes' => [],
            ]);

            $product->delete();
        });

        Storage::disk('public')->delete($image);

        return new ProductResource(true, 'Data Product Berhasil Dihapus!', null);
    }

    public function summary()
    {
        $summary = Product::query()
            ->selectRaw('COUNT(*) as product_count, COALESCE(SUM(stock), 0) as total_stock, COALESCE(SUM(price * stock), 0) as inventory_value')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan inventaris',
            'data' => [
                'product_count' => (int) $summary->product_count,
                'total_stock' => (int) $summary->total_stock,
                'inventory_value' => (int) $summary->inventory_value,
            ],
        ]);
    }
}
