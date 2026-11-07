(function () {
    let activeField = null;
    let currentPage = 1;

    const modal = document.getElementById('admin-media-modal');
    if (!modal) return;

    const grid = document.getElementById('admin-media-grid');
    const pagination = document.getElementById('admin-media-pagination');
    const searchInput = document.getElementById('admin-media-search');
    const uploadInput = document.getElementById('admin-media-upload');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function openModal(field) {
        activeField = field;
        modal.hidden = false;
        loadMedia(1);
    }

    function closeModal() {
        modal.hidden = true;
        activeField = null;
    }

    function selectItem(item) {
        if (!activeField) return;
        const wrap = activeField.closest('.admin-image-field, .admin-field-image, [data-field-type="image"]');
        if (!wrap) return;

        const idInput = wrap.querySelector('.admin-image-id');
        const urlInput = wrap.querySelector('.admin-image-url');
        const preview = wrap.querySelector('.admin-image-preview');

        if (idInput) idInput.value = item.id;
        if (urlInput) urlInput.value = item.url;
        if (preview) {
            preview.src = item.url;
            preview.style.display = item.url ? 'block' : 'none';
        }
        closeModal();
    }

    async function loadMedia(page) {
        currentPage = page;
        const q = searchInput?.value || '';
        const res = await fetch(`/admin/media/api?page=${page}&q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();
        grid.innerHTML = '';

        data.data.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'admin-media-item';
            btn.innerHTML = item.mime?.startsWith('image/')
                ? `<img src="${item.url}" alt="${item.title}"><span>${item.title}</span>`
                : `<div class="admin-media-file">${item.mime}</div><span>${item.title}</span>`;
            btn.addEventListener('click', () => selectItem(item));
            grid.appendChild(btn);
        });

        pagination.innerHTML = '';
        if (data.last_page > 1) {
            for (let p = 1; p <= data.last_page; p++) {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'admin-btn admin-btn-sm ' + (p === data.current_page ? 'admin-btn-primary' : 'admin-btn-secondary');
                b.textContent = p;
                b.addEventListener('click', () => loadMedia(p));
                pagination.appendChild(b);
            }
        }
    }

    document.addEventListener('click', e => {
        if (e.target.closest('.admin-pick-image')) {
            e.preventDefault();
            openModal(e.target.closest('.admin-pick-image'));
        }
        if (e.target.closest('.admin-clear-image')) {
            e.preventDefault();
            const wrap = e.target.closest('[data-field-type="image"]');
            if (!wrap) return;
            wrap.querySelectorAll('.admin-image-id, .admin-image-url').forEach(el => el.value = '');
            const preview = wrap.querySelector('.admin-image-preview');
            if (preview) { preview.src = ''; preview.style.display = 'none'; }
        }
        if (e.target.closest('#admin-media-close') || e.target.classList.contains('admin-media-modal-backdrop')) {
            closeModal();
        }
    });

    searchInput?.addEventListener('input', () => loadMedia(1));

    uploadInput?.addEventListener('change', async () => {
        const file = uploadInput.files?.[0];
        if (!file) return;
        const form = new FormData();
        form.append('file', file);
        form.append('_token', csrf);
        const res = await fetch('/admin/media', {
            method: 'POST',
            body: form,
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        const data = await res.json();
        uploadInput.value = '';
        if (data.success) {
            selectItem(data.item);
        } else {
            loadMedia(currentPage);
        }
    });
})();