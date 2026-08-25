<?php
/**
 * login.php - Estate Prima
 * Menangani submit form login. Field form: email, password
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

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
            header('Location: ' . BASE_URL . 'admin-dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'index.php');
        }
        exit;
    }
}

$user = user_login();
$page_title = 'Masuk — Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>
<style>
    .hero-login {
        position: relative;
        min-height: 92vh;
        display: flex;
        align-items: center;
        background-image:
            linear-gradient(180deg, rgba(13,31,51,0.55) 0%, rgba(13,31,51,0.35) 40%, rgba(13,31,51,0.92) 100%),
            url('https://images.unsplash.com/photo-1759238136854-a43787126db7?fm=jpg&q=80&w=2200&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
    }
    .hero-login .breadcrumb-estate a { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem; }
    .hero-login .breadcrumb-estate a:hover { color: var(--gold-300); }
    .hero-login .breadcrumb-estate .sep { color: rgba(255,255,255,0.35); margin: 0 0.4rem; }
    .hero-login .breadcrumb-estate .current { color: var(--gold-300); font-size: 0.85rem; }
    .hero-login .hero-eyebrow { font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; font-size: 0.78rem; color: var(--gold-300); }
    .hero-login h1 { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(2.4rem, 5vw, 3.9rem); line-height: 1.08; color: #fff; }
    .hero-login h1 em { font-style: italic; color: var(--gold-300); }
    .hero-login p.lead { color: rgba(255,255,255,0.85); font-size: 1.08rem; max-width: 34rem; }
</style>

    <!-- ============ HERO ============ -->
    <header class="hero-login">
        <div class="container pb-5 text-center">
            <div class="breadcrumb-estate mb-3">
                <a href="index.php">Beranda</a>
                <span class="sep">/</span>
                <span class="current">Masuk</span>
            </div>
            <p class="hero-eyebrow mb-3">Selamat Datang Kembali</p>
            <h1 class="mb-3">Lanjutkan pencarian <em>hunian impianmu.</em></h1>
            <p class="lead mb-4 mx-auto">
                Masuk untuk mengelola wishlist, memantau status pengajuan pembelian,
                dan mengakses fitur khusus member Estate Prima.
            </p>

            <!-- Form login, mengambang di tengah halaman -->
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="floating-card field-panel mt-4 text-start">
                        <span class="corner-tick tl"></span>
                        <span class="corner-tick tr"></span>
                        <span class="corner-tick bl"></span>
                        <span class="corner-tick br"></span>

                        <p class="section-eyebrow mb-1">Masuk Akun</p>
                        <h2 class="mb-4" style="font-family:'Fraunces',serif; font-weight:600; font-size:1.4rem; color:var(--navy-900);">
                            Login ke Estate Prima
                        </h2>

                        <?php if (!empty($errors)): ?>
                            <div class="alert-estate-error p-3 mb-3">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label class="d-block">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="d-block">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-gold w-100 py-2 mb-3">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                            </button>
                            <p class="text-center text-muted small mb-0">
                                Belum punya akun? <a href="register.php" class="text-gold fw-bold text-decoration-none">Daftar di sini</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

<?php require_once __DIR__ . '/includes/footer.php'; ?>