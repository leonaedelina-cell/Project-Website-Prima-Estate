<?php
/**
 * admin-user-tambah.php - Estate Prima
 * Admin bikin akun user/admin baru dari panel admin (langsung pilih role).
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
cek_admin();

$user = user_login();
$page_title = 'Tambah User - Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'users';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="page-header page-header-photo"><div class="container">
        <p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Tambah User</h1><div class="breadcrumb-estate"><a href="<?= BASE_URL ?>index.php">Beranda</a><span class="sep">/</span><a href="admin-users.php">Kelola Users</a><span class="sep">/</span><span class="current">Tambah User</span></div>
    </div></div>
    <main class="py-5"><div class="container"><div class="admin-form-card"><div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><p class="section-eyebrow mb-2">Data Akun</p><h2 class="section-title mb-0">Tambah User Baru</h2></div><a href="admin-users.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
    <form method="POST" action="proses-users.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="aksi" value="tambah">
        <div class="row g-3">
            <div class="col-12 field-panel"><label class="form-label">Nama</label><input class="form-control" type="text" name="nama" required></div>
            <div class="col-md-6 field-panel"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
            <div class="col-md-6 field-panel"><label class="form-label">No. HP</label><input class="form-control" type="text" name="no_hp"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required minlength="6"><small class="text-muted">Minimal 6 karakter.</small></div>
            <div class="col-md-6 field-panel"><label class="form-label">Role</label><select class="form-select" name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-person-plus-fill me-1"></i> Simpan User</button></div>
    </form>
    </div></div></main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
