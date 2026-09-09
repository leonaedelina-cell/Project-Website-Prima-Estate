<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
cek_admin();

$q = trim($_GET['q'] ?? '');
$per_page = 10;
$page = max((int)($_GET['page'] ?? 1), 1);
$offset = ($page - 1) * $per_page;
$like = "%{$q}%";
$where = $q !== '' ? ' WHERE nama LIKE ? OR email LIKE ?' : '';
$count = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM users{$where}");
if ($q !== '') mysqli_stmt_bind_param($count, 'ss', $like, $like);
mysqli_stmt_execute($count); $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count))['total']; mysqli_stmt_close($count);
$pages = max((int)ceil($total / $per_page), 1);
$stmt = mysqli_prepare($koneksi, "SELECT id, nama, email, no_hp, role, created_at FROM users{$where} ORDER BY created_at DESC LIMIT ? OFFSET ?");
if ($q !== '') mysqli_stmt_bind_param($stmt, 'ssii', $like, $like, $per_page, $offset);
else mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
mysqli_stmt_execute($stmt); $daftar_user = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC); mysqli_stmt_close($stmt);

$pesan = $_GET['pesan'] ?? '';
$pesan_sukses = [
    'tambah-berhasil' => 'User berhasil ditambahkan.',
    'edit-berhasil' => 'Data user berhasil diperbarui.',
    'hapus-berhasil' => 'User berhasil dihapus.',
][$pesan] ?? '';
$user = user_login(); $page_title = 'Kelola Users — Estate Prima'; $admin_sidebar = true; $dashboard_sidebar_active = 'users';
require_once __DIR__ . '/includes/header.php';
?>
<header class="page-header"><div class="container"><p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Kelola Users</h1><p class="lead mb-0">Atur data akun, role, dan akses pengguna.</p></div></header>
<main class="py-5"><div class="container">
<?php if ($pesan_sukses): ?><div class="alert-estate-success p-3 mb-4"><i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($pesan_sukses) ?></div><?php endif; ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3"><div><p class="section-eyebrow mb-2">Daftar Akun</p><h2 class="section-title mb-0" style="font-size:1.6rem;"><?= $total ?> User</h2></div><div class="d-flex gap-2"><form method="GET" class="d-flex gap-2"><input class="form-control form-control-sm" name="q" placeholder="Cari nama / email..." value="<?= htmlspecialchars($q) ?>"><button class="btn btn-sm btn-outline-navy" type="submit"><i class="bi bi-search"></i></button></form><a href="admin-user-tambah.php" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i> Tambah User</a></div></div>
<div class="table-responsive"><table class="table-estate"><thead><tr><th>No.</th><th>Nama</th><th>Email</th><th>No. HP</th><th>Role</th><th>Aksi</th></tr></thead><tbody>
<?php if (!$daftar_user): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada user.</td></tr><?php else: $nomor = $offset + 1; foreach ($daftar_user as $akun): ?>
<tr><td><?= $nomor++ ?></td><td class="fw-bold"><?= htmlspecialchars($akun['nama']) ?></td><td><?= htmlspecialchars($akun['email']) ?></td><td><?= htmlspecialchars($akun['no_hp'] ?: '-') ?></td><td><span class="badge rounded-pill <?= $akun['role'] === 'admin' ? 'text-bg-dark' : 'text-bg-secondary' ?>"><?= ucfirst($akun['role']) ?></span></td><td>
<div class="d-flex gap-2"><a class="btn btn-sm btn-outline-navy" href="admin-user-edit.php?id=<?= $akun['id'] ?>" title="Edit"><i class="bi bi-pencil-fill"></i></a><?php if ((int)$akun['id'] !== (int)$_SESSION['user_id']): ?><button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#hapusUser<?= $akun['id'] ?>" type="button" title="Hapus"><i class="bi bi-trash-fill"></i></button><?php else: ?><span class="badge rounded-pill text-bg-light border">Akun Anda</span><?php endif; ?></div>
<div class="modal fade" id="hapusUser<?= $akun['id'] ?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Hapus User?</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div><div class="modal-body">Akun <strong><?= htmlspecialchars($akun['nama']) ?></strong> akan dihapus permanen.</div><div class="modal-footer"><button class="btn btn-outline-navy" data-bs-dismiss="modal" type="button">Batal</button><form method="POST" action="proses-users.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $akun['id'] ?>"><button class="btn btn-danger" type="submit">Hapus Akun</button></form></div></div></div></div>
</td></tr>
<?php endforeach; endif; ?></tbody></table></div>
<?php if ($pages > 1): ?><nav class="pagination-estate mt-4"><?php for ($i=1;$i<=$pages;$i++): ?><a class="<?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a><?php endfor; ?></nav><?php endif; ?>
</div></main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
