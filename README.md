# Nusa Goods

Nusa Goods adalah aplikasi katalog dan inventaris sederhana berbasis Laravel 12. Proyek ini mengikuti delapan tahap seri RESTful API SantriKoding: instalasi, model dan migration, API Resource, daftar data, tambah, detail, ubah, dan hapus produk. Aplikasi juga menyediakan dashboard berbahasa Indonesia, pencarian, pengurutan, pagination, ringkasan stok, serta unggah foto produk.

## Kebutuhan

- PHP 8.2 atau lebih baru dengan ekstensi PDO SQLite, fileinfo, dan GD
- Composer
- Node.js dan npm

## Menjalankan aplikasi

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Buka `http://localhost:8000`. Untuk pengembangan frontend dengan hot reload, jalankan `npm run dev` di terminal terpisah dari `php artisan serve`.

Database bawaan menggunakan SQLite di `database/database.sqlite`; foto yang diunggah disimpan pada disk publik di `storage/app/public/products` dan dapat diakses melalui `/storage`.

## REST API

Semua endpoint berada di `/api` dan mengembalikan JSON.

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| `GET` | `/api/health` | Status API |
| `GET` | `/api/products` | Daftar produk (`q`, `sort`, `per_page`, `page`) |
| `POST` | `/api/products` | Tambah produk dengan `multipart/form-data` |
| `GET` | `/api/products/{id}` | Detail produk |
| `PUT` / `PATCH` | `/api/products/{id}` | Perbarui data produk |
| `DELETE` | `/api/products/{id}` | Hapus produk dan foto unggahan |
| `GET` | `/api/products/summary` | Jumlah produk, stok, dan nilai inventaris |

Data produk berisi `title`, `description`, `price` (bilangan bulat Rupiah), dan `stock`. Foto wajib saat membuat produk; format JPG, PNG, atau WEBP, maksimal 2 MB. Untuk mengubah foto melalui form-data, kirim `POST` dengan field `_method=PUT`; foto bersifat opsional untuk perubahan lainnya. Respons validasi menggunakan status `422`, dan ID yang tidak ditemukan menggunakan `404`.

Contoh menambahkan produk:

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Accept: application/json" \
  -F "title=Lampu meja Aira" \
  -F "description=Lampu meja dengan cahaya hangat" \
  -F "price=285000" \
  -F "stock=12" \
  -F "image=@lampu.jpg"
```

Dokumentasi endpoint juga tersedia di `http://localhost:8000/api-docs`.

## Pengujian

```bash
php artisan test
```

Tes API memakai database SQLite in-memory dan storage palsu, sehingga tidak mengubah data lokal.
