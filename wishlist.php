<?php
/**
 * pages/user/wishlist.php - Estate Prima
 * Daftar lengkap wishlist milik user yang login.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login();

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT w.id AS wishlist_id, p.id AS properti_id, p.judul, p.harga, p.kota, p.status, p.gambar_url
     FROM wishlist w
     JOIN properti p ON w.properti_id = p.id
     WHERE w.user_id = ?
     ORDER BY w.created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$daftar_wishlist = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Wishlist Saya - Estate Prima (data test)</title>
</head>
<body>
    <p>
        <a href="dashboard-user.php">&larr; Dashboard</a> |
        <a href="pesanan.php">Pesanan Saya</a>
    </p>

    <h1>Wishlist Saya</h1>

    <?php if (empty($daftar_wishlist)): ?>
        <p>Belum ada properti yang di-wishlist. <a href="<?= BASE_URL ?>listing.php">Cari properti &rarr;</a></p>
    <?php else: ?>
        <?php foreach ($daftar_wishlist as $w): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <h3>
                    <a href="<?= BASE_URL ?>detail.php?id=<?= $w['properti_id'] ?>">
                        <?= htmlspecialchars($w['judul']) ?>
                    </a>
                </h3>
                <p>Rp <?= number_format($w['harga'], 0, ',', '.') ?> - <?= htmlspecialchars($w['kota']) ?></p>
                <p>Status: <?= ucfirst($w['status']) ?></p>

                <!-- Tombol hapus wishlist langsung dari halaman ini -->
                <form method="POST" action="<?= BASE_URL ?>proses-wishlist.php">
                    <input type="hidden" name="properti_id" value="<?= $w['properti_id'] ?>">
                    <button type="submit">Hapus dari Wishlist</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
