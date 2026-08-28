<?php
/**
 * admin-agen.php - Estate Prima
 * List semua agen sales + tombol tambah/edit/hapus.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$q = trim($_GET['q'] ?? '');
$data_per_halaman = 10;
$halaman = max((int)($_GET['page'] ?? 1), 1);
$offset = ($halaman - 1) * $data_per_halaman;

$where_sql = '';
$parameter = [];
$tipe_data = '';
if ($q !== '') {
    $where_sql = 'WHERE a.nama LIKE ? OR a.email LIKE ?';
    $keyword = "%{$q}%";
    $parameter = [$keyword, $keyword];
    $tipe_data = 'ss';
}

$stmt_total = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM agen a {$where_sql}");
if (!empty($parameter)) {
    mysqli_stmt_bind_param($stmt_total, $tipe_data, ...$parameter);
}
mysqli_stmt_execute($stmt_total);
$total_agen = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['total'];
$total_halaman = max((int)ceil($total_agen / $data_per_halaman), 1);
mysqli_stmt_close($stmt_total);

// Sekalian hitung berapa properti yang dipegang tiap agen (biar admin tau dampak sebelum hapus)
$stmt = mysqli_prepare($koneksi,
    "SELECT a.id, a.nama, a.no_hp, a.email, COUNT(p.id) AS jumlah_properti
     FROM agen a
     LEFT JOIN properti p ON p.agen_id = a.id
     {$where_sql}
     GROUP BY a.id, a.nama, a.no_hp, a.email
     ORDER BY a.nama
     LIMIT ? OFFSET ?"
);
$tipe_data_full = $tipe_data . 'ii';
$parameter_full = array_merge($parameter, [$data_per_halaman, $offset]);
mysqli_stmt_bind_param($stmt, $tipe_data_full, ...$parameter_full);
mysqli_stmt_execute($stmt);
$daftar_agen = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$pesan = $_GET['pesan'] ?? '';

$user = user_login();
$page_title = 'Kelola Agen — Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'agen';
require_once __DIR__ . '/includes/header.php';
?>
<style>
    .page-header-photo {
        background-image:
            linear-gradient(180deg, rgba(13,31,51,0.72) 0%, rgba(13,31,51,0.6) 55%, rgba(13,31,51,0.94) 100%),
            url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?fm=jpg&q=80&w=2000&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
    }
    .table-estate {
        width: 100%; background: #fff; border-collapse: collapse;
        border: 1px solid var(--ivory-100); border-radius: 3px; overflow: hidden;
    }
    .table-estate thead { background: var(--navy-950); }
    .table-estate thead th {
        color: rgba(255,255,255,0.85); font-size: 0.72rem; font-weight: 700;
        letter-spacing: 0.08em; text-transform: uppercase; padding: 0.85rem 1rem; text-align: left;
    }
    .table-estate tbody td { padding: 0.85rem 1rem; border-top: 1px solid var(--ivory-100); font-size: 0.92rem; vertical-align: middle; }
    .table-estate tbody tr:hover { background: #fbf8f1; }
    .table-estate .btn-mini {
        font-size: 0.78rem; padding: 0.3rem 0.7rem; border-radius: 3px; font-weight: 700;
        text-decoration: none; display: inline-block; border: 1px solid var(--ivory-100);
    }
    .table-estate .btn-edit { color: var(--navy-900); }
    .table-estate .btn-edit:hover { border-color: var(--gold-500); color: var(--gold-600); }
    .table-estate .btn-hapus { color: #8a2c22; background: none; }
    .table-estate .btn-hapus:hover { background: #fbeceb; border-color: #f0bdb9; }
</style>

    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Kelola Agen Sales</h1>
            <div class="breadcrumb-estate">
                <a href="<?= BASE_URL ?>index.php">Beranda</a>
                <span class="sep">/</span>
                <span class="current">Kelola Agen</span>
            </div>

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
                <div class="d-flex gap-2 flex-wrap">
                    <form method="GET" class="d-flex gap-2">
                        <input type="search" name="q" class="form-control" placeholder="Cari nama/email..." value="<?= htmlspecialchars($q) ?>">
                        <button type="submit" class="btn btn-outline-navy"><i class="bi bi-search"></i></button>
                    </form>
                    <a href="agen-tambah.php" class="btn btn-gold px-4 py-2">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Agen
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-estate">
                    <thead>
                        <tr>
                            <th>No</th><th>Nama</th><th>No. HP</th><th>Email</th><th>Jumlah Properti</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_agen)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada agen ditemukan.</td></tr>
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
                                    </td>
                                </tr>
                                <div class="modal fade" id="modalHapusAgen<?= $a['id'] ?>" tabindex="-1" aria-labelledby="labelHapusAgen<?= $a['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h2 class="modal-title fs-5" id="labelHapusAgen<?= $a['id'] ?>">Hapus Agen?</h2>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <div class="modal-body">
                                                Agen <strong><?= htmlspecialchars($a['nama']) ?></strong> akan dihapus.
                                                <?php if ($a['jumlah_properti'] > 0): ?>
                                                    <p class="text-muted small mt-2 mb-0">Agen ini masih pegang <?= (int) $a['jumlah_properti'] ?> properti — propertinya akan jadi "Tanpa Agen".</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Batal</button>
                                                <form method="POST" action="proses-agen.php">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <input type="hidden" name="aksi" value="hapus">
                                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_halaman > 1): ?>
                <nav class="pagination-estate mt-4" aria-label="Halaman agen">
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <a href="admin-agen.php?page=<?= $i ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="<?= $i === $halaman ? 'active' : '' ?>" aria-label="Halaman <?= $i ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>