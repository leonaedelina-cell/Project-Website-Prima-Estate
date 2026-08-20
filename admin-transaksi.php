<?php
/**
 * pages/admin/admin-transaksi.php - Estate Prima
 * List semua transaksi, filter by status.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$filter_status = $_GET['status'] ?? '';
$status_valid  = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai'];

$query = "SELECT t.id, t.status, t.metode_bayar, t.created_at,
                 u.nama AS nama_user, p.judul, p.harga
          FROM transaksi t
          JOIN users u ON t.user_id = u.id
          JOIN properti p ON t.properti_id = p.id";

if (in_array($filter_status, $status_valid)) {
    $stmt = mysqli_prepare($koneksi, $query . " WHERE t.status = ? ORDER BY t.created_at DESC");
    mysqli_stmt_bind_param($stmt, "s", $filter_status);
    mysqli_stmt_execute($stmt);
    $daftar_transaksi = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} else {
    $daftar_transaksi = mysqli_fetch_all(
        mysqli_query($koneksi, $query . " ORDER BY t.created_at DESC"), MYSQLI_ASSOC
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Transaksi - Estate Prima (data test)</title>
</head>
<body>
    <p>
        <a href="admin-dashboard.php">&larr; Dashboard</a> |
        <a href="admin-properti.php">Kelola Properti</a>
    </p>

    <h1>Kelola Transaksi</h1>

    <!-- Filter status -->
    <p>
        Filter:
        <a href="admin-transaksi.php" style="<?= $filter_status === '' ? 'font-weight:bold;' : '' ?>">Semua</a> |
        <?php foreach ($status_valid as $s): ?>
            <a href="admin-transaksi.php?status=<?= $s ?>"
               style="<?= $filter_status === $s ? 'font-weight:bold;' : '' ?>"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <tr>
            <th>User</th><th>Properti</th><th>Harga</th><th>Metode Bayar</th><th>Status</th><th>Tanggal</th><th>Aksi</th>
        </tr>
        <?php if (empty($daftar_transaksi)): ?>
            <tr><td colspan="7">Tidak ada transaksi.</td></tr>
        <?php else: ?>
            <?php foreach ($daftar_transaksi as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['nama_user']) ?></td>
                    <td><?= htmlspecialchars($t['judul']) ?></td>
                    <td>Rp <?= number_format($t['harga'], 0, ',', '.') ?></td>
                    <td><?= $t['metode_bayar'] ? ucwords(str_replace('_', ' ', $t['metode_bayar'])) : '-' ?></td>
                    <td><?= ucfirst($t['status']) ?></td>
                    <td><?= $t['created_at'] ?></td>
                    <td><a href="admin-transaksi-detail.php?id=<?= $t['id'] ?>">Kelola</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</body>
</html>
