# Nusa Goods

Nusa Goods adalah aplikasi katalog dan inventaris sederhana berbasis Laravel 12. Proyek ini mengikuti delapan tahap seri RESTful API SantriKoding: instalasi, model dan migration, API Resource, daftar data, tambah, detail, ubah, dan hapus produk. Aplikasi juga menyediakan dashboard berbahasa Indonesia, pencarian, pengurutan, pagination, ringkasan stok, serta unggah foto produk.

## Kebutuhan

- PHP 8.2 atau lebih baru dengan ekstensi PDO SQLite, fileinfo, dan GD
- Composer
- Node.js dan npm

## Menjalankan pertama kali di Windows

Buka folder proyek di VS Code (`C:\laragon\www\restful-api`), lalu pilih **Terminal → New Terminal**. Pastikan terminal berada di folder proyek. Jalankan perintah berikut satu per satu:

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
npm install
php artisan key:generate
if (-not (Test-Path database\database.sqlite)) { New-Item -ItemType File database\database.sqlite }
php artisan migrate --seed
if (-not (Test-Path public\storage)) { php artisan storage:link }
npm run build
```

`key:generate` dilakukan saat menyiapkan proyek baru atau ketika `APP_KEY` di `.env` masih kosong; jangan mengganti kunci aplikasi yang sudah dipakai. Bawaan `.env.example` menggunakan SQLite, jadi MySQL/XAMPP tidak perlu dinyalakan.

Setelah persiapan selesai, jalankan server:

```powershell
php artisan serve
```

Biarkan terminal tersebut tetap terbuka. Buka `http://localhost:8000` untuk dashboard, `http://localhost:8000/api` untuk melihat indeks endpoint API, dan `http://localhost:8000/api-docs` untuk dokumentasi lengkap. Jika terminal server ditutup, jalankan lagi `php artisan serve`. Untuk hot reload saat mengubah CSS/JavaScript, jalankan `npm run dev` pada terminal VS Code kedua.

Database bawaan menggunakan SQLite di `database/database.sqlite`; foto yang diunggah disimpan pada disk publik di `storage/app/public/products` dan dapat diakses melalui `/storage`.

## Integrasi dan pengujian dengan Postman

1. Buka **Postman Desktop** dari menu Start. Jika belum terpasang, unduh dari [postman.com/downloads](https://www.postman.com/downloads/) atau jalankan `winget install --id Postman.Postman --exact`.
2. Pastikan server Laravel sedang berjalan pada port `8000`.
3. Klik **New → HTTP** (atau buka tab request baru), pilih method **GET**, lalu masukkan `http://localhost:8000/api` untuk melihat indeks endpoint, atau `http://localhost:8000/api/products` untuk daftar produk.
4. Di tab **Headers**, tambahkan `Accept` dengan nilai `application/json`.
5. Klik **Send**. Respons yang benar memiliki status **200 OK**, berisi `"success": true`, `"message": "List Data Products"` dan objek pagination di dalam `"data"`.

Contoh respons lebih lengkap dapat dilihat dengan `?per_page=5`:

```text
GET http://localhost:8000/api/products?per_page=5
```

Database awal diisi enam produk contoh, sehingga daftar pertama berisi lima produk dan halaman berikutnya satu produk; contoh seri SantriKoding yang kosong adalah respons ketika belum ada produk. Untuk melihat hasil kosong tanpa menghapus data, gunakan kata pencarian yang tidak cocok, misalnya `http://localhost:8000/api/products?q=produk-tidak-ada-123`.

Untuk mencoba endpoint lainnya, buat request baru dengan header `Accept: application/json`:

| Method | URL | Pengaturan Postman |
| --- | --- | --- |
| `GET` | `http://localhost:8000/api/health` | Memeriksa koneksi API |
| `GET` | `http://localhost:8000/api/products/1` | Ganti `1` dengan ID produk yang ada |
| `POST` | `http://localhost:8000/api/products` | **Body → form-data**; isi `title`, `description`, `price`, `stock`, dan `image`. Ubah tipe `image` menjadi **File**, pilih foto JPG/PNG/WEBP berukuran maksimal 2 MB. |
| `PATCH` | `http://localhost:8000/api/products/1` | **Body → raw → JSON**, misalnya `{"stock":10}`. Ganti ID dengan ID produk yang ada. |
| `POST` | `http://localhost:8000/api/products/1` | Untuk mengubah foto: **Body → form-data**, tambahkan `_method` bertipe **Text** bernilai `PUT`, lalu isi hanya field yang ingin diubah dan, bila perlu, `image` bertipe **File**. |
| `DELETE` | `http://localhost:8000/api/products/1` | Menghapus data produk secara permanen; ganti ID dan pastikan produk memang ingin dihapus. |

Setelah `POST`, salin nilai `data.id` dari respons untuk dipakai di request detail, ubah, dan hapus. Pembuatan sukses mengembalikan **201 Created**; data tidak valid **422 Unprocessable Content**; ID yang tidak ditemukan **404 Not Found**.

## REST API

Semua endpoint berada di `/api` dan mengembalikan JSON. Buka `http://localhost:8000/api` untuk melihat indeks endpoint yang tersedia beserta tautan dokumentasi.

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| `GET` | `/api/health` | Status API |
| `GET` | `/api/products` | Daftar produk dengan pagination (`q`, `sort`, `per_page`, `page`) |
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
