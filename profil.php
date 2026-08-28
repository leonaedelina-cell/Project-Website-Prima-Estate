<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login();
$user_id = (int) $_SESSION['user_id'];
$pesan = $_GET['pesan'] ?? '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if ($nama === '') $errors[] = 'Nama wajib diisi.';
    if ($password !== '' && strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $konfirmasi) $errors[] = 'Konfirmasi password tidak cocok.';

    if (!$errors) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, 'UPDATE users SET nama = ?, no_hp = ?, password = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'sssi', $nama, $no_hp, $hash, $user_id);
        } else {
            $stmt = mysqli_prepare($koneksi, 'UPDATE users SET nama = ?, no_hp = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'ssi', $nama, $no_hp, $user_id);
        }
        if (!mysqli_stmt_execute($stmt)) $errors[] = 'Profil gagal diperbarui.';
        mysqli_stmt_close($stmt);
        if (!$errors) {
            $_SESSION['nama'] = $nama;
            header('Location: profil.php?pesan=berhasil');
            exit;
        }
    }
}

$stmt = mysqli_prepare($koneksi, 'SELECT nama, email, no_hp FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$profil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$user = user_login();
$page_title = 'Profil Saya - Estate Prima';
$dashboard_sidebar = true;
$dashboard_sidebar_active = 'profil';
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><div class="container"><p class="eyebrow mb-2">Akun Saya</p><h1 class="mb-2">Profil Saya</h1><p class="lead mb-0">Kelola informasi akun dan password Anda.</p></div></div>
<main class="py-5"><div class="container"><div class="admin-form-card">
<?php if ($pesan === 'berhasil'): ?><div class="alert-estate-success p-3 mb-4">Profil berhasil diperbarui.</div><?php endif; ?>
<?php if ($errors): ?><div class="alert-estate-error p-3 mb-4"><ul class="mb-0"><li><?= htmlspecialchars(implode(' ', $errors)) ?></li></ul></div><?php endif; ?>
<form method="POST" action="profil.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><div class="row g-3">
<div class="col-12 field-panel"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($profil['nama'] ?? '') ?>" required></div>
<div class="col-md-6 field-panel"><label class="form-label" for="email">Email</label><input class="form-control" id="email" value="<?= htmlspecialchars($profil['email'] ?? '') ?>" disabled></div>
<div class="col-md-6 field-panel"><label class="form-label" for="no_hp">No. HP</label><input class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($profil['no_hp'] ?? '') ?>"></div>
<div class="col-md-6 field-panel"><label class="form-label" for="password">Password Baru (opsional)</label><input class="form-control" id="password" type="password" name="password"></div>
<div class="col-md-6 field-panel"><label class="form-label" for="konfirmasi_password">Konfirmasi Password</label><input class="form-control" id="konfirmasi_password" type="password" name="konfirmasi_password"></div>
</div><div class="form-actions"><button class="btn btn-gold" type="submit"><i class="bi bi-check2-circle me-1"></i> Simpan Profil</button></div></form>
</div></div></main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>