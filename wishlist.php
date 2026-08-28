<?php
/**
 * pages/user/wishlist.php - Estate Prima
 * Daftar lengkap wishlist milik user yang login.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login();

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT w.id AS wishlist_id, p.id AS properti_id, p.judul, p.harga, p.kota, p.status, p.gambar_url
     FROM wishlist w
     JOIN properti p ON w.properti_id = p.id
     WHERE w.user_id = ?
     ORDER BY w.created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$daftar_wishlist = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<?php
$user = user_login();
$page_title = 'Wishlist Saya — Estate Prima';
$dashboard_sidebar = true;
$dashboard_sidebar_active = 'wishlist';
require_once __DIR__ . '/includes/header.php';
?>
<style>
    .wishlist-card .thumb { height: 220px; }
    .wishlist-card .card-body { position: relative; }
    .wishlist-card .remove-form { position: relative; z-index: 3; }
    .wishlist-card .remove-form .btn { border-color: #e3c3bd; color: #8a2c22; font-size: 0.78rem; font-weight: 700; }
    .wishlist-card .remove-form .btn:hover { background: #fbeceb; }
</style>

<header class="page-header">
    <div class="container">
        <p class="eyebrow mb-2">Koleksi Pribadi</p>
        <h1 class="mb-2">Wishlist Saya</h1>
        <p class="lead mb-0">Simpan properti yang menarik perhatian Anda untuk ditinjau kembali.</p>
    </div>
</header>

<main class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
            <div>
                <p class="section-eyebrow mb-2">Properti Tersimpan</p>
                <h2 class="section-title mb-0">Pilihan hunian Anda</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard-user.php" class="btn btn-outline-navy"><i class="bi bi-grid-1x2 me-1"></i> Dashboard</a>
                <a href="pesanan.php" class="btn btn-gold"><i class="bi bi-receipt me-1"></i> Pesanan</a>
            </div>
        </div>

        <?php if (empty($daftar_wishlist)): ?>
            <div class="cta-banner text-center">
                <i class="bi bi-heart text-gold fs-1"></i>
                <h2 class="mt-3 mb-2">Wishlist Anda masih kosong</h2>
                <p class="text-white-50 mb-4">Temukan properti yang sesuai dan simpan untuk melihatnya lagi nanti.</p>
                <a href="<?= BASE_URL ?>listing.php" class="btn btn-gold">Cari Properti <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        <?php else: ?>
            <div class="row g-4" id="wishlist-grid">
                <?php foreach ($daftar_wishlist as $w): ?>
                    <div class="col-md-6 col-lg-4" data-wishlist-item="<?= $w['properti_id'] ?>">
                        <div class="property-card wishlist-card">
                            <span class="corner-tick tl"></span>
                            <span class="corner-tick br"></span>
                            <div class="thumb" style="background-image:url('<?= htmlspecialchars($w['gambar_url'] ?: 'https://images.unsplash.com/photo-1568605114967-8130f3a36994') ?>');">
                                <span class="type-tag"><i class="bi bi-heart-fill me-1"></i> Tersimpan</span>
                                <span class="price-tag">Rp <?= number_format($w['harga'], 0, ',', '.') ?></span>
                            </div>
                            <div class="card-body p-3">
                                <h3 class="mb-1"><?= htmlspecialchars($w['judul']) ?></h3>
                                <p class="location mb-3"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($w['kota']) ?></p>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <span class="badge-status <?= htmlspecialchars($w['status']) ?>"><i class="bi bi-circle-fill"></i> <?= ucfirst($w['status']) ?></span>
                                    <div class="remove-form">
                                        <button type="button" class="btn btn-sm" data-wishlist-remove="<?= $w['properti_id'] ?>"><i class="bi bi-trash3 me-1"></i> Hapus</button>
                                    </div>
                                </div>
                                <a href="<?= BASE_URL ?>detail.php?id=<?= $w['properti_id'] ?>" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="cta-banner text-center d-none" id="wishlist-empty-state">
                <i class="bi bi-heart text-gold fs-1"></i>
                <h2 class="mt-3 mb-2">Wishlist Anda masih kosong</h2>
                <p class="text-white-50 mb-4">Temukan properti yang sesuai dan simpan untuk melihatnya lagi nanti.</p>
                <a href="<?= BASE_URL ?>listing.php" class="btn btn-gold">Cari Properti <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Hapus wishlist tanpa reload halaman (AJAX). Card dihapus dari DOM begitu server konfirmasi sukses.
const csrfToken = <?= json_encode(csrf_token()) ?>;
document.querySelectorAll('[data-wishlist-remove]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const propertiId = btn.getAttribute('data-wishlist-remove');
        const card = btn.closest('[data-wishlist-item]');
        btn.disabled = true;

        fetch('<?= BASE_URL ?>proses-wishlist.php', {
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
                    const grid = document.getElementById('wishlist-grid');
                    if (grid && grid.children.length === 0) {
                        grid.classList.add('d-none');
                        document.getElementById('wishlist-empty-state').classList.remove('d-none');
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
</script>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
