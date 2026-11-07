<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f2">
    <title>Nusa Goods — Manajemen Produk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell" data-dashboard data-api-url="{{ url('/api') }}">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="Nusa Goods beranda">
                <span class="brand-mark"><span></span><span></span><span></span></span>
                <span><strong>nusa<span>.</span></strong><small>goods studio</small></span>
            </a>

            <div class="sidebar-label">RUANG KERJA</div>
            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="side-link active" href="{{ route('dashboard') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg>
                    <span>Ikhtisar</span><span class="nav-dot"></span>
                </a>
                <a class="side-link" href="#products" data-focus-search>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/></svg>
                    <span>Produk</span>
                </a>
                <a class="side-link" href="{{ route('api-docs') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/></svg>
                    <span>Dokumentasi API</span><span class="external-arrow">↗</span>
                </a>
            </nav>

            <div class="sidebar-bottom">
                <div class="sidebar-note">
                    <div class="note-orbit"><span></span></div>
                    <p>Ruang kecil untuk<br><strong>ide yang besar.</strong></p>
                    <span class="note-caption">DIBUAT DENGAN SEPENUH HATI</span>
                </div>
                <div class="profile-row">
                    <div class="avatar">N</div>
                    <div class="profile-copy"><strong>Nusa Studio</strong><small>Admin toko</small></div>
                    <span class="profile-menu">···</span>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="breadcrumb"><span>Ruang kerja</span><b>/</b><strong>Ikhtisar</strong></div>
                <div class="topbar-right">
                    <span class="api-status"><i></i> API aktif</span>
                    <a class="icon-button help-button" href="{{ route('api-docs') }}" aria-label="Buka dokumentasi API">?</a>
                </div>
            </header>

            <section class="welcome-section">
                <div>
                    <div class="eyebrow"><span class="eyebrow-line"></span> {{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
                    <h1>Halo, Nusa Studio <span class="wave">✳</span></h1>
                    <p class="welcome-copy">Semua yang kamu jual, tertata dalam satu tempat.</p>
                </div>
                <button class="button button-primary" type="button" data-add-product>
                    <span class="button-plus">+</span> Tambah produk
                </button>
            </section>

            <section class="stats-grid" aria-label="Ringkasan inventaris">
                <article class="stat-card stat-products">
                    <div class="stat-top"><span class="stat-label">TOTAL PRODUK</span><span class="stat-icon mint">▦</span></div>
                    <div class="stat-value" data-stat="products">—</div>
                    <div class="stat-foot"><span class="stat-indicator"></span> Item di katalog</div>
                    <div class="stat-watermark">01</div>
                </article>
                <article class="stat-card stat-stock">
                    <div class="stat-top"><span class="stat-label">TOTAL STOK</span><span class="stat-icon peach">▤</span></div>
                    <div class="stat-value" data-stat="stock">—</div>
                    <div class="stat-foot"><span class="stat-indicator orange"></span> Unit siap dikirim</div>
                    <div class="stat-watermark">02</div>
                </article>
                <article class="stat-card stat-value-card">
                    <div class="stat-top"><span class="stat-label">NILAI INVENTARIS</span><span class="stat-icon lilac">↗</span></div>
                    <div class="stat-value stat-currency" data-stat="value">—</div>
                    <div class="stat-foot"><span class="stat-indicator purple"></span> Harga × jumlah stok</div>
                    <div class="stat-watermark">03</div>
                </article>
            </section>

            <section class="catalog-section" id="products">
                <div class="section-heading">
                    <div>
                        <div class="section-kicker">KOLEKSI PILIHAN</div>
                        <h2>Produk kamu <span class="product-count" data-product-count>0</span></h2>
                    </div>
                    <a class="text-link" href="{{ route('api-docs') }}">Jelajahi API <span>↗</span></a>
                </div>

                <div class="catalog-toolbar">
                    <label class="search-box" for="product-search">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
                        <input id="product-search" type="search" placeholder="Cari nama atau deskripsi produk…" autocomplete="off">
                        <kbd>⌘ K</kbd>
                    </label>
                    <label class="sort-box" for="product-sort">
                        <span>Urutkan:</span>
                        <select id="product-sort">
                            <option value="latest">Terbaru</option>
                            <option value="oldest">Terlama</option>
                            <option value="price_asc">Harga terendah</option>
                            <option value="price_desc">Harga tertinggi</option>
                            <option value="title_asc">Nama A–Z</option>
                        </select>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                    </label>
                </div>

                <div class="product-grid" data-product-grid aria-live="polite">
                    <div class="loading-state"><span class="spinner"></span><span>Menyiapkan katalog…</span></div>
                </div>
                <div class="catalog-footer">
                    <span data-pagination-label>Memuat katalog</span>
                    <div class="pagination" data-pagination></div>
                </div>
            </section>

            <footer class="page-footer"><span>© 2026 Nusa Goods Studio</span><span>Dirancang untuk tumbuh, dibuat untuk kamu <b>✳</b></span></footer>
        </main>
    </div>

    <dialog class="product-dialog" data-product-dialog aria-labelledby="dialog-title">
        <form class="dialog-form" data-product-form>
            <div class="dialog-head">
                <div><span class="section-kicker">KATALOG NUSA GOODS</span><h2 id="dialog-title" data-dialog-title>Produk baru</h2></div>
                <button type="button" class="icon-button close-dialog" data-close-dialog aria-label="Tutup">×</button>
            </div>
            <p class="dialog-intro">Isi detail produk agar katalog kamu selalu rapi dan mudah ditemukan.</p>
            <div class="form-alert" data-form-alert role="alert" hidden></div>
            <input type="hidden" name="id">
            <label class="field-label" for="product-title">Nama produk <span>*</span></label>
            <input class="field-input" id="product-title" name="title" type="text" maxlength="120" placeholder="Contoh: Lampu meja Aira" required>
            <label class="field-label" for="product-description">Deskripsi <span>*</span></label>
            <textarea class="field-input field-textarea" id="product-description" name="description" rows="3" maxlength="2000" placeholder="Ceritakan sedikit tentang produk ini…" required></textarea>
            <div class="field-row">
                <div><label class="field-label" for="product-price">Harga (Rupiah) <span>*</span></label><div class="input-prefix"><span>Rp</span><input class="field-input" id="product-price" name="price" type="number" min="0" max="999999999" step="1" placeholder="125000" required></div></div>
                <div><label class="field-label" for="product-stock">Jumlah stok <span>*</span></label><input class="field-input" id="product-stock" name="stock" type="number" min="0" max="1000000" step="1" placeholder="12" required></div>
            </div>
            <label class="field-label" for="product-image">Foto produk <span data-image-required>*</span></label>
            <label class="upload-field" for="product-image">
                <span class="upload-icon">↑</span><span><strong data-upload-copy>Pilih foto dari perangkat</strong><small>JPG, PNG, atau WEBP · Maks. 2 MB</small></span><span class="upload-action">Pilih file</span>
            </label>
            <input class="visually-hidden" id="product-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            <div class="dialog-actions"><button type="button" class="button button-quiet" data-close-dialog>Batal</button><button class="button button-primary" type="submit" data-submit-button><span data-submit-label>Simpan produk</span><span class="button-arrow">↗</span></button></div>
        </form>
    </dialog>

    <div class="toast" data-toast role="status" aria-live="polite"></div>
</body>
</html>
