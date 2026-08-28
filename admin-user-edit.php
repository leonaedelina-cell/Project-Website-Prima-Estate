<?php
/**
 * admin-user-edit.php - Estate Prima
 * Admin edit data akun user lain (nama, email, no HP). Ganti role/hapus tetap di admin-users.php.
 * Akses: admin-user-edit.php?id=1
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
cek_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('User tidak valid.');
}

$sedang_login = $id === (int) ($_SESSION['user_id'] ?? 0);

$stmt = mysqli_prepare($koneksi, 'SELECT id, nama, email, no_hp, role FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$akun) {
    die('User tidak ditemukan.');
}

$user = user_login();
$page_title = 'Edit User - Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'users';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="page-header page-header-photo"><div class="container">
        <p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Edit User</h1><p class="lead mb-0">Ubah nama, email, no HP, dan role akun <?= htmlspecialchars($akun['nama']) ?>.</p>
    </div></div>
    <main class="py-5"><div class="container"><div class="admin-form-card"><div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><p class="section-eyebrow mb-2">Data Akun</p><h2 class="section-title mb-0">Edit User</h2></div><a href="admin-users.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
    <form method="POST" action="proses-users.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id" value="<?= $akun['id'] ?>">
        <div class="row g-3">
            <div class="col-12 field-panel"><label class="form-label">Nama</label><input class="form-control" type="text" name="nama" value="<?= htmlspecialchars($akun['nama']) ?>" required></div>
            <div class="col-md-6 field-panel"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= htmlspecialchars($akun['email']) ?>" required></div>
            <div class="col-md-6 field-panel"><label class="form-label">No. HP</label><input class="form-control" type="text" name="no_hp" value="<?= htmlspecialchars($akun['no_hp'] ?? '') ?>"></div>
            <div class="col-md-6 field-panel">
                <label class="form-label">Role</label>
                <?php if ($sedang_login): ?>
                    <input class="form-control" type="text" value="<?= htmlspecialchars(ucfirst($akun['role'])) ?>" disabled>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($akun['role']) ?>">
                    <small class="text-muted">Role akun sendiri gak bisa diubah lewat sini.</small>
                <?php else: ?>
                    <select class="form-select" name="role" required>
                        <option value="user" <?= $akun['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $akun['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-check2-circle me-1"></i> Update User</button></div>
    </form>
    </div></div></main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
