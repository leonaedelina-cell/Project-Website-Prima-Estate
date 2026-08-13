<?php
/**
 * register.php - Estate Prima
 * Menangani submit form register (dari register.html nanti).
 * Field form yang diharapkan: nama, email, password, konfirmasi_password, no_hp
 */

session_start();
require_once __DIR__ . '/config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar - Estate Prima</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--navy:#16324f;--navy-dark:#0d1f33;--gold:#c9a24b;--cream:#f7f5f0;--text:#20242b;--muted:#6b7280;--line:#e3e1da}
*{box-sizing:border-box}
body{margin:0;background:var(--cream);color:var(--text);font-family:"Manrope",sans-serif;-webkit-font-smoothing:antialiased}
.navbar-estate{background:var(--navy);padding:1rem 0}
.brand{color:#fff;font-family:"Fraunces",serif;font-size:1.45rem;font-weight:600;letter-spacing:.02em;text-decoration:none}
.brand span{color:var(--gold)}
.navbar-estate .nav-link{color:rgba(255,255,255,.78);font-size:.88rem;font-weight:700;margin:0 .5rem}
.navbar-estate .nav-link:hover,.navbar-estate .nav-link.active{color:#e0c179}
.navbar-toggler{border-color:rgba(255,255,255,.25)}
.navbar-toggler:focus{box-shadow:0 0 0 .2rem rgba(201,162,75,.18)}
.btn-gold{background:var(--gold);border-color:var(--gold);color:var(--navy-dark);font-weight:800}
.btn-gold:hover{background:#e0c179;border-color:#e0c179;color:var(--navy-dark)}
.btn-outline-gold{border:1px solid var(--gold);color:#e0c179;font-weight:700}
.btn-outline-gold:hover{background:var(--gold);border-color:var(--gold);color:var(--navy-dark)}
.register-section{min-height:calc(100vh - 73px);display:flex;align-items:center;padding:3.5rem 0;background:radial-gradient(circle at 88% 15%,rgba(201,162,75,.09),transparent 28%),var(--cream)}
.register-shell{width:100%;max-width:1080px;margin:auto}
.register-panel{overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:5px;box-shadow:0 22px 60px rgba(13,31,51,.1)}
.register-intro{min-height:650px;position:relative;overflow:hidden;padding:3.2rem;background:linear-gradient(180deg,rgba(13,31,51,.25),rgba(13,31,51,.94)),url("https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1400&q=80") center/cover;display:flex;flex-direction:column;justify-content:flex-end}
.register-intro:after{content:"";position:absolute;width:270px;height:270px;border:1px solid rgba(201,162,75,.3);border-radius:50%;right:-115px;top:-120px}
.intro-content{position:relative;z-index:2}
.eyebrow{color:var(--gold);font-size:.72rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
.register-intro h1{max-width:420px;margin:.75rem 0 1rem;color:#fff;font-family:"Fraunces",serif;font-size:clamp(2.3rem,4vw,3.25rem);font-weight:600;line-height:1.08}
.register-intro h1 em{color:#e0c179;font-style:italic}
.register-intro p{max-width:440px;margin:0;color:rgba(255,255,255,.72);font-size:.84rem;line-height:1.8}
.register-form-side{min-height:650px;padding:3rem;display:flex;align-items:center}
.form-inner{width:100%;max-width:440px;margin:auto}
.back-home{color:var(--muted);font-size:.74rem;font-weight:700;text-decoration:none}
.back-home:hover{color:var(--navy)}
.form-inner h2{color:var(--navy);font-family:"Fraunces",serif;font-size:2rem;font-weight:600;margin:.5rem 0 .55rem}
.form-intro{color:var(--muted);font-size:.8rem;line-height:1.7;margin-bottom:1.45rem}
.form-label{color:var(--navy);font-size:.66rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:.42rem}
.form-control{min-height:45px;border:1px solid var(--line);border-radius:3px;color:var(--text);font-size:.8rem;font-weight:600}
.form-control:focus{border-color:var(--gold);box-shadow:0 0 0 .2rem rgba(201,162,75,.15)}
.input-wrap{position:relative}.input-wrap .form-control{padding-right:2.8rem}
.input-icon,.toggle-password{position:absolute;right:.8rem;top:50%;transform:translateY(-50%);z-index:5;color:var(--gold)}
.toggle-password{border:0;background:transparent;padding:.25rem;color:var(--muted)}
.toggle-password:hover{color:var(--navy)}
.register-submit{min-height:47px;border-radius:3px;font-size:.82rem}
.alert-error{border:1px solid #f0d3d3;border-radius:3px;background:#fff5f5;color:#a33c3c;font-size:.77rem;line-height:1.55}
.login-note{margin-top:1.35rem;padding-top:1.2rem;border-top:1px dashed var(--line);color:var(--muted);font-size:.75rem;text-align:center}
.login-note a{color:var(--navy);font-weight:800;text-decoration:none}.login-note a:hover{color:var(--gold)}
@media(max-width:991.98px){.register-intro{min-height:350px}.register-form-side{min-height:auto}}
@media(max-width:767.98px){.register-section{min-height:calc(100vh - 69px);padding:2rem 0}.register-intro{min-height:290px;padding:2rem}.register-intro h1{font-size:2.3rem}.register-form-side{padding:2rem 1.25rem}}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-estate sticky-top">
<div class="container">
<a class="brand" href="index.php">ESTATE <span>PRIMA</span></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Buka menu"><i class="bi bi-list text-white fs-4"></i></button>
<div class="collapse navbar-collapse" id="mainNav">
<ul class="navbar-nav mx-auto my-3 my-lg-0">
<li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
<li class="nav-item"><a class="nav-link" href="listing.php">Properti</a></li>
<li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
</ul>
<div class="d-flex align-items-center gap-2">
<a href="login.php" class="btn btn-outline-gold btn-sm px-3">Masuk</a>
<a href="register.php" class="btn btn-gold btn-sm px-3">Daftar</a>
</div>
</div>
</div>
</nav>

<main class="register-section">
<div class="container">
<div class="register-shell">
<div class="register-panel">
<div class="row g-0">
<div class="col-lg-6">
<section class="register-intro">
<div class="intro-content">
<div class="eyebrow">Estate Prima / Member Area</div>
<h1>Mulai perjalanan <em>properti kamu.</em></h1>
<p>Buat akun Estate Prima untuk menyimpan properti pilihan, mengajukan pembelian, dan mendapatkan pengalaman pencarian properti yang lebih mudah.</p>
</div>
</section>
</div>
<div class="col-lg-6">
<section class="register-form-side">
<div class="form-inner">
<a href="index.php" class="back-home"><i class="bi bi-arrow-left me-1"></i>Kembali ke beranda</a>
<div class="eyebrow mt-4">Buat Akun</div>
<h2>Daftar sekarang</h2>
<p class="form-intro">Lengkapi data berikut untuk membuat akun Estate Prima.</p>

<?php if (!empty($errors)): ?>
<div class="alert alert-error mb-4" role="alert">
<div class="d-flex align-items-start gap-2"><i class="bi bi-exclamation-circle-fill mt-1"></i><div>
<?php foreach ($errors as $error): ?>
<div><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>
</div></div>
</div>
<?php endif; ?>

<form method="POST" action="register.php">
<div class="mb-3">
<label for="nama" class="form-label">Nama Lengkap</label>
<div class="input-wrap">
<input type="text" class="form-control" id="nama" name="nama" placeholder="Nama lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" autocomplete="name" required>
<i class="bi bi-person input-icon"></i>
</div>
</div>
<div class="mb-3">
<label for="email" class="form-label">Email</label>
<div class="input-wrap">
<input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="email" required>
<i class="bi bi-envelope input-icon"></i>
</div>
</div>
<div class="mb-3">
<label for="no_hp" class="form-label">No. HP <span class="text-secondary fw-normal">(opsional)</span></label>
<div class="input-wrap">
<input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" autocomplete="tel">
<i class="bi bi-phone input-icon"></i>
</div>
</div>
<div class="row g-3 mb-4">
<div class="col-md-6">
<label for="password" class="form-label">Password</label>
<div class="input-wrap">
<input type="password" class="form-control" id="password" name="password" placeholder="Min. 6 karakter" autocomplete="new-password" required>
<button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
</div>
</div>
<div class="col-md-6">
<label for="konfirmasi_password" class="form-label">Konfirmasi</label>
<div class="input-wrap">
<input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password" autocomplete="new-password" required>
<button type="button" class="toggle-password" data-target="konfirmasi_password" aria-label="Tampilkan konfirmasi password"><i class="bi bi-eye"></i></button>
</div>
</div>
</div>
<button type="submit" class="btn btn-gold register-submit w-100"><i class="bi bi-person-plus-fill me-2"></i>Buat Akun</button>
</form>

<div class="login-note">Sudah punya akun? <a href="login.php">Masuk sekarang</a></div>
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
document.querySelectorAll('.toggle-password').forEach(function(button){
    button.addEventListener('click', function(){
        const input=document.getElementById(this.dataset.target);
        const visible=input.type==='text';
        input.type=visible?'password':'text';
        this.innerHTML=visible?'<i class="bi bi-eye"></i>':'<i class="bi bi-eye-slash"></i>';
        this.setAttribute('aria-label',visible?'Tampilkan password':'Sembunyikan password');
    });
});
</script>
</body>
</html>