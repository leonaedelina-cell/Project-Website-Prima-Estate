<?php
/**
 * register.php - Estate Prima
 * Menangani submit form register (dari register.html nanti).
 * Field form yang diharapkan: nama, email, password, konfirmasi_password, no_hp
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    cek_csrf();

    // 1. Ambil & bersihkan input
    $nama                = trim($_POST['nama'] ?? '');
    $email               = trim($_POST['email'] ?? '');
    $password            = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
    $no_hp               = trim($_POST['no_hp'] ?? '');

    // 2. Validasi
    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirmasi_password) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // 3. Cek email sudah dipakai atau belum (pakai prepared statement mysqli)
    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($hasil)) {
            $errors[] = 'Email sudah terdaftar, silakan pakai email lain atau login.';
        }
        mysqli_stmt_close($stmt);
    }

    // 4. Simpan kalau lolos validasi
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, 'user')"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $password_hash, $no_hp);
        mysqli_stmt_execute($stmt);

        // Langsung login-kan user setelah register (opsional, tapi umum dipakai)
        session_regenerate_id(true);
        $_SESSION['user_id'] = mysqli_insert_id($koneksi);
        $_SESSION['nama']    = $nama;
        $_SESSION['email']   = $email;
        $_SESSION['role']    = 'user';

        mysqli_stmt_close($stmt);

        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Kalau sampai sini berarti ada $errors atau method GET (baru buka halaman)
// -> nanti di sinilah tempat nge-include tampilan register.html / render pesan error

$user = user_login();
$page_title = 'Daftar Akun — Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>

    <!-- ============ HERO ============ -->
    <header class="hero-register">
        <div class="container pb-5 text-center">
            <div class="breadcrumb-estate mb-3">
                <a href="index.php">Beranda</a>
                <span class="sep">/</span>
                <span class="current">Daftar</span>
            </div>
            <p class="hero-eyebrow mb-3">Gabung Bersama Kami</p>
            <h1 class="mb-3">Mulai perjalanan <em>memiliki hunian.</em></h1>
            <p class="lead mb-4 mx-auto">
                Daftar untuk menyimpan wishlist properti favoritmu dan mengajukan
                pembelian langsung dari platform Estate Prima.
            </p>

            <!-- Form register, mengambang di tengah halaman -->
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="floating-card field-panel mt-4 text-start">
                        <span class="corner-tick tl"></span>
                        <span class="corner-tick tr"></span>
                        <span class="corner-tick bl"></span>
                        <span class="corner-tick br"></span>

                        <p class="section-eyebrow mb-1">Buat Akun</p>
                        <h2 class="mb-4" style="font-family:'Fraunces',serif; font-weight:600; font-size:1.4rem; color:var(--navy-900);">
                            Daftar ke Estate Prima
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

                        <form method="POST" action="register.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <div class="mb-3">
                                <label class="d-block">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control"
                                       value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="d-block">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="d-block">No. HP (opsional)</label>
                                <input type="text" name="no_hp" class="form-control"
                                       value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="d-block">Password</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Minimal 6 karakter.</small>
                            </div>
                            <div class="mb-4">
                                <label class="d-block">Konfirmasi Password</label>
                                <input type="password" name="konfirmasi_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-gold w-100 py-2 mb-3">
                                <i class="bi bi-person-plus-fill me-1"></i> Daftar
                            </button>
                            <p class="text-center text-muted small mb-0">
                                Sudah punya akun? <a href="login.php" class="text-gold fw-bold text-decoration-none">Masuk di sini</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

<?php require_once __DIR__ . '/includes/footer.php'; ?>