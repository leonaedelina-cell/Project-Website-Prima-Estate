<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
$pesan = $_GET['pesan'] ?? '';
$daftar_user = mysqli_fetch_all(mysqli_query($koneksi, 'SELECT id, nama, email, no_hp, role, created_at FROM users ORDER BY created_at DESC'), MYSQLI_ASSOC);
$user = user_login();
$page_title = 'Kelola Users - Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'users';
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><div class="container"><p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Kelola Users</h1><p class="lead mb-0">Atur role dan akses akun pengguna.</p></div></div>
<main class="py-5"><div class="container">
<?php if ($pesan === 'role-berhasil' || $pesan === 'hapus-berhasil'): ?><div class="alert-estate-success p-3 mb-4">Perubahan user berhasil disimpan.</div><?php endif; ?>
<div class="admin-form-card"><div class="d-flex justify-content-between align-items-center mb-4"><div><p class="section-eyebrow mb-2">Daftar Akun</p><h2 class="section-title mb-0" style="font-size:1.6rem;"><?= count($daftar_user) ?> User</h2></div></div>
<div class="table-responsive"><table class="table-estate"><thead><tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Role</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($daftar_user as $akun): ?><tr><td class="fw-bold"><?= htmlspecialchars($akun['nama']) ?></td><td><?= htmlspecialchars($akun['email']) ?></td><td><?= htmlspecialchars($akun['no_hp'] ?: '-') ?></td><td><span class="badge-status <?= $akun['role'] === 'admin' ? 'terjual' : 'tersedia' ?>"><?= htmlspecialchars(ucfirst($akun['role'])) ?></span></td><td><?php if ((int) $akun['id'] === (int) $_SESSION['user_id']): ?><span class="text-muted small">Akun Anda</span><?php else: ?><div class="d-flex gap-2"><form method="POST" action="proses-users.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="aksi" value="ubah-role"><input type="hidden" name="id" value="<?= $akun['id'] ?>"><input type="hidden" name="role" value="<?= $akun['role'] === 'admin' ? 'user' : 'admin' ?>"><button class="btn btn-sm btn-outline-navy" type="submit">Jadikan <?= $akun['role'] === 'admin' ? 'User' : 'Admin' ?></button></form><button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalHapusUser<?= $akun['id'] ?>"><i class="bi bi-trash-fill"></i></button></div><div class="modal fade" id="modalHapusUser<?= $akun['id'] ?>" tabindex="-1" aria-labelledby="labelHapusUser<?= $akun['id'] ?>" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="labelHapusUser<?= $akun['id'] ?>">Hapus User?</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body">Akun <strong><?= htmlspecialchars($akun['nama']) ?></strong> akan dihapus.</div><div class="modal-footer"><button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Batal</button><form method="POST" action="proses-users.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $akun['id'] ?>"><button class="btn btn-danger" type="submit">Hapus</button></form></div></div></div></div><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></div></main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>