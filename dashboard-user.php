<?php
/**
 * pages/user/dashboard-user.php - Estate Prima
 * Dashboard user: statistik ringkas (jumlah wishlist, jumlah pesanan per status)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login(); // wajib login

$user_id = $_SESSION['user_id'];

// Jumlah wishlist
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM wishlist WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$total_wishlist = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

// Jumlah pesanan per status
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT status, COUNT(*) AS total FROM transaksi WHERE user_id = ? GROUP BY status"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$status_list = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai', 'lunas'];
$rekap_status = array_fill_keys($status_list, 0);
while ($row = mysqli_fetch_assoc($hasil)) {
    $rekap_status[$row['status']] = $row['total'];
}
mysqli_stmt_close($stmt);

// 5 aktivitas transaksi terbaru
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT t.id, t.status, t.created_at, p.judul
     FROM transaksi t
     JOIN properti p ON t.properti_id = p.id
     WHERE t.user_id = ?
     ORDER BY t.created_at DESC
     LIMIT 5"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$transaksi_terbaru = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<?php
$user = user_login();
$page_title = 'Dashboard Saya — Estate Prima';
$dashboard_sidebar = true;
$dashboard_sidebar_active = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>
<header class="page-header dashboard-hero">
    <div class="container">
        <p class="eyebrow mb-2">Ruang Pribadi</p>
        <h1 class="mb-2">Halo, <?= htmlspecialchars($_SESSION['nama']) ?>.</h1>
        <p class="lead mb-0">Pantau wishlist dan proses pengajuan properti Anda di satu tempat.</p>
    </div>
</header>

<main class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
            <div>
                <p class="section-eyebrow mb-2">Ringkasan Akun</p>
                <h2 class="section-title mb-0">Aktivitas properti Anda</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="wishlist.php" class="btn btn-outline-navy"><i class="bi bi-heart me-1"></i> Wishlist</a>
                <a href="pesanan.php" class="btn btn-gold"><i class="bi bi-receipt me-1"></i> Pesanan</a>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <?php
            $stat_dashboard = [
                ['icon' => 'bi-heart-fill', 'label' => 'Total Wishlist', 'value' => $total_wishlist],
                ['icon' => 'bi-hourglass-split', 'label' => 'Menunggu', 'value' => $rekap_status['menunggu']],
                ['icon' => 'bi-arrow-repeat', 'label' => 'Diproses', 'value' => $rekap_status['diproses']],
                ['icon' => 'bi-check-circle-fill', 'label' => 'Disetujui', 'value' => $rekap_status['disetujui']],
                ['icon' => 'bi-check2-all', 'label' => 'Selesai', 'value' => $rekap_status['selesai']],
                ['icon' => 'bi-cash-coin', 'label' => 'Lunas', 'value' => $rekap_status['lunas']],
            ];
            foreach ($stat_dashboard as $stat):
            ?>
                <div class="col-6 col-lg">
                    <div class="dashboard-stat">
                        <i class="bi <?= $stat['icon'] ?>"></i>
                        <div class="stat-num mt-3"><?= $stat['value'] ?></div>
                        <div class="stat-label"><?= $stat['label'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="dashboard-panel h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4 gap-3">
                        <h2 class="mb-0">Aktivitas Transaksi Terbaru</h2>
                        <a href="pesanan.php" class="btn btn-sm btn-outline-navy">Lihat semua <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <?php if (empty($transaksi_terbaru)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-gold fs-2"></i>
                            <p class="text-muted mb-0 mt-2">Belum ada transaksi.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($transaksi_terbaru as $t): ?>
                            <div class="activity-row">
                                <span class="activity-icon"><i class="bi bi-house-check"></i></span>
                                <div class="flex-grow-1">
                                    <div class="activity-title"><?= htmlspecialchars($t['judul']) ?></div>
                                    <div class="activity-date"><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($t['created_at']) ?></div>
                                </div>
                                <span class="status-pill"><?= htmlspecialchars(ucfirst($t['status'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="cta-banner h-100 d-flex flex-column justify-content-center">
                    <p class="hero-eyebrow mb-2">Temukan Berikutnya</p>
                    <h2 class="mb-2">Masih mencari hunian?</h2>
                    <p class="text-white-50 mb-4">Jelajahi properti pilihan yang baru ditambahkan ke Estate Prima.</p>
                    <a href="listing.php" class="btn btn-gold align-self-start">Jelajahi Properti <i class="bi bi-arrow-right ms-1"></i></a>
                </section>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
