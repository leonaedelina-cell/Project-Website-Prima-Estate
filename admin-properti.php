<?php
/**
 * admin-properti.php - Estate Prima
 * Daftar dan pengelolaan properti.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$data_per_halaman = 10;
$halaman = max((int)($_GET['page'] ?? 1), 1);
$offset = ($halaman - 1) * $data_per_halaman;
$total_properti = (int)mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM properti'))['total'];
$total_halaman = max((int)ceil($total_properti / $data_per_halaman), 1);
$stmt = mysqli_prepare($koneksi, 'SELECT id, judul, harga, tipe, kota, status FROM properti ORDER BY created_at DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($stmt, 'ii', $data_per_halaman, $offset);
mysqli_stmt_execute($stmt);
$daftar_properti = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$pesan = $_GET['pesan'] ?? '';
$user = user_login();
$page_title = 'Kelola Properti — Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'properti';
require_once __DIR__ . '/includes/header.php';
?>
<style>
    .page-header-photo { background-image: linear-gradient(180deg, rgba(13,31,51,0.72), rgba(13,31,51,0.94)), url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?fm=jpg&q=80&w=2000&auto=format&fit=crop'); background-size:cover; background-position:center; }
    .table-estate { width:100%; background:#fff; border:1px solid var(--ivory-100); border-radius:3px; overflow:hidden; }
    .table-estate thead { background:var(--navy-950); }
    .table-estate th { color:rgba(255,255,255,0.85); font-size:.72rem; letter-spacing:.08em; text-transform:uppercase; padding:.85rem 1rem; white-space:nowrap; }
    .table-estate td { padding:.85rem 1rem; border-top:1px solid var(--ivory-100); vertical-align:middle; }
    .table-estate tr:hover { background:#fbf8f1; }
    .property-name { color:var(--navy-900); font-weight:700; }
</style>

<div class="page-header page-header-photo">
    <div class="container">
        <p class="eyebrow mb-2">Panel Admin</p>
        <h1 class="mb-2">Kelola Properti</h1>
        <p class="lead mb-0">Tambah, ubah, dan kelola koleksi properti Estate Prima.</p>
    </div>
</div>

<main class="py-5">
    <div class="container">
        <?php if ($pesan === 'tambah-berhasil' || $pesan === 'edit-berhasil' || $pesan === 'hapus-berhasil'): ?>
            <div class="alert-estate-success p-3 mb-4"><i class="bi bi-check-circle-fill me-1"></i> Perubahan properti berhasil disimpan.</div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div><p class="section-eyebrow mb-2">Daftar Properti</p><h2 class="section-title mb-0" style="font-size:1.6rem;"><?= count($daftar_properti) ?> Properti</h2></div>
            <a href="properti-tambah.php" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i> Tambah Properti</a>
        </div>
            <div class="table-responsive">
            <table class="table-estate">
                <thead><tr><th>Judul</th><th>Harga</th><th>Tipe</th><th>Kota</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($daftar_properti)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada properti.</td></tr>
                <?php else: foreach ($daftar_properti as $properti): ?>
                    <tr>
                        <td class="property-name"><?= htmlspecialchars($properti['judul']) ?></td>
                        <td>Rp <?= number_format($properti['harga'], 0, ',', '.') ?></td>
                        <td><?= ucfirst($properti['tipe']) ?></td>
                        <td><?= htmlspecialchars($properti['kota']) ?></td>
                        <td><span class="badge-status <?= htmlspecialchars($properti['status']) ?>"><i class="bi bi-circle-fill"></i> <?= ucfirst($properti['status']) ?></span></td>
                        <td><div class="d-flex gap-2"><a href="properti-edit.php?id=<?= $properti['id'] ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-pencil-fill"></i> Edit</a><a href="properti-galeri.php?id=<?= $properti['id'] ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-images"></i> Galeri</a><button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $properti['id'] ?>"><i class="bi bi-trash-fill"></i> Hapus</button></div><div class="modal fade" id="modalHapus<?= $properti['id'] ?>" tabindex="-1" aria-labelledby="labelHapus<?= $properti['id'] ?>" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="labelHapus<?= $properti['id'] ?>">Hapus Properti?</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body">Properti <strong><?= htmlspecialchars($properti['judul']) ?></strong> dan data terkait akan dihapus.</div><div class="modal-footer"><button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Batal</button><form method="POST" action="proses-properti.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $properti['id'] ?>"><button type="submit" class="btn btn-danger">Hapus</button></form></div></div></div></div></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_halaman > 1): ?><nav class="pagination-estate mt-4" aria-label="Halaman properti"><?php for ($i = 1; $i <= $total_halaman; $i++): ?><a href="admin-properti.php?page=<?= $i ?>" class="<?= $i === $halaman ? 'active' : '' ?>" aria-label="Halaman <?= $i ?>"><?= $i ?></a><?php endfor; ?></nav><?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
