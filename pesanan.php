<?php
/**
 * pages/user/pesanan.php - Estate Prima
 * Status transaksi/pengajuan pembelian & sewa milik user yang login.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login();

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT t.id, t.total_harga, t.durasi_sewa, t.tanggal_mulai, t.tanggal_selesai,
            t.metode_bayar, t.bukti_bayar, t.status, t.catatan_admin, t.created_at, t.tipe_transaksi,
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

$label_status = [
    'menunggu'  => ['Menunggu Konfirmasi', 'gray'],
    'diproses'  => ['Sedang Diproses',     'blue'],
    'disetujui' => ['Disetujui',           'green'],
    'ditolak'   => ['Ditolak',             'red'],
    'selesai'   => ['Selesai',             'darkgreen'],
    'lunas'     => ['Lunas',               'darkgreen'],
];

$user = user_login();
$page_title = 'Pesanan Saya — Estate Prima';
$dashboard_sidebar = true;
$dashboard_sidebar_active = 'pesanan';
require_once __DIR__ . '/includes/header.php';
?>
<header class="page-header">
    <div class="container">
        <p class="eyebrow mb-2">Perjalanan Transaksi</p>
        <h1 class="mb-2">Pesanan Saya</h1>
        <p class="lead mb-0">Pantau perkembangan pengajuan transaksi properti Anda.</p>
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
                <h2 class="mt-3 mb-2">Belum ada pengajuan</h2>
                <p class="text-white-50 mb-4">Temukan properti pilihan dan mulai pengajuan Anda.</p>
                <a href="<?= BASE_URL ?>listing.php" class="btn btn-gold">Cari Properti <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        <?php else: ?>
            <div class="order-panel">
                <?php foreach ($daftar_transaksi as $t): ?>
                    <?php 
                        [$label, $warna] = $label_status[$t['status']] ?? ['Selesai', 'darkgreen'];
                        $jenis = $t['tipe_transaksi'] ?? 'jual';
                        $total = $t['total_harga'] ?? $t['harga'];
                    ?>
                    <article class="order-card">
                        <div class="row g-3 align-items-start">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="section-eyebrow mb-0">Pengajuan #<?= $t['id'] ?></span>
                                    <span class="badge-tx <?= $jenis === 'sewa' ? 'badge-tx-sewa' : 'badge-tx-jual' ?>">
                                        <?= strtoupper($jenis) ?>
                                    </span>
                                </div>
                                <h2 class="mb-2"><a href="<?= BASE_URL ?>detail.php?id=<?= $t['properti_id'] ?>" class="text-decoration-none text-reset"><?= htmlspecialchars($t['judul']) ?></a></h2>
                                <p class="order-price mb-2">Rp <?= number_format($total, 0, ',', '.') ?></p>
                                
                                <?php if ($jenis === 'sewa' && !empty($t['tanggal_mulai'])): ?>
                                    <p class="small text-muted mb-3">
                                        <i class="bi bi-clock-history me-1"></i> Sewa: <?= htmlspecialchars($t['tanggal_mulai']) ?> s/d <?= htmlspecialchars($t['tanggal_selesai']) ?> (<?= $t['durasi_sewa'] ?> periode)
                                    </p>
                                <?php endif; ?>

                                <div class="d-flex flex-wrap gap-3 order-meta mt-2">
                                    <span><i class="bi bi-wallet2 me-1"></i><?= $t['metode_bayar'] ? ucwords(str_replace('_', ' ', $t['metode_bayar'])) : '-' ?></span>
                                    <span><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($t['created_at']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="status-pill <?= htmlspecialchars($t['status']) ?>"><i class="bi bi-circle-fill"></i><?= htmlspecialchars($label) ?></span>
                            </div>
                        </div>
                        <?php if (in_array($t['status'], ['selesai', 'lunas'], true)): ?><button type="button" class="btn btn-sm btn-outline-navy mt-3" data-print-receipt="<?= $t['id'] ?>"><i class="bi bi-printer me-1"></i> Cetak Bukti</button><?php endif; ?>
                        <?php if ($t['catatan_admin']): ?>
                            <div class="order-note mt-3"><strong>Catatan Admin:</strong> <i><?= htmlspecialchars($t['catatan_admin']) ?></i></div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php foreach ($daftar_transaksi as $t): if (in_array($t['status'], ['selesai', 'lunas'], true)): ?>
<div class="print-receipt" id="print-receipt-<?= $t['id'] ?>"><div class="receipt-head"><div><h1>ESTATE PRIMA</h1><p>Property & Lifestyle</p></div><div class="receipt-number">BUKTI TRANSAKSI<br><strong>#TRX<?= str_pad($t['id'], 4, '0', STR_PAD_LEFT) ?></strong></div></div><div class="receipt-status">LUNAS / SELESAI</div><div class="receipt-grid"><div><small>PEMOHON</small><strong><?= htmlspecialchars($user['nama']) ?></strong><span><?= htmlspecialchars($user['email'] ?? '') ?></span></div><div><small>PROPERTI</small><strong><?= htmlspecialchars($t['judul']) ?></strong><span><?= ucfirst($t['tipe_transaksi'] ?? 'jual') ?></span></div></div><div class="receipt-total"><small>TOTAL NILAI TRANSAKSI</small><strong>Rp <?= number_format($t['total_harga'] ?? $t['harga'], 0, ',', '.') ?></strong></div><div class="receipt-sign">Terima kasih telah mempercayakan kebutuhan properti Anda kepada Estate Prima.<br><strong>Admin Estate Prima</strong></div></div>
<?php endif; endforeach; ?>
<script src="<?= BASE_URL ?>assets/js/print-receipt.js?v=<?= filemtime(__DIR__ . '/assets/js/print-receipt.js') ?>"></script>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>