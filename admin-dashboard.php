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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Estate Prima (data test)</title>
</head>
<body>
    <p>
        <a href="<?= BASE_URL ?>index.php">&larr; Homepage</a> |
        <a href="admin-properti.php">Kelola Properti</a> |
        <a href="admin-transaksi.php">Kelola Transaksi</a> |
        <a href="admin-agen.php">Kelola Agen</a> |
        <a href="admin-pesan.php">Pesan Kontak</a> |
        <a href="<?= BASE_URL ?>logout.php">Logout</a>
    </p>

    <h1>Dashboard Admin</h1>

    <h2>Statistik Properti</h2>
    <ul>
        <li>Total Properti: <b><?= $stat_properti['total'] ?></b></li>
        <li>Tersedia: <b><?= $stat_properti['tersedia'] ?></b></li>
        <li>Terjual: <b><?= $stat_properti['terjual'] ?></b></li>
        <li>Total Nilai Terjual: <b>Rp <?= number_format($total_revenue, 0, ',', '.') ?></b></li>
    </ul>

    <h2>Statistik User</h2>
    <ul>
        <li>Total User Terdaftar: <b><?= $total_user ?></b></li>
    </ul>

    <h2>Statistik Transaksi</h2>
    <ul>
        <li>Menunggu: <b><?= $stat_transaksi['menunggu'] ?></b></li>
        <li>Diproses: <b><?= $stat_transaksi['diproses'] ?></b></li>
        <li>Disetujui: <b><?= $stat_transaksi['disetujui'] ?></b></li>
        <li>Ditolak: <b><?= $stat_transaksi['ditolak'] ?></b></li>
        <li>Selesai: <b><?= $stat_transaksi['selesai'] ?></b></li>
    </ul>

    <h2>Perlu Ditinjau (Status: Menunggu)</h2>
    <?php if (empty($transaksi_pending)): ?>
        <p>Tidak ada pengajuan yang menunggu.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($transaksi_pending as $t): ?>
                <li>
                    <a href="admin-transaksi-detail.php?id=<?= $t['id'] ?>">
                        <?= htmlspecialchars($t['nama_user']) ?> — <?= htmlspecialchars($t['judul']) ?>
                    </a>
                    (<?= $t['created_at'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
