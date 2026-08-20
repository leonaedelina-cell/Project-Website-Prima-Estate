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

$status_list = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai'];
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User - Estate Prima (data test)</title>
</head>
<body>
    <p>
        <a href="<?= BASE_URL ?>index.php">&larr; Homepage</a> |
        <a href="wishlist.php">Wishlist Saya</a> |
        <a href="pesanan.php">Pesanan Saya</a> |
        <a href="<?= BASE_URL ?>logout.php">Logout</a>
    </p>

    <h1>Halo, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h1>

    <h2>Ringkasan</h2>
    <ul>
        <li>Total Wishlist: <b><?= $total_wishlist ?></b></li>
        <li>Menunggu: <b><?= $rekap_status['menunggu'] ?></b></li>
        <li>Diproses: <b><?= $rekap_status['diproses'] ?></b></li>
        <li>Disetujui: <b><?= $rekap_status['disetujui'] ?></b></li>
        <li>Ditolak: <b><?= $rekap_status['ditolak'] ?></b></li>
        <li>Selesai: <b><?= $rekap_status['selesai'] ?></b></li>
    </ul>

    <h2>Aktivitas Transaksi Terbaru</h2>
    <?php if (empty($transaksi_terbaru)): ?>
        <p>Belum ada transaksi.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($transaksi_terbaru as $t): ?>
                <li>
                    <?= htmlspecialchars($t['judul']) ?> —
                    status: <b><?= ucfirst($t['status']) ?></b>
                    (<?= $t['created_at'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
