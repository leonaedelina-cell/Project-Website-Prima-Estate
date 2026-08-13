<?php
/**
 * index.php - Estate Prima
 * Homepage: hero + search cepat + statistik + properti terbaru
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// ------------------------------------------------------------------
// 1. Statistik ringkas
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
$query_terbaru = "SELECT id, judul, harga, tipe, kota, kamar_tidur, kamar_mandi, luas_bangunan, gambar_url
                   FROM properti
                   WHERE status = 'tersedia'
                   ORDER BY created_at DESC
                   LIMIT 6";
$hasil_terbaru = mysqli_query($koneksi, $query_terbaru);
$properti_terbaru = mysqli_fetch_all($hasil_terbaru, MYSQLI_ASSOC);

// Daftar kota unik (buat dropdown quick search)
$daftar_kota = mysqli_fetch_all(
    mysqli_query($koneksi, "SELECT DISTINCT kota FROM properti ORDER BY kota"),
    MYSQLI_ASSOC
);

$user = user_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estate Prima — Temukan Hunian Impian Anda</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --navy-950: #0d1f33;
            --navy-900: #16324f;
            --navy-700: #24486d;
            --gold-500: #c9a24b;
            --gold-300: #e0c179;
            --cream-50:  #f7f5f0;
            --ink-900:  #20242b;
            --ink-500:  #6b7280;
        }

        body {
            font-family: 'Manrope', sans-serif;
            color: var(--ink-900);
            background: var(--cream-50);
        }

        .font-display {
            font-family: 'Fraunces', serif;
        }

        .text-gold   { color: var(--gold-500); }
        .bg-navy     { background-color: var(--navy-900); }
        .bg-navy-950 { background-color: var(--navy-950); }

        /* ---------- Navbar ---------- */
        .navbar-estate {
            background-color: var(--navy-900);
            padding: 1.1rem 0;
        }
        .navbar-estate .navbar-brand {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: 0.02em;
            color: #fff;
        }
        .navbar-estate .navbar-brand .accent { color: var(--gold-500); }
        .navbar-estate .nav-link {
            color: rgba(255,255,255,0.82);
            font-weight: 600;
            font-size: 0.92rem;
            margin: 0 0.65rem;
        }
        .navbar-estate .nav-link:hover,
        .navbar-estate .nav-link.active { color: var(--gold-300); }
        .btn-gold {
            background-color: var(--gold-500);
            border-color: var(--gold-500);
            color: var(--navy-950);
            font-weight: 700;
        }
        .btn-gold:hover {
            background-color: var(--gold-300);
            border-color: var(--gold-300);
            color: var(--navy-950);
        }
        .btn-outline-gold {
            border: 1.5px solid var(--gold-500);
            color: var(--gold-300);
            font-weight: 600;
        }
        .btn-outline-gold:hover {
            background-color: var(--gold-500);
            color: var(--navy-950);
        }

        /* ---------- Hero ---------- */
        .hero {
            position: relative;
            min-height: 92vh;
            display: flex;
            align-items: center;
            background-image:
                linear-gradient(180deg, rgba(13,31,51,0.55) 0%, rgba(13,31,51,0.35) 40%, rgba(13,31,51,0.92) 100%),
                url('https://images.unsplash.com/photo-1757359056339-22968344cce6?fm=jpg&q=80&w=2200&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }
        .hero-eyebrow {
            font-family: 'Manrope', sans-serif;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-size: 0.78rem;
            color: var(--gold-300);
        }
        .hero h1 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: clamp(2.4rem, 5vw, 3.9rem);
            line-height: 1.08;
            color: #fff;
        }
        .hero h1 em {
            font-style: italic;
            color: var(--gold-300);
        }
        .hero p.lead {
            color: rgba(255,255,255,0.85);
            font-size: 1.08rem;
            max-width: 34rem;
        }

        /* ---------- Search card (signature: blueprint corner-tick) ---------- */
        .search-card {
            position: relative;
            background: #fff;
            border-radius: 4px;
            padding: 1.75rem 1.75rem 1.5rem;
            box-shadow: 0 24px 60px rgba(13,31,51,0.35);
        }
        .corner-tick { position: absolute; width: 18px; height: 18px; pointer-events: none; }
        .corner-tick::before, .corner-tick::after { content: ""; position: absolute; background: var(--gold-500); }
        .corner-tick::before { width: 100%; height: 2px; }
        .corner-tick::after  { width: 2px; height: 100%; }
        .corner-tick.tl { top: -2px; left: -2px; }
        .corner-tick.tr { top: -2px; right: -2px; }
        .corner-tick.tr::before { right: 0; }
        .corner-tick.tr::after  { right: 0; }
        .corner-tick.bl { bottom: -2px; left: -2px; }
        .corner-tick.bl::before { bottom: 0; }
        .corner-tick.bl::after  { bottom: 0; }
        .corner-tick.br { bottom: -2px; right: -2px; }
        .corner-tick.br::before { bottom: 0; right: 0; }
        .corner-tick.br::after  { bottom: 0; right: 0; }

        .search-card label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink-500);
            margin-bottom: 0.3rem;
        }
        .search-card .form-select,
        .search-card .form-control {
            border: 1px solid #e3e1da;
            border-radius: 3px;
            padding: 0.55rem 0.75rem;
            font-weight: 600;
            color: var(--ink-900);
        }
        .search-card .form-select:focus,
        .search-card .form-control:focus {
            border-color: var(--gold-500);
            box-shadow: 0 0 0 0.2rem rgba(201,162,75,0.25);
        }

        /* ---------- Stats band ---------- */
        .stats-band {
            background: var(--navy-950);
            padding: 2.75rem 0 2.25rem;
        }
        .stat-num {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 2.4rem;
            color: var(--gold-500);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.65);
            margin-top: 0.4rem;
        }

        /* ---------- Section heading ---------- */
        .section-eyebrow {
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-size: 0.76rem;
            color: var(--gold-500);
        }
        .section-title {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 2.1rem;
            color: var(--navy-900);
        }

        /* ---------- Property card ---------- */
        .property-card {
            position: relative;
            background: #fff;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #ece9e1;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(13,31,51,0.15);
        }
        .property-card .corner-tick { opacity: 0; transition: opacity 0.25s ease; }
        .property-card:hover .corner-tick { opacity: 1; }
        .property-card .thumb {
            height: 210px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .property-card .price-tag {
            position: absolute;
            bottom: 0.75rem;
            left: 0.75rem;
            background: var(--navy-950);
            color: var(--gold-300);
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.35rem 0.75rem;
            border-radius: 3px;
        }
        .property-card .type-tag {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            background: rgba(255,255,255,0.92);
            color: var(--navy-900);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.25rem 0.6rem;
            border-radius: 3px;
        }
        .property-card .card-body h3 {
            font-family: 'Fraunces', serif;
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--navy-900);
        }
        .property-card .location {
            color: var(--ink-500);
            font-size: 0.85rem;
        }
        .property-card .specs {
            font-size: 0.82rem;
            color: var(--ink-500);
            border-top: 1px dashed #e3e1da;
            padding-top: 0.65rem;
            margin-top: 0.65rem;
        }
        .property-card .specs i { color: var(--gold-500); margin-right: 0.25rem; }
        .property-card .stretched-link::after { z-index: 2; }

        /* ---------- CTA banner ---------- */
        .cta-banner {
            background: var(--navy-900);
            border-radius: 6px;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .cta-banner::after {
            content: "";
            position: absolute;
            inset: 0;
            border: 1px solid rgba(201,162,75,0.35);
            margin: 10px;
            border-radius: 3px;
            pointer-events: none;
        }
        .cta-banner h2 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            color: #fff;
        }

        /* ---------- Footer ---------- */
        .site-footer {
            background: var(--navy-950);
            color: rgba(255,255,255,0.65);
            padding-top: 3rem;
        }
        .site-footer h5 {
            font-family: 'Fraunces', serif;
            color: #fff;
            font-weight: 600;
        }
        .site-footer a {
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 0.92rem;
        }
        .site-footer a:hover { color: var(--gold-300); }
        .site-footer hr { border-color: rgba(255,255,255,0.12); }
    </style>
