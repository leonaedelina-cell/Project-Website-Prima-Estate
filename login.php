<?php
/**
 * login.php - Estate Prima
 * Menangani submit form login (dari login.html nanti).
 * Field form yang diharapkan: email, password
 */

session_start();
require_once __DIR__ . '/config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    }

    $user = null;

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id, nama, email, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $user  = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        // Penting: pesan error digeneralisasi ("email/password salah"), jangan spesifik
        // "email tidak ditemukan" -- supaya orang jahat gak bisa nebak email mana yang terdaftar.
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        }
    }

    if (empty($errors)) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        // Redirect beda tujuan tergantung role
        if ($user['role'] === 'admin') {
            header('Location: ' . BASE_URL . 'pages/admin/admin-dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'index.php');
        }
        exit;
    }
}

// Kalau sampai sini berarti ada $errors atau method GET (baru buka halaman)
// -> nanti di sinilah tempat nge-include tampilan login.html / render pesan error
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Masuk ke akun Estate Prima.">
    <title>Masuk - Estate Prima</title>

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
            min-height: 100vh;
            margin: 0;
            font-family: "Manrope", sans-serif;
            color: var(--text);
            background: var(--cream);
            -webkit-font-smoothing: antialiased;
        }

        .font-display { font-family: "Fraunces", serif; }

        .navbar-estate {
            background: var(--navy);
            padding: 1rem 0;
        }

        .brand {
            color: #fff;
            font-family: "Fraunces", serif;
            font-size: 1.45rem;
            font-weight: 600;
            letter-spacing: .02em;
            text-decoration: none;
        }

        .brand span { color: var(--gold); }

        .navbar-estate .nav-link {
            color: rgba(255,255,255,.78);
            font-size: .88rem;
            font-weight: 700;
            margin: 0 .5rem;
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

        .btn-outline-gold {
            border: 1px solid var(--gold);
            color: #e0c179;
            font-weight: 700;
        }

        .btn-outline-gold:hover {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--navy-dark);
        }

        .login-section {
            min-height: calc(100vh - 73px);
            display: flex;
            align-items: center;
            padding: 4rem 0;
            background:
                radial-gradient(circle at 12% 20%, rgba(201,162,75,.08), transparent 27%),
                var(--cream);
        }

        .login-shell {
            width: 100%;
            max-width: 1060px;
            margin: auto;
        }

        .login-panel {
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 5px;
            box-shadow: 0 22px 60px rgba(13,31,51,.10);
        }

        .login-intro {
            min-height: 600px;
            position: relative;
            overflow: hidden;
            padding: 3.2rem;
            background:
                linear-gradient(180deg, rgba(13,31,51,.38), rgba(13,31,51,.93)),
                url("https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1400&q=80")
                center / cover;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .login-intro::after {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border: 1px solid rgba(201,162,75,.32);
            border-radius: 50%;
            top: -110px;
            right: -100px;
        }

        .intro-content {
            position: relative;
            z-index: 2;
        }

        .eyebrow {
            color: var(--gold);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .login-intro h1 {
            max-width: 410px;
            margin: .75rem 0 1rem;
            color: #fff;
            font-family: "Fraunces", serif;
            font-size: clamp(2.3rem, 4vw, 3.25rem);
            font-weight: 600;
            line-height: 1.08;
        }

        .login-intro h1 em {
            color: #e0c179;
            font-style: italic;
        }

        .login-intro p {
            max-width: 440px;
            margin: 0;
            color: rgba(255,255,255,.72);
            font-size: .84rem;
            line-height: 1.8;
        }

        .login-form-side {
            min-height: 600px;
            padding: 3.2rem;
            display: flex;
            align-items: center;
        }

        .form-inner {
            width: 100%;
            max-width: 410px;
            margin: auto;
        }

        .form-inner h2 {
            color: var(--navy);
            font-family: "Fraunces", serif;
            font-size: 2rem;
            font-weight: 600;
            margin: .5rem 0 .6rem;
        }

        .form-intro {
            color: var(--muted);
            font-size: .8rem;
            line-height: 1.7;
            margin-bottom: 1.7rem;
        }

        .form-label {
            color: var(--navy);
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: .45rem;
        }

        .input-group-estate {
            position: relative;
        }

        .input-group-estate .form-control {
            min-height: 47px;
            border: 1px solid var(--line);
            border-radius: 3px;
            color: var(--text);
            font-size: .82rem;
            font-weight: 600;
            padding-right: 2.8rem;
        }

        .input-group-estate .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 .2rem rgba(201,162,75,.15);
        }

        .input-icon {
            position: absolute;
            right: .85rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold);
            z-index: 4;
        }

        .login-submit {
            min-height: 47px;
            border-radius: 3px;
            font-size: .82rem;
        }

        .alert-error {
            border: 1px solid #f0d3d3;
            border-radius: 3px;
            background: #fff5f5;
            color: #a33c3c;
            font-size: .78rem;
            line-height: 1.6;
        }

        .register-note {
            margin-top: 1.5rem;
            padding-top: 1.3rem;
            border-top: 1px dashed var(--line);
            color: var(--muted);
            font-size: .76rem;
            text-align: center;
        }

        .register-note a {
            color: var(--navy);
            font-weight: 800;
            text-decoration: none;
        }

        .register-note a:hover {
            color: var(--gold);
        }

        .back-home {
            color: var(--muted);
            font-size: .74rem;
            font-weight: 700;
            text-decoration: none;
        }

        .back-home:hover { color: var(--navy); }

        @media (max-width: 991.98px) {
            .login-intro {
                min-height: 360px;
            }

            .login-form-side {
                min-height: auto;
            }
        }

        @media (max-width: 767.98px) {
            .login-section {
                min-height: calc(100vh - 69px);
                padding: 2rem 0;
            }

            .login-intro {
                min-height: 300px;
                padding: 2rem;
            }

            .login-intro h1 {
                font-size: 2.35rem;
            }

            .login-form-side {
                padding: 2rem 1.25rem;
            }
        }
    </style>
