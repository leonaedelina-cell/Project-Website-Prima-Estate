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
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

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
                        <a href="<?= BASE_URL ?><?= $user['role'] === 'admin' ? 'pages/admin/admin-dashboard.php' : 'pages/user/dashboard-user.php' ?>"
                           class="btn btn-outline-gold btn-sm px-3">
                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user['nama']) ?>
                        </a>
                        <a href="<?= BASE_URL ?>logout.php" class="btn btn-gold btn-sm px-3">Keluar</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-gold btn-sm px-3">Masuk</a>
                        <a href="<?= BASE_URL ?>register.php" class="btn btn-gold btn-sm px-3">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>