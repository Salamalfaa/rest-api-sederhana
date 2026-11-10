<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f2">
    <title>Dokumentasi API — Nusa Goods</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="Nusa Goods beranda">
                <span class="brand-mark"><span></span><span></span><span></span></span>
                <span><strong>nusa<span>.</span></strong><small>goods studio</small></span>
            </a>
            <div class="sidebar-label">RUANG KERJA</div>
            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="side-link" href="{{ route('dashboard') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg><span>Ikhtisar</span></a>
                <a class="side-link active" href="{{ route('api-docs') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/></svg><span>Dokumentasi API</span><span class="nav-dot"></span></a>
            </nav>
            <div class="sidebar-bottom"><div class="sidebar-note"><div class="note-orbit"><span></span></div><p>Ruang kecil untuk<br><strong>ide yang besar.</strong></p><span class="note-caption">DIBUAT DENGAN SEPENUH HATI</span></div><div class="profile-row"><div class="avatar">N</div><div class="profile-copy"><strong>Nusa Studio</strong><small>Admin toko</small></div><span class="profile-menu">···</span></div></div>
        </aside>
        <main class="main-content docs-main">
            <header class="topbar"><div class="breadcrumb"><a href="{{ route('dashboard') }}">Ruang kerja</a><b>/</b><strong>Dokumentasi API</strong></div><div class="topbar-right"><span class="api-status"><i></i> REST API</span></div></header>
            <section class="docs-hero"><div class="eyebrow"><span class="eyebrow-line"></span> DEVELOPER SPACE</div><h1>API yang simpel.<br><span>Produk yang tertata.</span></h1><p>Kelola katalog Nusa Goods dari aplikasi apa pun. Endpoint REST mengembalikan JSON, tervalidasi, dan siap digunakan.</p><div class="docs-badges"><span><i></i> Laravel 12</span><span>JSON REST</span><span>Tanpa autentikasi</span></div></section>
            <section class="docs-content">
                <div class="docs-section-title"><div><span class="section-kicker">MULAI DI SINI</span><h2>Base URL & respons</h2></div></div>
                <div class="code-panel"><div class="code-panel-head"><span>BASE URL</span><span class="code-live"><i></i> ONLINE</span></div><code>{{ url('/api') }}</code><p>Buka <a href="{{ url('/api') }}" target="_blank" rel="noreferrer"><code>GET /api</code></a> untuk melihat indeks endpoint yang tersedia. Setiap respons memakai <code>application/json</code>. Kirim header <code>Accept: application/json</code> untuk menerima pesan validasi terstruktur.</p></div>

                <div class="docs-section-title endpoint-title"><div><span class="section-kicker">ENDPOINT</span><h2>Operasi produk</h2></div><span class="endpoint-count">7 ROUTE</span></div>
                <div class="endpoint-list">
                    <article class="endpoint-card"><div class="endpoint-method get">GET</div><div class="endpoint-copy"><code>/products</code><p>Daftar produk, mendukung pencarian, pengurutan, dan pagination.</p><div class="endpoint-params"><code>?q=lampu</code><code>&amp;sort=price_asc</code><code>&amp;per_page=9</code></div></div></article>
                    <article class="endpoint-card"><div class="endpoint-method get">GET</div><div class="endpoint-copy"><code>/products/{id}</code><p>Detail satu produk berdasarkan ID. ID yang tidak ditemukan memberi respons 404.</p></div></article>
                    <article class="endpoint-card"><div class="endpoint-method post">POST</div><div class="endpoint-copy"><code>/products</code><p>Buat produk dengan <code>multipart/form-data</code> untuk mengirim foto. POST untuk membuat produk hanya berlaku pada endpoint ini.</p><div class="endpoint-params"><code>image</code><code>title</code><code>description</code><code>price</code><code>stock</code></div></div></article>
                    <article class="endpoint-card"><div class="endpoint-method put">PUT / PATCH</div><div class="endpoint-copy"><code>/products/{id}</code><p>Perbarui sebagian atau seluruh data dengan JSON atau form-data. Untuk unggah foto, gunakan PUT multipart langsung (PHP 8.4+) atau POST multipart dengan field <code>_method=PUT</code> dan <code>image</code>.</p></div></article>
                    <article class="endpoint-card"><div class="endpoint-method delete">DELETE</div><div class="endpoint-copy"><code>/products/{id}</code><p>Hapus produk dan foto yang diunggah bersamanya.</p></div></article>
                    <article class="endpoint-card"><div class="endpoint-method get">GET</div><div class="endpoint-copy"><code>/products/summary</code><p>Ringkasan jumlah produk, total stok, dan nilai inventaris.</p></div></article>
                    <article class="endpoint-card"><div class="endpoint-method get">GET</div><div class="endpoint-copy"><code>/activities</code><p>Riwayat tambah, ubah, dan hapus produk dari dashboard maupun API. Gunakan endpoint ini di Postman untuk melacak perubahan.</p><div class="endpoint-params"><code>?limit=10</code><code>maks. 50</code></div></div></article>
                </div>

                <div class="docs-section-title endpoint-title"><div><span class="section-kicker">CONTOH PAKAI</span><h2>Buat produk baru</h2></div></div>
                <div class="code-panel code-example"><div class="code-panel-head"><span>TERMINAL</span><span>cURL</span></div><pre><code>curl -X POST "{{ url('/api/products') }}" \
  -H "Accept: application/json" \
  -F "title=Lampu meja Aira" \
  -F "description=Lampu meja dengan cahaya hangat" \
  -F "price=285000" \
  -F "stock=12" \
  -F "image=@lampu.jpg"</code></pre></div>

                <div class="docs-note"><span class="docs-note-icon">i</span><div><strong>Validasi & sinkronisasi</strong><p>Foto wajib saat membuat produk dan harus JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB. Harga dan stok berupa bilangan bulat non-negatif. API mengembalikan error JSON untuk validasi maupun ID yang tidak ditemukan. Perubahan API tersimpan di database yang sama dan dashboard yang terbuka memperbarui katalog otomatis setiap 5 detik. Jalankan ulang request daftar di Postman untuk melihat data terbaru.</p></div></div>
                <div class="docs-back"><a class="button button-primary" href="{{ route('dashboard') }}">Kembali ke katalog <span class="button-arrow">↗</span></a><a class="text-link" href="{{ route('api.health') }}" target="_blank" rel="noreferrer">Cek status API <span>↗</span></a></div>
            </section>
            <footer class="page-footer"><span>© 2026 Nusa Goods Studio</span><span>Dirancang untuk tumbuh, dibuat untuk kamu <b>✳</b></span></footer>
        </main>
    </div>
</body>
</html>
