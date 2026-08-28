/**
 * wishlist.js - Estate Prima
 * Dipakai di wishlist.php.
 * Hapus wishlist tanpa reload halaman (AJAX ke proses-wishlist.php).
 * Card dihapus dari DOM begitu server konfirmasi sukses lewat JSON.
 * CSRF token & URL endpoint diambil dari data-attribute #wishlist-grid
 * (bukan ditulis langsung di JS ini) soalnya keduanya nilai dinamis
 * dari PHP (csrf_token() per session, BASE_URL per lokasi project).
 */
(function () {
    var grid = document.getElementById('wishlist-grid');
    if (!grid) return;

    var csrfToken = grid.getAttribute('data-csrf-token');
    var endpoint = grid.getAttribute('data-endpoint');

    document.querySelectorAll('[data-wishlist-remove]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var propertiId = btn.getAttribute('data-wishlist-remove');
            var card = btn.closest('[data-wishlist-item]');
            btn.disabled = true;

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({ csrf_token: csrfToken, properti_id: propertiId }),
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.sukses && !data.ada_di_wishlist) {
                        card.remove();
                        if (grid.children.length === 0) {
                            grid.classList.add('d-none');
                            var emptyState = document.getElementById('wishlist-empty-state');
                            if (emptyState) emptyState.classList.remove('d-none');
                        }
                    } else {
                        btn.disabled = false;
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    alert('Gagal menghapus wishlist. Coba lagi.');
                });
        });
    });
})();
