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
    "SELECT t.id, t.metode_bayar, t.bukti_bayar, t.status, t.catatan_admin, t.created_at, t.updated_at,
            u.nama AS nama_user, u.email AS email_user, u.no_hp AS no_hp_user,
            p.id AS properti_id, p.judul, p.harga, p.alamat AS alamat_properti, p.kota AS kota_properti
     FROM transaksi t
     JOIN users u ON t.user_id = u.id
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
$dashboard_sidebar = true;
$dashboard_sidebar_active = 'pesanan';
require_once __DIR__ . '/includes/header.php';
?>

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
                                <?php if ($t['status'] === 'selesai'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-navy mt-2 d-block ms-md-auto" onclick="cetakBukti(<?= $t['id'] ?>)"><i class="bi bi-printer-fill me-1"></i> Cetak Bukti</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($t['catatan_admin']): ?>
                            <div class="order-note mt-3"><strong>Catatan Admin:</strong> <i><?= htmlspecialchars($t['catatan_admin']) ?></i></div>
                        <?php endif; ?>
                    </article>

                    <?php if ($t['status'] === 'selesai'): ?>
                        <div class="print-receipt" id="print-receipt-<?= $t['id'] ?>">
                            <div class="print-sheet">
                                <div class="print-letterhead">
                                    <div>
                                        <div class="brand">ESTATE <span>PRIMA</span></div>
                                        <div class="brand-sub">Bukti Transaksi Properti</div>
                                    </div>
                                    <div class="doc-label">
                                        No. Transaksi
                                        <div class="doc-number">#TRX<?= str_pad((string) $t['id'], 4, '0', STR_PAD_LEFT) ?></div>
                                    </div>
                                </div>
                                <div class="print-status-strip">
                                    <span><i class="bi bi-check-circle-fill me-1"></i> Lunas / Selesai</span>
                                    <span>Dicetak <?= date('d M Y, H:i') ?></span>
                                </div>
                                <div class="print-body">
                                    <p class="print-section-title">Data Pemohon</p>
                                    <div class="print-row"><span class="lbl">Nama</span><span class="val"><?= htmlspecialchars($t['nama_user']) ?></span></div>
                                    <div class="print-row"><span class="lbl">Email</span><span class="val"><?= htmlspecialchars($t['email_user']) ?></span></div>
                                    <div class="print-row"><span class="lbl">No. HP</span><span class="val"><?= htmlspecialchars($t['no_hp_user'] ?: '-') ?></span></div>

                                    <p class="print-section-title">Data Properti</p>
                                    <div class="print-row"><span class="lbl">Properti</span><span class="val"><?= htmlspecialchars($t['judul']) ?></span></div>
                                    <div class="print-row"><span class="lbl">Alamat</span><span class="val"><?= htmlspecialchars($t['alamat_properti']) ?>, <?= htmlspecialchars($t['kota_properti']) ?></span></div>
                                    <div class="print-row"><span class="lbl">Metode Pembayaran</span><span class="val"><?= $t['metode_bayar'] ? ucwords(str_replace('_', ' ', $t['metode_bayar'])) : '-' ?></span></div>

                                    <p class="print-section-title">Waktu</p>
                                    <div class="print-row"><span class="lbl">Diajukan</span><span class="val"><?= htmlspecialchars($t['created_at']) ?></span></div>
                                    <div class="print-row"><span class="lbl">Terakhir Diperbarui</span><span class="val"><?= htmlspecialchars($t['updated_at']) ?></span></div>

                                    <div class="print-total-row">
                                        <span class="lbl">Total Nilai Transaksi</span>
                                        <span class="val">Rp <?= number_format($t['harga'], 0, ',', '.') ?></span>
                                    </div>

                                    <?php if ($t['catatan_admin']): ?>
                                        <p class="print-section-title">Catatan Admin</p>
                                        <div class="print-note-box"><?= nl2br(htmlspecialchars($t['catatan_admin'])) ?></div>
                                    <?php endif; ?>

                                    <div class="print-signature">
                                        <div class="box"><div class="line">Estate Prima — Admin</div></div>
                                    </div>
                                </div>
                                <p class="print-footnote">Dokumen ini dicetak otomatis dari sistem Estate Prima sebagai bukti transaksi telah selesai.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="<?= BASE_URL ?>assets/js/print-receipt.js?v=<?= filemtime(__DIR__ . '/assets/js/print-receipt.js') ?>"></script>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
