import './bootstrap';

const dashboard = document.querySelector('[data-dashboard]');

if (dashboard) {
    const apiUrl = dashboard.dataset.apiUrl;
    const grid = dashboard.querySelector('[data-product-grid]');
    const searchInput = dashboard.querySelector('#product-search');
    const sortInput = dashboard.querySelector('#product-sort');
    const dialog = dashboard.querySelector('[data-product-dialog]');
    const form = dashboard.querySelector('[data-product-form]');
    const alertBox = dashboard.querySelector('[data-form-alert]');
    const submitButton = dashboard.querySelector('[data-submit-button]');
    const submitLabel = dashboard.querySelector('[data-submit-label]');
    const toast = dashboard.querySelector('[data-toast]');
    const imageInput = dashboard.querySelector('#product-image');
    const productsById = new Map();
    let currentPage = 1;
    let lastPage = 1;
    let searchTimer;
    let toastTimer;

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);

    const money = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);

    const showToast = (message, isError = false) => {
        toast.textContent = message;
        toast.classList.toggle('error', isError);
        toast.classList.add('visible');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toast.classList.remove('visible'), 3200);
    };

    const request = async (url, options = {}) => {
        const headers = new Headers(options.headers || {});
        headers.set('Accept', 'application/json');
        headers.set('X-Requested-With', 'XMLHttpRequest');

        if (options.body && !(options.body instanceof FormData)) {
            headers.set('Content-Type', 'application/json');
        }

        const response = await fetch(url, { ...options, headers });
        const payload = await response.json().catch(() => null);

        if (!response.ok) {
            const error = new Error(payload?.message || `Permintaan gagal (${response.status}).`);
            error.status = response.status;
            error.errors = payload?.errors || {};
            throw error;
        }

        return payload;
    };

    const setStat = (name, value) => {
        const element = dashboard.querySelector(`[data-stat="${name}"]`);
        if (element) element.textContent = value;
    };

    const loadSummary = async () => {
        try {
            const payload = await request(`${apiUrl}/products/summary`);
            setStat('products', new Intl.NumberFormat('id-ID').format(payload.data.product_count));
            setStat('stock', new Intl.NumberFormat('id-ID').format(payload.data.total_stock));
            setStat('value', money(payload.data.inventory_value));
        } catch (error) {
            console.error('Ringkasan katalog gagal dimuat.', error);
            showToast('Ringkasan katalog tidak dapat dimuat. Coba muat ulang halaman.', true);
        }
    };

    const productCard = (product) => {
        const stockState = product.stock === 0
            ? ['Habis', 'out-of-stock']
            : product.stock < 5
                ? ['Stok menipis', 'low-stock']
                : ['Tersedia', ''];

        return `<article class="product-card">
            <div class="product-media">
                <img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.title)}" loading="lazy">
                <span class="product-badge ${stockState[1]}">${stockState[0]}</span>
            </div>
            <div class="product-card-body">
                <div class="product-card-top"><h3 title="${escapeHtml(product.title)}">${escapeHtml(product.title)}</h3></div>
                <p class="product-description">${escapeHtml(product.description)}</p>
                <div class="product-card-bottom">
                    <div><div class="product-price">${money(product.price)}</div><div class="product-stock">${new Intl.NumberFormat('id-ID').format(product.stock)} unit tersedia</div></div>
                    <div class="product-actions">
                        <button class="product-action" type="button" data-edit-id="${product.id}" aria-label="Ubah ${escapeHtml(product.title)}" title="Ubah produk">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 5 5 5M4 20l4.3-.9L19 8.4 15.6 5 4.9 15.7 4 20Z"/></svg>
                        </button>
                        <button class="product-action danger" type="button" data-delete-id="${product.id}" aria-label="Hapus ${escapeHtml(product.title)}" title="Hapus produk">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </article>`;
    };

    const renderPagination = (meta) => {
        const pagination = dashboard.querySelector('[data-pagination]');
        const label = dashboard.querySelector('[data-pagination-label]');
        const from = meta.from ?? 0;
        const to = meta.to ?? 0;
        label.textContent = `Menampilkan ${from}–${to} dari ${new Intl.NumberFormat('id-ID').format(meta.total)} produk`;
        pagination.replaceChildren();

        const addButton = (text, page, disabled = false, current = false, labelText = text) => {
            const button = document.createElement('button');
            button.className = `page-button${current ? ' current' : ''}`;
            button.type = 'button';
            button.textContent = text;
            button.setAttribute('aria-label', labelText);
            button.disabled = disabled;
            if (current) button.setAttribute('aria-current', 'page');
            button.addEventListener('click', () => loadProducts(page));
            pagination.append(button);
        };

        addButton('‹', currentPage - 1, currentPage <= 1, false, 'Halaman sebelumnya');
        const firstPage = Math.max(1, currentPage - 2);
        const finalPage = Math.min(lastPage, firstPage + 4);
        for (let page = firstPage; page <= finalPage; page++) {
            addButton(String(page), page, false, page === currentPage, `Halaman ${page}`);
        }
        addButton('›', currentPage + 1, currentPage >= lastPage, false, 'Halaman berikutnya');
    };

    const loadProducts = async (page = 1) => {
        currentPage = page;
        grid.innerHTML = '<div class="loading-state"><span class="spinner"></span><span>Menyiapkan katalog…</span></div>';
        const params = new URLSearchParams({
            page: String(page),
            per_page: '9',
            sort: sortInput.value,
        });
        const query = searchInput.value.trim();
        if (query) params.set('q', query);

        try {
            const payload = await request(`${apiUrl}/products?${params}`);
            const products = payload.data;
            productsById.clear();
            products.forEach((product) => productsById.set(product.id, product));
            dashboard.querySelector('[data-product-count]').textContent = new Intl.NumberFormat('id-ID').format(payload.meta.total);
            lastPage = payload.meta.last_page;
            renderPagination(payload.meta);

            if (products.length === 0) {
                grid.innerHTML = `<div class="empty-state"><strong>${query ? 'Belum ada yang cocok' : 'Katalog masih kosong'}</strong>
                    <p>${query ? 'Coba kata kunci lain, atau tambahkan produk baru.' : 'Tambahkan produk pertama agar koleksi kamu mulai tertata.'}</p>
                    <button class="button button-primary" type="button" data-empty-add>+ Tambah produk</button></div>`;
                return;
            }

            grid.innerHTML = products.map(productCard).join('');
            grid.querySelectorAll('.product-media img').forEach((image) => {
                image.addEventListener('error', () => image.classList.add('image-error'), { once: true });
            });
        } catch (error) {
            console.error('Katalog gagal dimuat.', error);
            dashboard.querySelector('[data-pagination-label]').textContent = 'Katalog gagal dimuat';
            dashboard.querySelector('[data-pagination]').replaceChildren();
            grid.innerHTML = `<div class="error-state"><strong>Katalog belum bisa dibuka</strong><span>${escapeHtml(error.message)}</span>
                <button class="button button-quiet" type="button" data-retry> Coba lagi </button></div>`;
        }
    };

    const clearFormAlert = () => {
        alertBox.hidden = true;
        alertBox.replaceChildren();
    };

    const showFormErrors = (error) => {
        const messages = Object.values(error.errors || {}).flat();
        const items = messages.length ? messages : [error.message];
        alertBox.replaceChildren(...items.map((message) => {
            const item = document.createElement('div');
            item.textContent = message;
            return item;
        }));
        alertBox.hidden = false;
    };

    const openCreateDialog = () => {
        form.reset();
        form.elements.id.value = '';
        dashboard.querySelector('[data-dialog-title]').textContent = 'Produk baru';
        dashboard.querySelector('[data-image-required]').hidden = false;
        dashboard.querySelector('[data-upload-copy]').textContent = 'Pilih foto dari perangkat';
        submitLabel.textContent = 'Simpan produk';
        clearFormAlert();
        dialog.showModal();
        form.elements.title.focus();
    };

    const openEditDialog = (product) => {
        form.reset();
        form.elements.id.value = product.id;
        form.elements.title.value = product.title;
        form.elements.description.value = product.description;
        form.elements.price.value = product.price;
        form.elements.stock.value = product.stock;
        dashboard.querySelector('[data-dialog-title]').textContent = 'Ubah produk';
        dashboard.querySelector('[data-image-required]').hidden = true;
        dashboard.querySelector('[data-upload-copy]').textContent = 'Pilih foto baru (opsional)';
        submitLabel.textContent = 'Simpan perubahan';
        clearFormAlert();
        dialog.showModal();
        form.elements.title.focus();
    };

    dashboard.querySelectorAll('[data-add-product]').forEach((button) => button.addEventListener('click', openCreateDialog));
    dashboard.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
    imageInput.addEventListener('change', () => {
        dashboard.querySelector('[data-upload-copy]').textContent = imageInput.files[0]?.name || 'Pilih foto dari perangkat';
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormAlert();

        if (!form.reportValidity()) return;

        const formData = new FormData(form);
        const id = formData.get('id');
        formData.delete('id');
        if (!imageInput.files.length) formData.delete('image');
        if (id) formData.append('_method', 'PUT');

        submitButton.disabled = true;
        submitLabel.textContent = id ? 'Menyimpan…' : 'Menambahkan…';

        try {
            await request(id ? `${apiUrl}/products/${id}` : `${apiUrl}/products`, {
                method: 'POST',
                body: formData,
            });
            dialog.close();
            showToast(id ? 'Perubahan produk berhasil disimpan.' : 'Produk baru berhasil ditambahkan.');
            await Promise.all([loadProducts(id ? currentPage : 1), loadSummary()]);
        } catch (error) {
            console.error('Produk gagal disimpan.', error);
            showFormErrors(error);
        } finally {
            submitButton.disabled = false;
            submitLabel.textContent = id ? 'Simpan perubahan' : 'Simpan produk';
        }
    });

    grid.addEventListener('click', async (event) => {
        const retry = event.target.closest('[data-retry]');
        if (retry) {
            await loadProducts(currentPage);
            return;
        }

        const add = event.target.closest('[data-empty-add]');
        if (add) {
            openCreateDialog();
            return;
        }

        const editButton = event.target.closest('[data-edit-id]');
        if (editButton) {
            const product = productsById.get(Number(editButton.dataset.editId));
            if (product) openEditDialog(product);
            return;
        }

        const deleteButton = event.target.closest('[data-delete-id]');
        if (!deleteButton) return;

        const product = productsById.get(Number(deleteButton.dataset.deleteId));
        if (!product || !window.confirm(`Hapus "${product.title}" dari katalog?`)) return;

        deleteButton.disabled = true;
        try {
            await request(`${apiUrl}/products/${product.id}`, { method: 'DELETE' });
            showToast('Produk berhasil dihapus.');
            await Promise.all([loadProducts(currentPage), loadSummary()]);
        } catch (error) {
            console.error('Produk gagal dihapus.', error);
            showToast(error.message, true);
            deleteButton.disabled = false;
        }
    });

    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => loadProducts(1), 250);
    });

    sortInput.addEventListener('change', () => loadProducts(1));
    dashboard.querySelector('[data-focus-search]')?.addEventListener('click', () => searchInput.focus());
    dashboard.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchInput.focus();
        }
    });

    loadProducts();
    loadSummary();
}
