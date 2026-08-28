<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
$pesan = $_GET['pesan'] ?? '';

// Search + pagination, pola sama kayak listing.php / admin-properti.php
$q = trim($_GET['q'] ?? '');
$data_per_halaman = 10;
$halaman = max((int)($_GET['page'] ?? 1), 1);
$offset = ($halaman - 1) * $data_per_halaman;

$where_sql = '';
$parameter = [];
$tipe_data = '';
if ($q !== '') {
    $where_sql = 'WHERE nama LIKE ? OR email LIKE ?';
    $keyword = "%{$q}%";
    $parameter = [$keyword, $keyword];
    $tipe_data = 'ss';
}

$stmt_total = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM users {$where_sql}");
if (!empty($parameter)) {
    mysqli_stmt_bind_param($stmt_total, $tipe_data, ...$parameter);
}
mysqli_stmt_execute($stmt_total);
$total_user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['total'];
$total_halaman = max((int)ceil($total_user_data / $data_per_halaman), 1);
mysqli_stmt_close($stmt_total);

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama, email, no_hp, role, created_at FROM users {$where_sql} ORDER BY created_at DESC LIMIT ? OFFSET ?"
);
$tipe_data_full = $tipe_data . 'ii';
$parameter_full = array_merge($parameter, [$data_per_halaman, $offset]);
mysqli_stmt_bind_param($stmt, $tipe_data_full, ...$parameter_full);
mysqli_stmt_execute($stmt);
$daftar_user = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$user = user_login();
$page_title = 'Kelola Users - Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'users';
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><div class="container"><p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Kelola Users</h1><p class="lead mb-0">Atur role dan akses akun pengguna.</p></div></div>
<main class="py-5"><div class="container">
<?php if (in_array($pesan, ['role-berhasil', 'hapus-berhasil', 'tambah-berhasil', 'edit-berhasil'], true)): ?><div class="alert-estate-success p-3 mb-4">Perubahan user berhasil disimpan.</div><?php endif; ?>
<div class="admin-form-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div><p class="section-eyebrow mb-2">Daftar Akun</p><h2 class="section-title mb-0" style="font-size:1.6rem;"><?= $total_user_data ?> User</h2></div>
        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" class="d-flex gap-2">
                <input type="search" name="q" class="form-control" placeholder="Cari nama/email..." value="<?= htmlspecialchars($q) ?>">
                <button type="submit" class="btn btn-outline-navy"><i class="bi bi-search"></i></button>
            </form>
            <a href="admin-user-tambah.php" class="btn btn-gold"><i class="bi bi-person-plus-fill me-1"></i> Tambah User</a>
        </div>
    </div>
    <div class="table-responsive"><table class="table-estate"><thead><tr><th>No</th><th>Nama</th><th>Email</th><th>No. HP</th><th>Role</th><th>Aksi</th></tr></thead><tbody>
    <?php if (empty($daftar_user)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada user ditemukan.</td></tr>
    <?php else: ?>
        <?php $nomor = $offset + 1; ?>
        <?php foreach ($daftar_user as $akun): ?>
            <tr>
                <td><?= $nomor++ ?></td>
                <td class="fw-bold"><?= htmlspecialchars($akun['nama']) ?></td>
                <td><?= htmlspecialchars($akun['email']) ?></td>
                <td><?= htmlspecialchars($akun['no_hp'] ?: '-') ?></td>
                <td><span class="badge-status <?= $akun['role'] === 'admin' ? 'terjual' : 'tersedia' ?>"><?= htmlspecialchars(ucfirst($akun['role'])) ?></span></td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="admin-user-edit.php?id=<?= $akun['id'] ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-pencil-fill"></i> Edit</a>
                        <?php if ((int) $akun['id'] === (int) $_SESSION['user_id']): ?>
                            <span class="badge rounded-pill text-bg-light border align-self-center"><i class="bi bi-person-check-fill me-1"></i>Akun Anda</span>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalHapusUser<?= $akun['id'] ?>"><i class="bi bi-trash-fill"></i></button>
                            <div class="modal fade" id="modalHapusUser<?= $akun['id'] ?>" tabindex="-1" aria-labelledby="labelHapusUser<?= $akun['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
                                    <div class="modal-header"><h2 class="modal-title fs-5" id="labelHapusUser<?= $akun['id'] ?>">Hapus User?</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                                    <div class="modal-body">Akun <strong><?= htmlspecialchars($akun['nama']) ?></strong> akan dihapus.</div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Batal</button>
                                        <form method="POST" action="proses-users.php">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= $akun['id'] ?>">
                                            <button class="btn btn-danger" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </div></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody></table></div>
    <?php if ($total_halaman > 1): ?>
        <nav class="pagination-estate mt-4" aria-label="Halaman users">
            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                <a href="admin-users.php?page=<?= $i ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="<?= $i === $halaman ? 'active' : '' ?>" aria-label="Halaman <?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</div>
</div></main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
