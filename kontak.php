<?php
/**
 * kontak.php - Estate Prima
 * Halaman contact sales: form kontak + Google Maps embed kantor.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if ($nama === '') $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if ($pesan === '') $errors[] = 'Pesan wajib diisi.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO pesan_kontak (nama, email, no_hp, pesan) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $no_hp, $pesan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $berhasil = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hubungi Estate Prima untuk konsultasi dan informasi properti.">
    <title>Kontak - Estate Prima</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #16324f;
            --navy-dark: #0d1f33;
            --gold: #c9a24b;
            --gold-light: #f5eedf;
            --cream: #f7f5f0;
            --text: #20242b;
            --muted: #6b7280;
            --line: #e3e1da;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--cream);
            color: var(--text);
            font-family: "Manrope", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .brand,
        .font-serif {
            font-family: "Fraunces", serif;
        }

        /* NAVBAR — sengaja mengikuti login.php */
        .navbar-estate {
            background: var(--navy);
            padding: 1rem 0;
        }

        .brand {
            color: #fff !important;
            font-size: 1.45rem;
            font-weight: 600;
            letter-spacing: .02em;
            text-decoration: none;
        }

        .brand span {
            color: var(--gold);
        }

        .navbar-estate .nav-link {
            color: rgba(255,255,255,.78);
            font-size: .88rem;
            font-weight: 700;
            margin: 0 .5rem;
            transition: color .2s ease;
        }

        .navbar-estate .nav-link:hover,
        .navbar-estate .nav-link.active {
            color: #e0c179;
        }

        .navbar-toggler {
            border-color: rgba(255,255,255,.25);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 .2rem rgba(201,162,75,.18);
        }

        .btn-outline-gold {
            border: 1px solid var(--gold);
            color: #e0c179;
            font-weight: 700;
        }

        .btn-outline-gold:hover,
        .btn-outline-gold.active {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--navy-dark);
        }

        .btn-gold {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--navy-dark);
            font-weight: 800;
        }

        .btn-gold:hover {
            background: #e0c179;
            border-color: #e0c179;
            color: var(--navy-dark);
        }

        /* CONTACT PAGE — layout mengikuti karakter login.php */
        .contact-section {
            min-height: calc(100vh - 73px);
            padding: 4rem 0 5rem;
        }

        .contact-shell {
            max-width: 1080px;
            margin: 0 auto;
        }

        .contact-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(13,31,51,.10);
        }

        .contact-intro {
            position: relative;
            min-height: 610px;
            padding: 3.2rem;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: var(--navy-dark);
            color: #fff;
        }

        .contact-intro::before {
            content: "";
            position: absolute;
            width: 390px;
            height: 390px;
            border: 1px solid rgba(201,162,75,.20);
            border-radius: 50%;
            right: -190px;
            top: -150px;
        }

        .contact-intro::after {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(201,162,75,.06);
            left: -150px;
            bottom: -145px;
        }

        .intro-content {
            position: relative;
            z-index: 2;
            max-width: 430px;
        }

        .eyebrow {
            color: var(--gold);
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .contact-intro h1 {
            margin: .75rem 0 1rem;
            color: #fff;
            font-family: "Fraunces", serif;
            font-size: 3.1rem;
            line-height: 1.08;
            font-weight: 600;
            letter-spacing: -.03em;
        }

        .contact-intro h1 em {
            color: #e0c179;
            font-style: italic;
        }

        .contact-intro p {
            color: rgba(255,255,255,.68);
            font-size: .82rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .contact-points {
            border-top: 1px solid rgba(255,255,255,.12);
        }

        .contact-point {
            display: flex;
            gap: .85rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255,255,255,.12);
        }

        .contact-point-icon {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            display: grid;
            place-items: center;
            color: #e0c179;
            border: 1px solid rgba(201,162,75,.25);
            background: rgba(201,162,75,.08);
            border-radius: 3px;
        }

        .point-label {
            color: rgba(255,255,255,.43);
            font-size: .58rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .point-value {
            color: #fff;
            font-size: .74rem;
            line-height: 1.55;
        }

        .point-value a {
            color: #fff;
            text-decoration: none;
        }

        .point-value a:hover {
            color: #e0c179;
        }

        .contact-form-side {
            min-height: 610px;
            padding: 3.2rem;
            background: #fff;
        }

        .back-home {
            color: var(--muted);
            font-size: .74rem;
            font-weight: 700;
            text-decoration: none;
        }

        .back-home:hover {
            color: var(--navy);
        }

        .contact-form-side h2 {
            color: var(--navy);
            font-family: "Fraunces", serif;
            font-size: 2rem;
            line-height: 1.15;
            font-weight: 600;
            margin: .55rem 0 .55rem;
        }

        .form-intro {
            color: var(--muted);
            font-size: .75rem;
            line-height: 1.75;
            margin-bottom: 1.6rem;
        }

        .form-label {
            color: var(--navy);
            font-size: .63rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: .4rem;
        }

        .form-control {
            min-height: 46px;
            border: 1px solid var(--line);
            border-radius: 3px;
            color: var(--text);
            font-size: .77rem;
            font-weight: 600;
        }

        textarea.form-control {
            min-height: 125px;
            resize: vertical;
        }

        .form-control::placeholder {
            color: #a0a5ad;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 .2rem rgba(201,162,75,.14);
        }

        .input-icon {
            position: relative;
        }

        .input-icon > i {
            position: absolute;
            z-index: 2;
            left: .85rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold);
            font-size: .85rem;
        }

        .input-icon .form-control {
            padding-left: 2.35rem;
        }

        .alert-estate {
            background: #f1f8f3;
            border: 1px solid #cfe5d4;
            color: #326b43;
            border-radius: 3px;
            font-size: .72rem;
            line-height: 1.6;
        }

        .alert-error {
            background: #fff5f5;
            border: 1px solid #f0d3d3;
            color: #a33c3c;
            border-radius: 3px;
            font-size: .72rem;
            line-height: 1.6;
        }

        .alert-error ul {
            margin-bottom: 0;
            padding-left: 1.1rem;
        }

        .map-section {
            padding: 0 0 5rem;
        }

        .map-card {
            max-width: 1080px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(13,31,51,.06);
        }

        .map-heading {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--line);
        }

        .map-heading h2 {
            color: var(--navy);
            font-family: "Fraunces", serif;
            font-size: 1.45rem;
            margin: .25rem 0 0;
        }

        .map-wrap {
            height: 320px;
        }

        .map-wrap iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .site-footer {
            background: var(--navy-dark);
            color: rgba(255,255,255,.62);
            padding: 3rem 0 1rem;
        }

        .footer-brand {
            color: #fff;
            font-family: "Fraunces", serif;
            font-size: 1.3rem;
            font-weight: 600;
            text-decoration: none;
        }

        .footer-brand span {
            color: var(--gold);
        }

        .site-footer h5 {
            color: #fff;
            font-family: "Fraunces", serif;
            font-size: 1rem;
            font-weight: 600;
        }

        .site-footer p,
        .site-footer li,
        .site-footer a {
            color: rgba(255,255,255,.62);
            font-size: .74rem;
            line-height: 1.8;
        }

        .site-footer a {
            text-decoration: none;
        }

        .site-footer a:hover {
            color: #e0c179;
        }

        .footer-line {
            border-color: rgba(255,255,255,.1);
        }

        @media (max-width: 991.98px) {
            .contact-intro {
                min-height: 390px;
            }

            .contact-form-side {
                min-height: auto;
            }
        }

        @media (max-width: 767.98px) {
            .contact-section {
                min-height: calc(100vh - 69px);
                padding: 2rem 0 3rem;
            }

            .contact-intro {
                min-height: 330px;
                padding: 2rem;
            }

            .contact-intro h1 {
                font-size: 2.35rem;
            }

            .contact-form-side {
                padding: 2rem 1.25rem;
            }

            .map-wrap {
                height: 270px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR: sama dengan login.php -->
<nav class="navbar navbar-expand-lg navbar-estate sticky-top">
    <div class="container">
        <a class="brand" href="index.php">
            ESTATE <span>PRIMA</span>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav"
                aria-controls="mainNav"
                aria-expanded="false"
                aria-label="Buka menu">
            <i class="bi bi-list text-white fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto my-3 my-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="listing.php">Properti</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="kontak.php">Kontak</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a href="login.php" class="btn btn-outline-gold btn-sm px-3">
                    Masuk
                </a>
                <a href="register.php" class="btn btn-gold btn-sm px-3">
                    Daftar
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="contact-section">
    <div class="container">
        <div class="contact-shell">
            <div class="contact-panel">
                <div class="row g-0">

                    <div class="col-lg-6">
                        <section class="contact-intro">
                            <div class="intro-content">
                                <div class="eyebrow">Estate Prima / Contact Sales</div>

                                <h1>
                                    Mari bicara tentang
                                    <em>properti impianmu.</em>
                                </h1>

                                <p>
                                    Punya pertanyaan tentang properti atau membutuhkan
                                    bantuan dari tim kami? Kirimkan pesan dan kami akan
                                    membantu menemukan informasi yang kamu butuhkan.
                                </p>

                                <div class="contact-points">
                                    <div class="contact-point">
                                        <div class="contact-point-icon">
                                            <i class="bi bi-geo-alt"></i>
                                        </div>
                                        <div>
                                            <div class="point-label">Lokasi Kantor</div>
                                            <div class="point-value">Jakarta Selatan, Indonesia</div>
                                        </div>
                                    </div>

                                    <div class="contact-point">
                                        <div class="contact-point-icon">
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                        <div>
                                            <div class="point-label">Email</div>
                                            <div class="point-value">
                                                <a href="mailto:halo@estateprima.test">
                                                    halo@estateprima.test
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="contact-point">
                                        <div class="contact-point-icon">
                                            <i class="bi bi-clock"></i>
                                        </div>
                                        <div>
                                            <div class="point-label">Jam Operasional</div>
                                            <div class="point-value">
                                                Senin - Jumat, 09.00 - 17.00 WIB
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-6">
                        <section class="contact-form-side">

                            <a href="index.php" class="back-home">
                                <i class="bi bi-arrow-left me-1"></i>
                                Kembali ke beranda
                            </a>

                            <div class="eyebrow mt-4">Kirim Pesan</div>
                            <h2>Hubungi kami</h2>
                            <p class="form-intro">
                                Isi data berikut dengan benar agar tim Estate Prima
                                dapat segera menghubungi kamu.
                            </p>

                            <?php if ($berhasil): ?>
                                <div class="alert alert-estate d-flex align-items-start gap-2 mb-4" role="alert">
                                    <i class="bi bi-check-circle-fill mt-1"></i>
                                    <div>
                                        Pesan kamu berhasil terkirim!
                                        Tim kami akan segera menghubungi kamu.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-error mb-4" role="alert">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-exclamation-circle-fill mt-1"></i>
                                        <div>
                                            <strong>Periksa kembali data kamu.</strong>
                                            <ul class="mt-1">
                                                <?php foreach ($errors as $e): ?>
                                                    <li><?= htmlspecialchars($e) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="kontak.php">
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">Nama</label>
                                        <div class="input-icon">
                                            <i class="bi bi-person"></i>
                                            <input
                                                type="text"
                                                id="nama"
                                                name="nama"
                                                class="form-control"
                                                placeholder="Nama lengkap"
                                                value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-icon">
                                            <i class="bi bi-envelope"></i>
                                            <input
                                                type="email"
                                                id="email"
                                                name="email"
                                                class="form-control"
                                                placeholder="nama@email.com"
                                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="no_hp" class="form-label">No. HP</label>
                                        <div class="input-icon">
                                            <i class="bi bi-telephone"></i>
                                            <input
                                                type="text"
                                                id="no_hp"
                                                name="no_hp"
                                                class="form-control"
                                                placeholder="Contoh: 081234567890"
                                                value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="pesan" class="form-label">Pesan</label>
                                        <textarea
                                            id="pesan"
                                            name="pesan"
                                            class="form-control"
                                            placeholder="Ceritakan kebutuhan properti kamu..."
                                            required
                                        ><?= htmlspecialchars($_POST['pesan'] ?? '') ?></textarea>
                                    </div>

                                    <div class="col-12 pt-1">
                                        <button type="submit" class="btn btn-gold w-100 py-2">
                                            <i class="bi bi-send-fill me-2"></i>
                                            Kirim Pesan
                                        </button>
                                    </div>

                                </div>
                            </form>

                        </section>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<section class="map-section">
    <div class="container">
        <div class="map-card">
            <div class="map-heading">
                <div class="eyebrow">Estate Prima / Location</div>
                <h2>Lokasi Kantor</h2>
            </div>

            <div class="map-wrap">
                <iframe
                    src="https://www.google.com/maps?q=Jakarta+Selatan&output=embed"
                    allowfullscreen
                    loading="lazy"
                    title="Lokasi Kantor Estate Prima">
                </iframe>
            </div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4 pb-4">

            <div class="col-lg-4">
                <a href="index.php" class="footer-brand">
                    ESTATE <span>PRIMA</span>
                </a>
                <p class="mt-2 mb-0">
                    Platform pencarian dan pengajuan pembelian properti —
                    rumah, apartemen, tanah, dan ruko di lokasi pilihan.
                </p>
            </div>

            <div class="col-lg-2 col-6">
                <h5>Jelajahi</h5>
                <ul class="list-unstyled mt-2">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="listing.php">Semua Properti</a></li>
                    <li><a href="kontak.php">Kontak</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-6">
                <h5>Akun</h5>
                <ul class="list-unstyled mt-2">
                    <li><a href="login.php">Masuk</a></li>
                    <li><a href="register.php">Daftar</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h5>Kontak</h5>
                <ul class="list-unstyled mt-2">
                    <li><i class="bi bi-geo-alt me-2"></i>Jakarta Selatan, Indonesia</li>
                    <li><i class="bi bi-envelope me-2"></i>halo@estateprima.test</li>
                </ul>
            </div>

        </div>

        <hr class="footer-line">

        <p class="text-center py-3 mb-0">
            &copy; <?= date('Y') ?> Estate Prima. Semua hak dilindungi.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>