<?php
/**
 * pages/user/pesanan.php - Estate Prima
 * Status transaksi/pengajuan pembelian milik user yang login.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login();

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT t.id, t.metode_bayar, t.bukti_bayar, t.status, t.catatan_admin, t.created_at,
            p.id AS properti_id, p.judul, p.harga
     FROM transaksi t
     JOIN properti p ON t.properti_id = p.id
     WHERE t.user_id = ?
     ORDER BY t.created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$daftar_transaksi = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Label + warna sederhana per status, biar gampang dibedain
$label_status = [
    'menunggu'  => ['Menunggu Konfirmasi', 'gray'],
    'diproses'  => ['Sedang Diproses',     'blue'],
    'disetujui' => ['Disetujui',           'green'],
    'ditolak'   => ['Ditolak',             'red'],
    'selesai'   => ['Selesai',             'darkgreen'],
];
?>
<?php
$user = user_login();
$page_title = 'Pesanan Saya — Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>
<style>
    .order-panel { background: #fff; border: 1px solid var(--ivory-100); border-radius: 3px; overflow: hidden; }
    .order-card { padding: 1.5rem; position: relative; border-bottom: 1px dashed var(--ivory-100); }
    .order-card:last-child { border-bottom: 0; }
    .order-card h2 { font-family: 'Fraunces', serif; font-size: 1.35rem; color: var(--navy-900); }
    .order-price { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 600; color: var(--navy-900); }
    .order-meta { color: var(--ink-500); font-size: 0.86rem; }
    .order-note { background: var(--ivory-50); border-left: 3px solid var(--gold-500); padding: 0.75rem 1rem; color: var(--ink-500); font-size: 0.86rem; }
    .status-pill { display: inline-flex; align-items: center; gap: 0.35rem; border-radius: 2px; padding: 0.35rem 0.7rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; }
    .status-pill.menunggu { background: #f1eee5; color: #766f60; }
    .status-pill.diproses { background: #e8f0f8; color: #2c4f74; }
    .status-pill.disetujui, .status-pill.selesai { background: #eaf4ec; color: #1e5c2c; }
    .status-pill.ditolak { background: #fbeceb; color: #8a2c22; }
</style>

<header class="page-header">
    <div class="container">
        <p class="eyebrow mb-2">Perjalanan Pembelian</p>
        <h1 class="mb-2">Pesanan Saya</h1>
        <p class="lead mb-0">Pantau perkembangan pengajuan pembelian properti Anda.</p>
    </div>
</header>

<main class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
            <div>
                <p class="section-eyebrow mb-2">Riwayat Pengajuan</p>
                <h2 class="section-title mb-0">Pesanan dan statusnya</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard-user.php" class="btn btn-outline-navy"><i class="bi bi-grid-1x2 me-1"></i> Dashboard</a>
                <a href="wishlist.php" class="btn btn-gold"><i class="bi bi-heart me-1"></i> Wishlist</a>
            </div>
        </div>

        <?php if (empty($daftar_transaksi)): ?>
            <div class="cta-banner text-center">
                <i class="bi bi-receipt text-gold fs-1"></i>
                <h2 class="mt-3 mb-2">Belum ada pengajuan pembelian</h2>
                <p class="text-white-50 mb-4">Temukan properti pilihan dan mulai pengajuan pembelian Anda.</p>
                <a href="<?= BASE_URL ?>listing.php" class="btn btn-gold">Cari Properti <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        <?php else: ?>
            <div class="order-panel">
                <?php foreach ($daftar_transaksi as $t): ?>
                    <?php [$label, $warna] = $label_status[$t['status']]; ?>
                    <article class="order-card">
                        <span class="corner-tick tl"></span>
                        <span class="corner-tick br"></span>
                        <div class="row g-3 align-items-start">
                            <div class="col-md-8">
                                <p class="section-eyebrow mb-2">Pengajuan #<?= $t['id'] ?></p>
                                <h2 class="mb-2"><a href="<?= BASE_URL ?>detail.php?id=<?= $t['properti_id'] ?>" class="text-decoration-none text-reset"><?= htmlspecialchars($t['judul']) ?></a></h2>
                                <p class="order-price mb-3">Rp <?= number_format($t['harga'], 0, ',', '.') ?></p>
                                <div class="d-flex flex-wrap gap-3 order-meta">
                                    <span><i class="bi bi-wallet2 me-1"></i><?= $t['metode_bayar'] ? ucwords(str_replace('_', ' ', $t['metode_bayar'])) : '-' ?></span>
                                    <span><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($t['created_at']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="status-pill <?= htmlspecialchars($t['status']) ?>"><i class="bi bi-circle-fill"></i><?= htmlspecialchars($label) ?></span>
                            </div>
                        </div>
                        <?php if ($t['catatan_admin']): ?>
                            <div class="order-note mt-3"><strong>Catatan Admin:</strong> <i><?= htmlspecialchars($t['catatan_admin']) ?></i></div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
