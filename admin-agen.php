<?php
/**
 * admin-agen.php - Estate Prima
 * List semua agen sales + tombol tambah/edit/hapus.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$q = trim($_GET['q'] ?? '');
$per_page = 10;
$page = max((int)($_GET['page'] ?? 1), 1);
$offset = ($page - 1) * $per_page;
$like = "%{$q}%";
$where = $q !== '' ? ' WHERE a.nama LIKE ? OR a.email LIKE ?' : '';
$count = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM agen a{$where}");
if ($q !== '') mysqli_stmt_bind_param($count, 'ss', $like, $like);
mysqli_stmt_execute($count); $total_agen = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count))['total']; mysqli_stmt_close($count);
$total_halaman = max((int)ceil($total_agen / $per_page), 1);
$stmt = mysqli_prepare($koneksi, "SELECT a.id, a.nama, a.no_hp, a.email, COUNT(p.id) AS jumlah_properti FROM agen a LEFT JOIN properti p ON p.agen_id = a.id{$where} GROUP BY a.id, a.nama, a.no_hp, a.email ORDER BY a.nama LIMIT ? OFFSET ?");
if ($q !== '') mysqli_stmt_bind_param($stmt, 'ssii', $like, $like, $per_page, $offset);
else mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
mysqli_stmt_execute($stmt); $daftar_agen = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC); mysqli_stmt_close($stmt);

$pesan = $_GET['pesan'] ?? '';

$user = user_login();
$page_title = 'Kelola Agen — Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'agen';
require_once __DIR__ . '/includes/header.php';
?>
    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Kelola Agen Sales</h1>
            <p class="lead mb-0">Kelola data agen sales dan lihat jumlah properti yang mereka tangani.</p>

        </div>
    </div>

    <main class="py-5">
        <div class="container">

            <?php if ($pesan === 'tambah-berhasil'): ?>
                <div class="alert-estate-success p-3 mb-4"><i class="bi bi-check-circle-fill me-1"></i> Agen berhasil ditambahkan.</div>
            <?php elseif ($pesan === 'edit-berhasil'): ?>
                <div class="alert-estate-success p-3 mb-4"><i class="bi bi-check-circle-fill me-1"></i> Agen berhasil diupdate.</div>
            <?php elseif ($pesan === 'hapus-berhasil'): ?>
                <div class="alert-estate-success p-3 mb-4"><i class="bi bi-check-circle-fill me-1"></i> Agen berhasil dihapus.</div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <p class="section-eyebrow mb-2">Daftar Agen</p>
                    <h2 class="section-title mb-0" style="font-size:1.6rem;"><?= $total_agen ?> Agen Sales</h2>
                </div>
                <div class="d-flex gap-2"><form method="GET" class="d-flex gap-2"><input class="form-control form-control-sm" name="q" placeholder="Cari nama / email..." value="<?= htmlspecialchars($q) ?>"><button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button></form><a href="agen-tambah.php" class="btn btn-gold px-4 py-2">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Agen
                </a></div>
            </div>

            <div class="table-responsive">
                <table class="table-estate">
                    <thead>
                        <tr>
                            <th>No.</th><th>Nama</th><th>No. HP</th><th>Email</th><th>Jumlah Properti</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php if (empty($daftar_agen)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada agen sales.</td></tr>
    <?php else: ?>
        <?php $nomor = $offset + 1; foreach ($daftar_agen as $a): ?>
            <tr>
                <td><?= $nomor++ ?></td>
                <td class="fw-bold" style="color:var(--navy-900);"><?= htmlspecialchars($a['nama']) ?></td>
                <td><?= htmlspecialchars($a['no_hp'] ?? '-') ?></td>
                <td><?= htmlspecialchars($a['email'] ?? '-') ?></td>
                <td><?= $a['jumlah_properti'] ?></td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="agen-edit.php?id=<?= $a['id'] ?>" class="btn-mini btn-edit">
                            <i class="bi bi-pencil-fill"></i> Edit
                        </a>
                        <button type="button" class="btn-mini btn-hapus" data-bs-toggle="modal" data-bs-target="#modalHapusAgen<?= $a['id'] ?>">
                            <i class="bi bi-trash-fill"></i> Hapus
                        </button>
                    </div>

                    <!-- Modal Hapus Agen -->
                    <div class="modal fade" id="modalHapusAgen<?= $a['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-start">
                                <div class="modal-header">
                                    <h5 class="modal-title">Hapus Agen?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <?php if ($a['jumlah_properti'] > 0): ?>
                                        <p class="text-danger fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perhatian!</p>
                                        <p class="mb-0">Agen ini masih memegang <strong><?= $a['jumlah_properti'] ?> properti</strong>. Jika dihapus, properti tersebut akan diubah menjadi <em>Tanpa Agen</em>.</p>
                                    <?php else: ?>
                                        Apakah Anda yakin ingin menghapus agen <strong><?= htmlspecialchars($a['nama']) ?></strong>?
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-outline-navy" data-bs-dismiss="modal">Batal</button>
                                    <form method="POST" action="proses-agen.php" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Ya, Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
                </table>
            </div>
            <?php if ($total_halaman > 1): ?>
                <nav class="pagination-estate mt-4"><?php for ($i = 1; $i <= $total_halaman; $i++): ?><a class="<?= $i === $page ? 'active' : '' ?>" href="?q=<?= urlencode($q) ?>&page=<?= $i ?>"><?= $i ?></a><?php endfor; ?></nav>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>