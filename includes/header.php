<?php
/**
 * includes/header.php - Estate Prima
 * Partial: <head> + navbar. Include di halaman PUBLIK (root level: index, listing, detail, kontak, dll).
 *
 * Variabel yang BOLEH diset sebelum require file ini:
 *   $page_title  (string) - judul tab browser, default "Estate Prima"
 *
 * Variabel yang HARUS sudah ada sebelum require file ini:
 *   $user        (array|null) - hasil dari user_login()
 *   BASE_URL     (constant)   - dari config/database.php
 */

$page_title = $page_title ?? 'Estate Prima';
$dashboard_sidebar = $dashboard_sidebar ?? false;
$admin_sidebar = $admin_sidebar ?? false;
$dashboard_sidebar_active = $dashboard_sidebar_active ?? 'dashboard';
$user = $user ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="<?= ($dashboard_sidebar || $admin_sidebar) ? 'has-dashboard-sidebar' : '' ?>">

<?php if ($admin_sidebar): ?>
    <aside class="dashboard-sidebar admin-sidebar">
        <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Ciutkan atau lebarkan sidebar" title="Ciutkan/lebarkan sidebar">
            <i class="bi bi-chevron-double-left"></i>
        </button>
        <a class="dashboard-sidebar-brand" href="<?= BASE_URL ?>admin-dashboard.php"><span class="sidebar-label">ESTATE <span>PRIMA</span></span></a>
        <div class="dashboard-sidebar-user">
            <i class="bi bi-shield-lock-fill"></i>
            <div class="sidebar-label"><small>Panel Admin</small><strong><?= htmlspecialchars(isset($user['nama']) ? $user['nama'] : 'Administrator') ?></strong></div>
        </div>
        <nav class="dashboard-sidebar-nav" aria-label="Navigasi admin">
            <a href="<?= BASE_URL ?>admin-dashboard.php" class="<?= $dashboard_sidebar_active === 'dashboard' ? 'active' : '' ?>" title="Dashboard"><i class="bi bi-speedometer2"></i><span class="sidebar-label">Dashboard</span></a>
            <a href="<?= BASE_URL ?>admin-properti.php" class="<?= $dashboard_sidebar_active === 'properti' ? 'active' : '' ?>" title="Kelola Properti"><i class="bi bi-houses-fill"></i><span class="sidebar-label">Kelola Properti</span></a>
            <a href="<?= BASE_URL ?>admin-transaksi.php" class="<?= $dashboard_sidebar_active === 'transaksi' ? 'active' : '' ?>" title="Kelola Transaksi"><i class="bi bi-receipt"></i><span class="sidebar-label">Kelola Transaksi</span></a>
            <a href="<?= BASE_URL ?>admin-agen.php" class="<?= $dashboard_sidebar_active === 'agen' ? 'active' : '' ?>" title="Kelola Agen"><i class="bi bi-person-badge-fill"></i><span class="sidebar-label">Kelola Agen</span></a>
            <a href="<?= BASE_URL ?>admin-pesan.php" class="<?= $dashboard_sidebar_active === 'pesan' ? 'active' : '' ?>" title="Pesan Kontak"><i class="bi bi-envelope-fill"></i><span class="sidebar-label">Pesan Kontak</span></a>
            <a href="<?= BASE_URL ?>admin-users.php" class="<?= $dashboard_sidebar_active === 'users' ? 'active' : '' ?>" title="Kelola Users"><i class="bi bi-people-fill"></i><span class="sidebar-label">Kelola Users</span></a>
        </nav>
        <div class="dashboard-sidebar-bottom">
            <a href="<?= BASE_URL ?>index.php" title="Kembali ke Beranda"><i class="bi bi-arrow-left"></i><span class="sidebar-label">Kembali ke Beranda</span></a>
            <form method="POST" action="<?= BASE_URL ?>logout.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><button type="submit" class="sidebar-logout" title="Keluar"><i class="bi bi-box-arrow-right"></i><span class="sidebar-label">Keluar</span></button></form>
        </div>
    </aside>
