<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = [
            [
                'title' => 'Lampu meja Aira',
                'description' => 'Lampu meja bernuansa hangat dengan desain minimalis untuk menemani waktu membaca.',
                'price' => 285000,
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Kursi rotan Senja',
                'description' => 'Kursi rotan buatan tangan yang ringan, nyaman, dan cocok untuk sudut santai.',
                'price' => 1250000,
                'stock' => 7,
                'image' => 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Jam tangan Kala',
                'description' => 'Jam tangan klasik dengan detail sederhana untuk gaya sehari-hari.',
                'price' => 740000,
                'stock' => 12,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Sneakers Langkah',
                'description' => 'Sepatu kasual berwarna cerah dengan bantalan empuk untuk aktivitas harian.',
                'price' => 895000,
                'stock' => 4,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Buku catatan Rona',
                'description' => 'Buku catatan sampul linen dengan kertas tebal untuk ide dan rencana baru.',
                'price' => 95000,
                'stock' => 32,
                'image' => 'https://images.unsplash.com/photo-1531346878377-a5be20888e57?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Vas keramik Pagi',
                'description' => 'Vas keramik bertekstur dengan bentuk organik, dibuat dan diglasir secara manual.',
                'price' => 320000,
                'stock' => 0,
                'image' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=900&q=85',
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['title' => $product['title']], $product);
        }
    }
}
