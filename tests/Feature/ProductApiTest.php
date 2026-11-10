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

    public function test_dashboard_and_api_documentation_render(): void
    {
        $this->get('/')->assertOk();
        $this->get('/api-docs')->assertOk();
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

        $this->getJson('/api/products/?per_page=5')
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

    public function test_products_can_be_updated_with_put_and_list_filters(): void
    {
        $product = Product::create([
            'title' => 'Lampu meja',
            'description' => 'Lampu baca.',
            'price' => 100000,
            'stock' => 3,
            'image' => UploadedFile::fake()->image('lampu.jpg')->store('products', 'public'),
        ]);

        $this->putJson("/api/products/{$product->id}", [
            'title' => 'Lampu meja baru',
            'description' => 'Lampu baca yang diperbarui.',
            'price' => 125000,
            'stock' => 8,
        ])->assertOk()
            ->assertJsonPath('data.title', 'Lampu meja baru')
            ->assertJsonPath('data.price', 125000)
            ->assertJsonPath('data.stock', 8);

        $this->getJson('/api/products?q=lampu&sort=price_asc&per_page=1&page=1')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $product->id)
            ->assertJsonPath('data.per_page', 1)
            ->assertJsonPath('data.total', 1);
    }

    public function test_seeded_remote_product_images_keep_their_absolute_urls(): void
    {
        $product = Product::create([
            'title' => 'Produk gambar eksternal',
            'description' => 'Memastikan URL foto seed tidak rusak.',
            'price' => 1000,
            'stock' => 1,
            'image' => 'https://images.example.test/product.jpg',
        ]);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.image_url', 'https://images.example.test/product.jpg');
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

    public function test_api_errors_are_json_even_without_an_accept_header(): void
    {
        $this->get('/api/products/999999')
            ->assertNotFound()
            ->assertExactJson([
                'success' => false,
                'message' => 'Produk dengan ID 999999 tidak ditemukan.',
            ])
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_api_method_errors_explain_supported_methods_without_a_stack_trace(): void
    {
        $this->post('/api/products/1')
            ->assertStatus(405)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Method POST tidak didukung untuk endpoint ini.')
            ->assertJsonPath('allowed_methods.0', 'GET')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_put_and_delete_collection_requests_explain_that_a_product_id_is_required(): void
    {
        foreach (['PUT', 'DELETE'] as $method) {
            $this->call($method, '/api/products/', [], [], [], [
                'HTTP_ACCEPT' => 'application/json',
            ])->assertStatus(405)
                ->assertJsonPath('success', false)
                ->assertJsonPath(
                    'message',
                    "Method {$method} membutuhkan ID produk. Gunakan /api/products/{id}; DELETE tidak memerlukan request body."
                )
                ->assertJsonPath('example', '/api/products/1')
                ->assertJsonMissingPath('trace');
        }
    }

    public function test_product_changes_are_available_in_the_activity_feed(): void
    {
        $created = $this->post('/api/products', [
            'title' => 'Kursi rotan',
            'description' => 'Kursi rotan untuk ruang santai.',
            'price' => 450000,
            'stock' => 6,
            'image' => UploadedFile::fake()->image('kursi.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $productId = $created->json('data.id');

        $this->patchJson("/api/products/{$productId}", ['stock' => 4])
            ->assertOk();

        $this->deleteJson("/api/products/{$productId}")
            ->assertOk();

        $this->getJson('/api/activities?limit=3')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.action', 'deleted')
            ->assertJsonPath('data.0.product_id', $productId)
            ->assertJsonPath('data.1.action', 'updated')
            ->assertJsonPath('data.1.changes.stock.from', 6)
            ->assertJsonPath('data.1.changes.stock.to', 4)
            ->assertJsonPath('data.2.action', 'created')
            ->assertJsonPath('data.2.product_title', 'Kursi rotan');
    }
}
