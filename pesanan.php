<?php
/**
 * pages/user/pesanan.php - Estate Prima
 * Status transaksi/pengajuan pembelian milik user yang login.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya - Estate Prima (data test)</title>
</head>
<body>
    <p>
        <a href="dashboard-user.php">&larr; Dashboard</a> |
        <a href="wishlist.php">Wishlist Saya</a>
    </p>

    <h1>Pesanan Saya</h1>

    <?php if (empty($daftar_transaksi)): ?>
        <p>Belum ada pengajuan pembelian. <a href="<?= BASE_URL ?>listing.php">Cari properti &rarr;</a></p>
    <?php else: ?>
        <?php foreach ($daftar_transaksi as $t): ?>
            <?php [$label, $warna] = $label_status[$t['status']]; ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <h3>
                    <a href="<?= BASE_URL ?>detail.php?id=<?= $t['properti_id'] ?>">
                        <?= htmlspecialchars($t['judul']) ?>
                    </a>
                </h3>
                <p>Rp <?= number_format($t['harga'], 0, ',', '.') ?></p>
                <p>Metode Bayar: <?= $t['metode_bayar'] ? ucwords(str_replace('_', ' ', $t['metode_bayar'])) : '-' ?></p>
                <p>Status: <b style="color:<?= $warna ?>;"><?= $label ?></b></p>
                <?php if ($t['catatan_admin']): ?>
                    <p>Catatan Admin: <i><?= htmlspecialchars($t['catatan_admin']) ?></i></p>
                <?php endif; ?>
                <p><small>Diajukan pada: <?= $t['created_at'] ?></small></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>