<?php elseif ($dashboard_sidebar): ?>
    <aside class="dashboard-sidebar">
        <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Ciutkan atau lebarkan sidebar" title="Ciutkan/lebarkan sidebar">
            <i class="bi bi-chevron-double-left"></i>
        </button>
        <a class="dashboard-sidebar-brand" href="<?= BASE_URL ?>dashboard-user.php"><span class="sidebar-label">ESTATE <span>PRIMA</span></span></a>
        <div class="dashboard-sidebar-user">
            <i class="bi bi-person-circle"></i>
            <div class="sidebar-label"><small>Selamat datang</small><strong><?= htmlspecialchars(isset($user['nama']) ? $user['nama'] : 'Pengguna') ?></strong></div>
        </div>
        <nav class="dashboard-sidebar-nav" aria-label="Navigasi dashboard">
            <a href="<?= BASE_URL ?>dashboard-user.php" class="<?= $dashboard_sidebar_active === 'dashboard' ? 'active' : '' ?>" title="Dashboard"><i class="bi bi-grid-1x2-fill"></i><span class="sidebar-label">Dashboard</span></a>
            <a href="<?= BASE_URL ?>wishlist.php" class="<?= $dashboard_sidebar_active === 'wishlist' ? 'active' : '' ?>" title="Wishlist"><i class="bi bi-heart-fill"></i><span class="sidebar-label">Wishlist</span></a>
            <a href="<?= BASE_URL ?>pesanan.php" class="<?= $dashboard_sidebar_active === 'pesanan' ? 'active' : '' ?>" title="Pesanan"><i class="bi bi-receipt"></i><span class="sidebar-label">Pesanan</span></a>
            <a href="<?= BASE_URL ?>profil.php" class="<?= $dashboard_sidebar_active === 'profil' ? 'active' : '' ?>" title="Profil"><i class="bi bi-person-gear"></i><span class="sidebar-label">Profil</span></a>
            <a href="<?= BASE_URL ?>listing.php" title="Jelajahi Properti"><i class="bi bi-houses-fill"></i><span class="sidebar-label">Jelajahi Properti</span></a>
        </nav>
        <div class="dashboard-sidebar-bottom">
            <a href="<?= BASE_URL ?>index.php" title="Kembali ke Beranda"><i class="bi bi-arrow-left"></i><span class="sidebar-label">Kembali ke Beranda</span></a>
            <form method="POST" action="<?= BASE_URL ?>logout.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><button type="submit" class="sidebar-logout" title="Keluar"><i class="bi bi-box-arrow-right"></i><span class="sidebar-label">Keluar</span></button></form>
        </div>
    </aside>
<?php else: ?>
    <!-- ============ NAVBAR ============ -->
    <nav class="navbar navbar-expand-lg navbar-estate sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>index.php">ESTATE <span class="accent">PRIMA</span></a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>listing.php">Properti</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>kontak.php">Kontak</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($user): ?>
                        <a href="<?= BASE_URL ?><?= $user['role'] === 'admin' ? 'admin-dashboard.php' : 'dashboard-user.php' ?>"
                           class="btn btn-outline-gold btn-sm px-3">
                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user['nama']) ?>
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>logout.php" class="d-inline"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><button type="submit" class="btn btn-gold btn-sm px-3">Keluar</button></form>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-gold btn-sm px-3">Masuk</a>
                        <a href="<?= BASE_URL ?>register.php" class="btn btn-gold btn-sm px-3">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
<?php endif; ?>
<?php if ($admin_sidebar || $dashboard_sidebar): ?>
<script src="<?= BASE_URL ?>assets/js/sidebar-toggle.js?v=<?= filemtime(__DIR__ . '/../assets/js/sidebar-toggle.js') ?>"></script>
<?php endif; ?>