</head>

<body>

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
                    <a class="nav-link" href="kontak.php">Kontak</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a href="login.php" class="btn btn-outline-gold btn-sm px-3 active">
                    Masuk
                </a>
                <a href="register.php" class="btn btn-gold btn-sm px-3">
                    Daftar
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="login-section">
    <div class="container">
        <div class="login-shell">
            <div class="login-panel">
                <div class="row g-0">

                    <div class="col-lg-6">
                        <section class="login-intro">
                            <div class="intro-content">
                                <div class="eyebrow">Estate Prima / Member Area</div>
                                <h1>Selamat datang <em>kembali.</em></h1>
                                <p>
                                    Masuk ke akun Estate Prima untuk melanjutkan perjalanan
                                    menemukan dan mengajukan properti pilihanmu.
                                </p>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-6">
                        <section class="login-form-side">
                            <div class="form-inner">

                                <a href="index.php" class="back-home">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Kembali ke beranda
                                </a>

                                <div class="eyebrow mt-4">Akses Akun</div>
                                <h2>Masuk ke akun</h2>
                                <p class="form-intro">
                                    Gunakan email dan password yang sudah terdaftar
                                    untuk melanjutkan.
                                </p>

                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-error mb-4" role="alert">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-exclamation-circle-fill mt-1"></i>
                                            <div>
                                                <?php foreach ($errors as $error): ?>
                                                    <div><?= htmlspecialchars($error) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="login.php">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group-estate">
                                            <input
                                                type="email"
                                                class="form-control"
                                                id="email"
                                                name="email"
                                                placeholder="nama@email.com"
                                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                                autocomplete="email"
                                                required
                                            >
                                            <i class="bi bi-envelope input-icon"></i>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group-estate">
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password"
                                                name="password"
                                                placeholder="Masukkan password"
                                                autocomplete="current-password"
                                                required
                                            >
                                            <button
                                                type="button"
                                                class="btn border-0 position-absolute end-0 top-50 translate-middle-y me-1 text-secondary"
                                                id="togglePassword"
                                                aria-label="Tampilkan password"
                                                style="z-index:5;"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-gold login-submit w-100">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Masuk
                                    </button>
                                </form>

                                <div class="register-note">
                                    Belum punya akun?
                                    <a href="register.php">Daftar sekarang</a>
                                </div>

                            </div>
                        </section>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';

            this.innerHTML = isPassword
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';

            this.setAttribute(
                'aria-label',
                isPassword ? 'Sembunyikan password' : 'Tampilkan password'
            );
        });
    }
</script>

</body>
</html>