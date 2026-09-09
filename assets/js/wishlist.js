// Remove wishlist cards asynchronously without reloading the page.
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('wishlist-grid');
    if (!grid) return;
    grid.addEventListener('submit', async function (event) {
        const form = event.target.closest('.remove-wishlist-form');
        if (!form) return;
        event.preventDefault();
        const button = form.querySelector('.remove-wishlist');
        button.disabled = true;
        const body = new URLSearchParams(new FormData(form));
        try {
            const response = await fetch(grid.dataset.endpoint, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                body
            });
            const result = await response.json();
            if (!response.ok || !result.sukses) throw new Error(result.pesan || 'Gagal menghapus wishlist.');
            const item = form.closest('.wishlist-item');
            if (item) item.remove();
            if (!grid.querySelector('.wishlist-item')) {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-5">Wishlist Anda sekarang kosong.</div>';
            }
        } catch (error) {
            button.disabled = false;
            alert(error.message);
        }
    });
});
