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
3. Di Postman, pilih **Import → File** dan impor `postman/Nusa-Goods.postman_collection.json`. Pastikan variabel `baseUrl` bernilai `http://localhost:8000/api`.
4. Jalankan **Daftar produk** (`GET /api/products/`) untuk mencoba pencarian (`q`), pengurutan (`sort`), dan pagination (`per_page`, `page`). Request ini juga memilih `productId` pertama yang tampil jika ID aktif kosong atau sudah tidak ada; **Ringkasan inventaris** dan **Aktivitas terbaru** untuk endpoint baca. Garis miring di akhir URL daftar didukung.
5. Untuk CRUD, jalankan **Buat produk**, pilih gambar pada field `image`, lalu klik **Send**. ID dari respons sukses disimpan otomatis sebagai `productId`; setelah itu gunakan **Detail produk aktif**, **Ubah produk aktif (PATCH sebagian)**, **Ubah produk aktif (PUT penuh)**, **Ubah produk aktif (PUT form-data)**, atau **Ubah foto produk aktif**. Untuk PUT form-data, biarkan Postman mengatur `Content-Type` beserta boundary secara otomatis. Hapus permanen dilakukan dengan **Hapus produk aktif**. Untuk produk yang sudah ada, ubah `productId` di variabel collection ke ID dari **Daftar produk**. ID yang tidak ada memang menghasilkan `404`.
6. Setelah perubahan berhasil, jalankan **Daftar produk** untuk membaca keadaan katalog terbaru di Postman. Postman menampilkan respons terakhir dari request yang dikirim, jadi jalankan ulang request daftar untuk memuat keadaan server terbaru. Dashboard yang sedang terbuka memeriksa perubahan API otomatis setiap 5 detik. Semua client harus memakai `baseUrl` dan database Laravel yang sama.
7. Untuk request manual, gunakan header `Accept: application/json`. Endpoint API mengembalikan error terstruktur tanpa stack trace, termasuk untuk ID yang tidak ada.

**Penting:** `POST http://localhost:8000/api/products` membuat produk baru; `POST http://localhost:8000/api/products/1` tidak didukung dan mengembalikan `405` beserta daftar method yang benar. Gunakan `PUT` atau `PATCH` untuk memperbarui. ID `1` hanya berfungsi jika produk tersebut ada di database. Jika belum ada, buat produk dahulu atau pilih ID dari hasil request **Daftar produk**. Jika Anda memilih reset database, `php artisan migrate:fresh --seed` menghapus semua data lalu memuat enam produk contoh.

Request DELETE harus memakai URL produk tertentu, seperti `DELETE http://localhost:8000/api/products/1`, dan tidak memerlukan Body. Jangan kirim DELETE ke `/api/products/` tanpa ID. Koleksi Postman mempertahankan ID terakhir setelah penghapusan; jalankan **Daftar produk** untuk memilih ID valid berikutnya atau buat produk baru. Request detail, PUT, dan DELETE koleksi dilewati jika ID belum dipilih.

Pada PHP 8.4 atau lebih baru, parser HTTP Symfony/Laravel mendukung PUT/PATCH multipart langsung. Middleware API memberi respons yang jelas pada versi PHP lama yang belum mendukungnya. Untuk PHP tersebut, gunakan request **Ubah foto produk aktif** dengan `POST` dan field `_method=PUT`; PUT JSON tetap didukung.

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
| `GET` | `/api/activities?limit=10` | Riwayat aktivitas tambah, ubah, dan hapus produk (maksimal 50) |

Data produk berisi `title`, `description`, `price` (bilangan bulat Rupiah), dan `stock`. Foto wajib saat membuat produk; format JPG, PNG, atau WEBP, maksimal 2 MB. Untuk mengubah foto melalui form-data, kirim `POST` dengan field `_method=PUT`; foto bersifat opsional untuk perubahan lainnya. Respons validasi menggunakan status `422`, dan ID yang tidak ditemukan menggunakan `404`.

Dashboard berkomunikasi langsung dengan endpoint REST yang sama, sehingga produk dapat ditambah, dicari, diubah, dan dihapus melalui website. Setiap perubahan dicatat pada endpoint aktivitas dan dapat diperiksa dari Postman tanpa menginput ulang data. Riwayat ini menyimpan jenis perubahan, nama produk, waktu, dan nilai field yang berubah.

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
