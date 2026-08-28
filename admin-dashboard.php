<?php
/**
 * admin-dashboard.php - Estate Prima
 * Dashboard admin: statistik keseluruhan sistem.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin(); // wajib login SEBAGAI ADMIN

// Statistik properti
$stat_properti = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT
        COUNT(*) AS total
     FROM properti"
));

$stat_properti['terjual'] = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM properti WHERE status = 'terjual'"
))['total'];
$stat_properti['tersedia'] = $stat_properti['total'] - $stat_properti['terjual'];

// Statistik user
$total_user = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'user'"
))['total'];

// Statistik transaksi per status
$stat_transaksi = ['menunggu' => 0, 'diproses' => 0, 'disetujui' => 0, 'ditolak' => 0, 'selesai' => 0];
$hasil = mysqli_query($koneksi, "SELECT status, COUNT(*) AS total FROM transaksi GROUP BY status");
while ($row = mysqli_fetch_assoc($hasil)) {
    $stat_transaksi[$row['status']] = $row['total'];
}

// Total nilai properti yang sudah terjual (revenue kasar)
$total_revenue = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(harga), 0) AS total
     FROM (
         SELECT p.id, p.harga
         FROM properti p
         WHERE p.status = 'terjual'
         GROUP BY p.id, p.harga
     ) AS properti_terjual"
))['total'];

// 5 pengajuan transaksi terbaru yang butuh perhatian (masih 'menunggu')
$transaksi_pending = mysqli_fetch_all(mysqli_query($koneksi,
    "SELECT t.id, t.created_at, u.nama AS nama_user, p.judul
     FROM transaksi t
     JOIN users u ON t.user_id = u.id
     JOIN properti p ON t.properti_id = p.id
     WHERE t.status = 'menunggu'
     ORDER BY t.created_at ASC
     LIMIT 5"
), MYSQLI_ASSOC);

$user = user_login();
$page_title = 'Dashboard Admin — Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Dashboard Admin</h1>
            <p class="lead mb-0">Pantau statistik properti, transaksi, dan user dalam satu tampilan.</p>
        </div>
    </div>

    <main class="py-5">
        <div class="container">

            <!-- ============ STATISTIK PROPERTI ============ -->
            <p class="section-eyebrow mb-2">Ringkasan</p>
            <h2 class="section-title mb-4" style="font-size:1.6rem;">Statistik Properti</h2>
            <div class="row g-3 mb-5">
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon-box"><i class="bi bi-houses-fill"></i></div>
                        <div class="num"><?= $stat_properti['total'] ?></div>
                        <div class="lbl">Total Properti</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon-box"><i class="bi bi-check-circle-fill"></i></div>
                        <div class="num"><?= $stat_properti['tersedia'] ?></div>
                        <div class="lbl">Tersedia</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon-box"><i class="bi bi-bag-check-fill"></i></div>
                        <div class="num"><?= $stat_properti['terjual'] ?></div>
                        <div class="lbl">Terjual</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon-box"><i class="bi bi-cash-stack"></i></div>
                        <div class="num" style="font-size:1.3rem;">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
                        <div class="lbl">Total Nilai Terjual</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- ============ STATISTIK USER & TRANSAKSI ============ -->
                <div class="col-lg-7">
                    <p class="section-eyebrow mb-2">Aktivitas</p>
                    <h2 class="section-title mb-4" style="font-size:1.6rem;">Statistik Transaksi</h2>
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="stat-card"><i class="bi bi-hourglass-split d-block mb-1"></i><div class="num"><?= $stat_transaksi['menunggu'] ?></div><div class="lbl">Menunggu</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="stat-card"><i class="bi bi-arrow-repeat d-block mb-1"></i><div class="num"><?= $stat_transaksi['diproses'] ?></div><div class="lbl">Diproses</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="stat-card"><i class="bi bi-check-circle-fill d-block mb-1"></i><div class="num"><?= $stat_transaksi['disetujui'] ?></div><div class="lbl">Disetujui</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="stat-card"><i class="bi bi-x-circle-fill d-block mb-1"></i><div class="num"><?= $stat_transaksi['ditolak'] ?></div><div class="lbl">Ditolak</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="stat-card"><i class="bi bi-check2-circle d-block mb-1"></i><div class="num"><?= $stat_transaksi['selesai'] ?></div><div class="lbl">Selesai</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="stat-card"><i class="bi bi-people-fill d-block mb-1"></i><div class="num"><?= $total_user ?></div><div class="lbl">Total User</div></div>
                        </div>
                    </div>
                </div>

                <!-- ============ PERLU DITINJAU ============ -->
                <div class="col-lg-5">
                    <p class="section-eyebrow mb-2">Butuh Aksi</p>
                    <h2 class="section-title mb-4" style="font-size:1.6rem;">Perlu Ditinjau</h2>

                    <?php if (empty($transaksi_pending)): ?>
                        <div class="alert-estate-success p-3">
                            <i class="bi bi-check-circle-fill me-1"></i> Tidak ada pengajuan yang menunggu.
                        </div>
                    <?php else: ?>
                        <?php foreach ($transaksi_pending as $t): ?>
                            <div class="review-item">
                                <a href="admin-transaksi-detail.php?id=<?= $t['id'] ?>">
                                    <?= htmlspecialchars($t['nama_user']) ?> — <?= htmlspecialchars($t['judul']) ?>
                                </a>
                                <small class="text-muted"><?= $t['created_at'] ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>