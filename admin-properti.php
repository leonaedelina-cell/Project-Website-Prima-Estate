<?php
/**
 * pages/admin/admin-dashboard.php - Estate Prima
 * Dashboard admin: statistik keseluruhan sistem.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin(); // wajib login SEBAGAI ADMIN

// Statistik properti
$stat_properti = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'tersedia') AS tersedia,
        SUM(status = 'terjual') AS terjual
     FROM properti"
));

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
    "SELECT COALESCE(SUM(harga), 0) AS total FROM properti WHERE status = 'terjual'"
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
$page_title = 'Kelola Properti — Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>
<style>
    .page-header-photo {
        background-image:
            linear-gradient(180deg, rgba(13,31,51,0.72) 0%, rgba(13,31,51,0.6) 55%, rgba(13,31,51,0.94) 100%),
            url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?fm=jpg&q=80&w=2000&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
    }
    .page-header-photo p.lead { color: rgba(255,255,255,0.85); font-size: 1rem; }
    .admin-subnav { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.25rem; }
    .admin-subnav a {
        padding: 0.5rem 1.1rem; border-radius: 3px; font-weight: 700; font-size: 0.85rem;
        color: rgba(255,255,255,0.8); border: 1px solid rgba(255,255,255,0.25); text-decoration: none;
        transition: all 0.2s ease;
    }
    .admin-subnav a:hover { border-color: var(--gold-500); color: var(--gold-300); }
    .admin-subnav a.active { background: var(--gold-grad); border-color: var(--gold-600); color: var(--navy-950); }

    .stat-card {
        background: #fff; border: 1px solid var(--ivory-100); border-radius: 3px;
        padding: 1.5rem; height: 100%;
    }
    .stat-card .icon-box {
        width: 44px; height: 44px; border-radius: 3px;
        background: var(--navy-950); color: var(--gold-300);
        display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        margin-bottom: 1rem;
    }
    .stat-card .num { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.8rem; color: var(--navy-900); }
    .stat-card .lbl { font-size: 0.8rem; color: var(--ink-500); }

    .review-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.9rem 1.1rem; border: 1px solid var(--ivory-100); border-radius: 3px;
        margin-bottom: 0.6rem; background: #fff;
    }
    .review-item a { text-decoration: none; color: var(--navy-900); font-weight: 600; }
    .review-item a:hover { color: var(--gold-600); }
</style>

    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Kelola Properti</h1>
            <p class="lead mb-3">Pantau performa properti dan pengajuan yang perlu segera ditinjau.</p>
            <div class="breadcrumb-estate">
                <a href="<?= BASE_URL ?>index.php">Beranda</a>
                <span class="sep">/</span>
                <span class="current">Kelola Properti</span>
            </div>

            <div class="admin-subnav">
                <a href="admin-dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                <a href="admin-properti.php" class="active"><i class="bi bi-houses-fill me-1"></i> Kelola Properti</a>
                <a href="admin-transaksi.php"><i class="bi bi-receipt me-1"></i> Kelola Transaksi</a>
                <a href="admin-agen.php"><i class="bi bi-person-badge-fill me-1"></i> Kelola Agen</a>
                <a href="admin-pesan.php"><i class="bi bi-envelope-fill me-1"></i> Pesan Kontak</a>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <p class="section-eyebrow mb-2">Ringkasan Sistem</p>
            <h2 class="section-title mb-4" style="font-size:1.6rem;">Ikhtisar Properti Estate Prima</h2>

            <div class="row g-3 mb-5">
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon-box"><i class="bi bi-buildings"></i></div>
                        <div class="num"><?= $stat_properti['total'] ?></div>
                        <div class="lbl">Total Properti</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon-box"><i class="bi bi-house-check-fill"></i></div>
                        <div class="num"><?= $stat_properti['tersedia'] ?></div>
                        <div class="lbl">Tersedia</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon-box"><i class="bi bi-house-heart-fill"></i></div>
                        <div class="num"><?= $stat_properti['terjual'] ?></div>
                        <div class="lbl">Terjual</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon-box"><i class="bi bi-people-fill"></i></div>
                        <div class="num"><?= $total_user ?></div>
                        <div class="lbl">User Terdaftar</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                        <p class="section-eyebrow mb-0">Aktivitas</p>
                        <a href="admin-transaksi.php" class="btn btn-sm btn-outline-navy">
                            Kelola Transaksi <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <h2 class="section-title mb-4" style="font-size:1.4rem;">Statistik Transaksi</h2>

                    <div class="row g-3 mb-3">
                        <?php foreach (['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai'] as $status): ?>
                            <div class="col-6 col-md-4">
                                <div class="stat-card"><div class="num"><?= $stat_transaksi[$status] ?></div><div class="lbl"><?= ucfirst($status) ?></div></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-3" style="background:var(--navy-950); border-radius:3px; color:#fff;">
                        <span class="text-white-50 small d-block mb-1">Total Nilai Properti Terjual</span>
                        <strong class="fs-4" style="font-family:'Fraunces',serif; color:var(--gold-300);">
                            Rp <?= number_format($total_revenue, 0, ',', '.') ?>
                        </strong>
                    </div>
                </div>

                <div class="col-lg-5">
                    <p class="section-eyebrow mb-2">Butuh Aksi</p>
                    <h2 class="section-title mb-4" style="font-size:1.4rem;">Perlu Ditinjau</h2>

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
                                <small class="text-muted"><?= htmlspecialchars($t['created_at']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>