</head>
<body>

    <!-- ============ NAVBAR ============ -->
    <nav class="navbar navbar-expand-lg navbar-estate sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">ESTATE <span class="accent">PRIMA</span></a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="listing.php">Properti</a></li>
                    <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($user): ?>
                        <a href="<?= $user['role'] === 'admin' ? 'pages/admin/admin-dashboard.php' : 'pages/user/dashboard-user.php' ?>"
                           class="btn btn-outline-gold btn-sm px-3">
                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user['nama']) ?>
                        </a>
                        <a href="logout.php" class="btn btn-gold btn-sm px-3">Keluar</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-gold btn-sm px-3">Masuk</a>
                        <a href="register.php" class="btn btn-gold btn-sm px-3">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- ============ HERO ============ -->
    <header class="hero">
        <div class="container pb-5">
            <div class="row">
                <div class="col-lg-7">
                    <p class="hero-eyebrow mb-3">Properti Pilihan &middot; Terverifikasi</p>
                    <h1 class="mb-3">Temukan hunian yang <em>terasa seperti pulang.</em></h1>
                    <p class="lead mb-4">
                        Estate Prima menghadirkan koleksi rumah, apartemen, dan tanah pilihan
                        di lokasi-lokasi strategis — lengkap dengan proses pengajuan beli yang transparan.
                    </p>
                </div>
            </div>

            <!-- Search card, mengambang di bawah hero -->
            <div class="row justify-content-start">
                <div class="col-lg-10">
                    <div class="search-card mt-4">
                        <span class="corner-tick tl"></span>
                        <span class="corner-tick tr"></span>
                        <span class="corner-tick bl"></span>
                        <span class="corner-tick br"></span>

                        <form action="listing.php" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="d-block">Kata Kunci</label>
                                <input type="text" name="q" class="form-control" placeholder="Nama, alamat, kota...">
                            </div>
                            <div class="col-md-3">
                                <label class="d-block">Kota</label>
                                <select name="kota" class="form-select">
                                    <option value="">Semua Kota</option>
                                    <?php foreach ($daftar_kota as $k): ?>
                                        <option value="<?= htmlspecialchars($k['kota']) ?>"><?= htmlspecialchars($k['kota']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="d-block">Tipe Properti</label>
                                <select name="tipe" class="form-select">
                                    <option value="">Semua Tipe</option>
                                    <option value="rumah">Rumah</option>
                                    <option value="apartemen">Apartemen</option>
                                    <option value="tanah">Tanah</option>
                                    <option value="ruko">Ruko</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-gold w-100 py-2">
                                    <i class="bi bi-search me-1"></i> Cari Properti
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ============ STATISTIK ============ -->
    <section class="stats-band">
        <div class="container">
            <div class="row text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-num"><?= $total_properti ?></div>
                    <div class="stat-label">Total Properti</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-num"><?= $total_tersedia ?></div>
                    <div class="stat-label">Siap Dihuni</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-num"><?= $total_terjual ?></div>
                    <div class="stat-label">Telah Terjual</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-num"><?= $total_user ?></div>
                    <div class="stat-label">Pengguna Terdaftar</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ PROPERTI TERBARU ============ -->
    <section class="py-5 my-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
                <div>
                    <p class="section-eyebrow mb-2">Baru Ditambahkan</p>
                    <h2 class="section-title mb-0">Properti Pilihan Terbaru</h2>
                </div>
                <a href="listing.php" class="btn btn-outline-secondary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <?php if (empty($properti_terbaru)): ?>
                <p class="text-muted">Belum ada properti yang tersedia saat ini.</p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($properti_terbaru as $p): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="property-card">
                                <span class="corner-tick tl"></span>
                                <span class="corner-tick br"></span>

                                <div class="thumb" style="background-image:url('<?= htmlspecialchars($p['gambar_url'] ?: 'https://images.unsplash.com/photo-1568605114967-8130f3a36994') ?>');">
                                    <span class="type-tag"><?= ucfirst($p['tipe']) ?></span>
                                    <span class="price-tag">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                                </div>
                                <div class="card-body p-3">
                                    <h3 class="mb-1"><?= htmlspecialchars($p['judul']) ?></h3>
                                    <p class="location mb-2"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($p['kota']) ?></p>
                                    <div class="specs d-flex gap-3">
                                        <span><i class="bi bi-door-closed-fill"></i><?= $p['kamar_tidur'] ?> KT</span>
                                        <span><i class="bi bi-droplet-fill"></i><?= $p['kamar_mandi'] ?> KM</span>
                                        <span><i class="bi bi-rulers"></i><?= $p['luas_bangunan'] ?> m&sup2;</span>
                                    </div>
                                    <a href="detail.php?id=<?= $p['id'] ?>" class="stretched-link"></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============ CTA ============ -->
    <section class="container pb-5 mb-4">
        <div class="cta-banner text-center">
            <h2 class="mb-2">Butuh bantuan menemukan hunian yang tepat?</h2>
            <p class="text-white-50 mb-4">Tim sales kami siap membantu, dari konsultasi hingga proses pengajuan pembelian.</p>
            <a href="kontak.php" class="btn btn-gold px-4 py-2">Hubungi Tim Sales</a>
        </div>
    </section>

    <!-- ============ FOOTER ============ -->
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 pb-4">
                <div class="col-lg-4">
                    <h5>ESTATE <span class="text-gold">PRIMA</span></h5>
                    <p class="small mt-2">Platform pencarian dan pengajuan pembelian properti — rumah, apartemen, tanah, dan ruko di lokasi-lokasi pilihan.</p>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="fs-6">Jelajahi</h5>
                    <ul class="list-unstyled mt-2 d-flex flex-column gap-2">
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="listing.php">Semua Properti</a></li>
                        <li><a href="kontak.php">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="fs-6">Akun</h5>
                    <ul class="list-unstyled mt-2 d-flex flex-column gap-2">
                        <li><a href="login.php">Masuk</a></li>
                        <li><a href="register.php">Daftar</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="fs-6">Kontak</h5>
                    <ul class="list-unstyled mt-2 d-flex flex-column gap-2 small">
                        <li><i class="bi bi-geo-alt me-2"></i>Jakarta Selatan, Indonesia</li>
                        <li><i class="bi bi-envelope me-2"></i>halo@estateprima.test</li>
                    </ul>
                </div>
            </div>
            <hr>
            <p class="small text-center py-3 mb-0">&copy; <?= date('Y') ?> Estate Prima. Dibuat untuk keperluan akademik.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>