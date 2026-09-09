<?php
/**
 * admin-properti.php - Estate Prima
 * Daftar dan pengelolaan properti oleh admin.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

// Parameter Pencarian & Paginasi
$q = trim($_GET['q'] ?? '');
$data_per_halaman = 10;
$halaman = max((int)($_GET['page'] ?? 1), 1);
$offset = ($halaman - 1) * $data_per_halaman;

// Hitung total properti (dengan filter kata kunci jika ada)
if ($q !== '') {
    $stmt_count = mysqli_prepare($koneksi, 'SELECT COUNT(*) AS total FROM properti WHERE judul LIKE ? OR kota LIKE ?');
    $param_q = "%{$q}%";
    mysqli_stmt_bind_param($stmt_count, 'ss', $param_q, $param_q);
    mysqli_stmt_execute($stmt_count);
    $total_properti = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total'];
    mysqli_stmt_close($stmt_count);
} else {
    $total_properti = (int)mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM properti'))['total'];
}

$total_halaman = max((int)ceil($total_properti / $data_per_halaman), 1);

// Query ambil daftar properti
if ($q !== '') {
    $stmt = mysqli_prepare(
        $koneksi, 
        'SELECT id, judul, harga, harga_sewa, tipe, kota, status, tipe_transaksi, periode_sewa 
         FROM properti 
         WHERE judul LIKE ? OR kota LIKE ? 
         ORDER BY created_at DESC LIMIT ? OFFSET ?'
    );
    mysqli_stmt_bind_param($stmt, 'ssii', $param_q, $param_q, $data_per_halaman, $offset);
} else {
    $stmt = mysqli_prepare(
        $koneksi, 
        'SELECT id, judul, harga, harga_sewa, tipe, kota, status, tipe_transaksi, periode_sewa 
         FROM properti 
         ORDER BY created_at DESC LIMIT ? OFFSET ?'
    );
    mysqli_stmt_bind_param($stmt, 'ii', $data_per_halaman, $offset);
}

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
            <div>
                <p class="section-eyebrow mb-1">Daftar Properti</p>
                <h2 class="section-title mb-0" style="font-size:1.6rem;"><?= $total_properti ?> Total Properti</h2>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <form method="GET" action="admin-properti.php" class="d-flex gap-2">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari judul / kota..." value="<?= htmlspecialchars($q) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button>
                    <?php if ($q !== ''): ?>
                        <a href="admin-properti.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </form>
                <a href="properti-tambah.php" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i> Tambah Properti</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-estate">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Judul</th>
                        <th>Opsi Transaksi</th>
                        <th>Harga</th>
                        <th>Tipe</th>
                        <th>Kota</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($daftar_properti)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data properti.</td></tr>
                <?php else: $nomor = $offset + 1; foreach ($daftar_properti as $properti): 
                    $tipe_tx = $properti['tipe_transaksi'] ?? 'jual';
                ?>
                    <tr>
                        <td><?= $nomor++ ?></td>
                        <td class="property-name">
                            <a href="detail.php?id=<?= $properti['id'] ?>" target="_blank" class="text-decoration-none text-navy-900">
                                <?= htmlspecialchars($properti['judul']) ?> <i class="bi bi-box-arrow-up-right small text-muted"></i>
                            </a>
                        </td>
                        <td>
                            <?php if ($tipe_tx === 'jual'): ?>
                                <span class="badge badge-transaksi-jual">JUAL</span>
                            <?php elseif ($tipe_tx === 'sewa'): ?>
                                <span class="badge badge-transaksi-sewa">SEWA</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($tipe_tx === 'jual'): ?>
                                <div><small class="text-muted">Beli:</small> <strong>Rp <?= number_format((float)($properti['harga'] ?? 0), 0, ',', '.') ?></strong></div>
                            <?php endif; ?>
                            <?php if ($tipe_tx === 'sewa'): ?>
                                <div><small class="text-muted">Sewa:</small> Rp <?= number_format((float)($properti['harga_sewa'] ?? 0), 0, ',', '.') ?> <small class="text-muted">/ <?= htmlspecialchars($properti['periode_sewa'] ?? 'bln') ?></small></div>
                            <?php endif; ?>
                        </td>
                        <td><?= ucfirst($properti['tipe']) ?></td>
                        <td><?= htmlspecialchars($properti['kota']) ?></td>
                        <td><span class="badge-status <?= htmlspecialchars($properti['status']) ?>"><i class="bi bi-circle-fill"></i> <?= ucfirst($properti['status']) ?></span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="properti-edit.php?id=<?= $properti['id'] ?>" class="btn btn-sm btn-outline-navy" title="Edit Data"><i class="bi bi-pencil-fill"></i></a>
                                <a href="properti-galeri.php?id=<?= $properti['id'] ?>" class="btn btn-sm btn-outline-navy" title="Kelola Galeri"><i class="bi bi-images"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $properti['id'] ?>" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                            </div>

                            <!-- Modal Konfirmasi Hapus -->
                            <div class="modal fade" id="modalHapus<?= $properti['id'] ?>" tabindex="-1" aria-labelledby="labelHapus<?= $properti['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title fs-5" id="labelHapus<?= $properti['id'] ?>">Hapus Properti?</h2>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            Properti <strong><?= htmlspecialchars($properti['judul']) ?></strong> beserta seluruh foto galerinya akan dihapus permanen.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Batal</button>
                                            <form method="POST" action="proses-properti.php">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id" value="<?= $properti['id'] ?>">
                                                <button type="submit" class="btn btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_halaman > 1): ?>
            <nav class="pagination-estate mt-4" aria-label="Halaman properti">
                <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                    <a href="admin-properti.php?page=<?= $i ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="<?= $i === $halaman ? 'active' : '' ?>" aria-label="Halaman <?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>