<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
cek_admin();
$user = user_login();
$page_title = 'Tambah User — Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'users';
require_once __DIR__ . '/includes/header.php';
?>
<header class="page-header"><div class="container">
    <p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Tambah User</h1>
    <p class="lead mb-0">Buat akun baru dan tentukan role aksesnya.</p>
</div></header>
<main class="py-5"><div class="container"><div class="admin-form-card">
    <div class="d-flex justify-content-between align-items-start mb-4"><div><p class="section-eyebrow mb-2">Data Akun</p><h2 class="section-title mb-0">User Baru</h2></div><a href="admin-users.php" class="btn btn-outline-navy">Kembali</a></div>
    <form method="POST" action="proses-users.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="aksi" value="tambah">
        <div class="row g-3">
            <div class="col-md-6 field-panel"><label class="form-label">Nama</label><input class="form-control" name="nama" required></div>
            <div class="col-md-6 field-panel"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
            <div class="col-md-6 field-panel"><label class="form-label">No. HP</label><input class="form-control" name="no_hp"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Role</label><select class="form-select" name="role"><option value="user">User</option><option value="admin">Admin</option></select></div>
            <div class="col-12 field-panel"><label class="form-label">Password</label><input class="form-control" type="password" name="password" minlength="6" required></div>
        </div>
        <button class="btn btn-gold mt-4" type="submit">Simpan User</button>
    </form>
</div></div></main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
