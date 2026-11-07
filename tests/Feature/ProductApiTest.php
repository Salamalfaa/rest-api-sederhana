<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_api_root_lists_available_endpoints(): void
    {
        $this->getJson('/api')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Nusa Goods REST API')
            ->assertJsonPath('data.health', url('/api/health'))
            ->assertJsonPath('data.products.list', url('/api/products'))
            ->assertJsonPath('data.products.summary', url('/api/products/summary'))
            ->assertJsonPath('data.products.update.methods.0', 'PUT')
            ->assertJsonPath('data.documentation', url('/api-docs'));
    }

    public function test_products_can_be_created_listed_viewed_updated_and_deleted(): void
    {
        $image = UploadedFile::fake()->image('lampu.jpg');

        $created = $this->post('/api/products', [
            'title' => 'Lampu meja Aira',
            'description' => 'Lampu baca dengan cahaya hangat.',
            'price' => 285000,
            'stock' => 18,
            'image' => $image,
        ], ['Accept' => 'application/json']);

        $created->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Lampu meja Aira')
            ->assertJsonPath('data.price', 285000);

        $productId = $created->json('data.id');
        $storedImage = $created->json('data.image');
        $created->assertJsonPath('data.image_url', url('/storage/'.$storedImage));
        Storage::disk('public')->assertExists($storedImage);

        $this->getJson('/api/products?per_page=5')
            ->assertOk()
            ->assertJsonPath('message', 'List Data Products')
            ->assertJsonPath('data.data.0.id', $productId)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.per_page', 5);

        $this->getJson("/api/products/{$productId}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Lampu meja Aira');

        $this->patchJson("/api/products/{$productId}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product']);

        $this->patchJson("/api/products/{$productId}", [
            'title' => 'Lampu meja Aira Baru',
            'stock' => 12,
        ])->assertOk()
            ->assertJsonPath('data.title', 'Lampu meja Aira Baru')
            ->assertJsonPath('data.stock', 12);

        Storage::disk('public')->assertExists($storedImage);

        $this->getJson('/api/products/summary')
            ->assertOk()
            ->assertJsonPath('data.product_count', 1)
            ->assertJsonPath('data.total_stock', 12)
            ->assertJsonPath('data.inventory_value', 285000 * 12);

        $this->deleteJson("/api/products/{$productId}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null);

        Storage::disk('public')->assertMissing($storedImage);
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_uploaded_image_is_replaced_and_the_old_file_is_removed(): void
    {
        $product = Product::create([
            'title' => 'Vas keramik Pagi',
            'description' => 'Vas keramik buatan tangan.',
            'price' => 320000,
            'stock' => 4,
            'image' => UploadedFile::fake()->image('lama.jpg')->store('products', 'public'),
        ]);
        $oldImage = $product->image;

        $this->post("/api/products/{$product->id}", [
            '_method' => 'PUT',
            'image' => UploadedFile::fake()->image('baru.png'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.id', $product->id);

        $product->refresh();
        $this->assertNotSame($oldImage, $product->image);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_invalid_product_data_returns_field_validation_errors(): void
    {
        $this->postJson('/api/products', [
            'title' => '',
            'description' => '',
            'price' => -10,
            'stock' => -1,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['image', 'title', 'description', 'price', 'stock']);
    }

    public function test_unknown_product_ids_return_not_found(): void
    {
        $this->getJson('/api/products/999999')->assertNotFound();
        $this->patchJson('/api/products/999999', ['title' => 'Produk'])->assertNotFound();
        $this->deleteJson('/api/products/999999')->assertNotFound();
    }
}
