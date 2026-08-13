<?php
/**
 * index.php - Estate Prima
 * Homepage: hero + statistik + properti terbaru
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php'; // buat cek user login (nampilin "Halo, {nama}" nanti)

// ------------------------------------------------------------------
// 1. Statistik ringkas (buat section statistik di homepage)
// ------------------------------------------------------------------
$total_properti = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM properti")
)['total'];

$total_tersedia = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM properti WHERE status = 'tersedia'")
)['total'];

$total_terjual = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM properti WHERE status = 'terjual'")
)['total'];

$total_user = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role = 'user'")
)['total'];

// ------------------------------------------------------------------
// 2. Properti terbaru (6 item, status masih tersedia)
// ------------------------------------------------------------------
$query_terbaru = "SELECT id, judul, harga, kota, kamar_tidur, kamar_mandi, luas_bangunan, gambar_url
                   FROM properti
                   WHERE status = 'tersedia'
                   ORDER BY created_at DESC
                   LIMIT 6";
$hasil_terbaru = mysqli_query($koneksi, $query_terbaru);
$properti_terbaru = mysqli_fetch_all($hasil_terbaru, MYSQLI_ASSOC);

$user = user_login(); // null kalau belum login
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Estate Prima - Homepage (data test)</title>
</head>
<body>
    <h1>Estate Prima</h1>

    <p>
        <?php if ($user): ?>
            Halo, <b><?= htmlspecialchars($user['nama']) ?></b> (<?= $user['role'] ?>) —
            <?php if ($user['role'] === 'admin'): ?>
                <a href="pages/admin/admin-dashboard.php">Dashboard Admin</a> |
            <?php else: ?>
                <a href="pages/user/dashboard-user.php">Dashboard Saya</a> |
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </p>

    <h2>Statistik</h2>
    <ul>
        <li>Total properti: <?= $total_properti ?></li>
        <li>Tersedia: <?= $total_tersedia ?></li>
        <li>Terjual: <?= $total_terjual ?></li>
        <li>Total user terdaftar: <?= $total_user ?></li>
    </ul>

    <h2>Properti Terbaru</h2>
    <?php if (empty($properti_terbaru)): ?>
        <p>Belum ada properti.</p>
    <?php else: ?>
        <?php foreach ($properti_terbaru as $p): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <h3><a href="detail.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['judul']) ?></a></h3>
                <p>Rp <?= number_format($p['harga'], 0, ',', '.') ?> - <?= htmlspecialchars($p['kota']) ?></p>
                <p><?= $p['kamar_tidur'] ?> KT | <?= $p['kamar_mandi'] ?> KM | <?= $p['luas_bangunan'] ?> m2</p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="listing.php">Lihat semua properti &rarr;</a></p>
</body>
</html>