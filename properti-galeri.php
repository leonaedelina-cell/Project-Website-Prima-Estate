<?php
/**
 * pages/admin/properti-galeri.php - Estate Prima
 * Kelola galeri foto tambahan untuk 1 properti (banyak foto per properti).
 * Akses: properti-galeri.php?id=1
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$properti_id = (int)($_GET['id'] ?? 0);
if ($properti_id <= 0) {
    die('Properti tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "SELECT id, judul FROM properti WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $properti_id);
mysqli_stmt_execute($stmt);
$properti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$properti) {
    die('Properti tidak ditemukan.');
}

$stmt = mysqli_prepare($koneksi, "SELECT id, gambar_url FROM galeri_properti WHERE properti_id = ?");
mysqli_stmt_bind_param($stmt, "i", $properti_id);
mysqli_stmt_execute($stmt);
$galeri = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$user = user_login();
$page_title = 'Kelola Galeri - Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'properti';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="page-header page-header-photo"><div class="container">
        <p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Kelola Galeri</h1><p class="lead mb-0">Kelola koleksi foto tambahan buat properti <?= htmlspecialchars($properti['judul']) ?>.</p>
    </div></div>
    <main class="py-5"><div class="container"><div class="admin-form-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><p class="section-eyebrow mb-2">Properti</p><h2 class="section-title mb-0">Galeri Foto</h2><p class="text-muted mb-0 mt-2"><?= htmlspecialchars($properti['judul']) ?></p></div><a href="admin-properti.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
        <p class="section-eyebrow mb-2">Tambah Media</p><h3 class="section-title mb-3" style="font-size:1.35rem;">Foto Baru</h3>
    <form method="POST" action="proses-galeri.php" class="row g-3 align-items-end">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="aksi" value="tambah">
        <input type="hidden" name="properti_id" value="<?= $properti_id ?>">
        <div class="col-md-9 field-panel"><label class="form-label">URL Gambar</label><input class="form-control" type="text" name="gambar_url" placeholder="https://..." required></div>
        <div class="col-md-3"><button type="submit" class="btn btn-gold w-100"><i class="bi bi-plus-lg me-1"></i> Tambah Foto</button></div>
    </form>
    </div>
    <div class="d-flex justify-content-between align-items-end mb-3"><div><p class="section-eyebrow mb-2">Koleksi</p><h2 class="section-title mb-0" style="font-size:1.6rem;">Foto yang Sudah Ada</h2></div><span class="badge-status" style="background:#f1eee5;color:var(--navy-900);"><?= count($galeri) ?> Foto</span></div>
    <?php if (empty($galeri)): ?>
        <div class="admin-form-card text-center text-muted py-5"><i class="bi bi-images fs-2 d-block mb-2 text-gold"></i>Belum ada foto galeri.</div>
    <?php else: ?>
        <div class="gallery-admin-grid">
        <?php foreach ($galeri as $g): ?>
            <div class="gallery-admin-item"><img src="<?= htmlspecialchars($g['gambar_url']) ?>" alt="Galeri <?= htmlspecialchars($properti['judul']) ?>"><div class="item-footer"><div class="url mb-2" title="<?= htmlspecialchars($g['gambar_url']) ?>"><?= htmlspecialchars($g['gambar_url']) ?></div>
                <button type="button" class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#modalHapusFoto<?= $g['id'] ?>"><i class="bi bi-trash3 me-1"></i> Hapus Foto</button>
            </div></div>
            <div class="modal fade" id="modalHapusFoto<?= $g['id'] ?>" tabindex="-1" aria-labelledby="labelHapusFoto<?= $g['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="labelHapusFoto<?= $g['id'] ?>">Hapus Foto?</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">Foto ini akan dihapus dari galeri <strong><?= htmlspecialchars($properti['judul']) ?></strong>.</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Batal</button>
                            <form method="POST" action="proses-galeri.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                <input type="hidden" name="properti_id" value="<?= $properti_id ?>">
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div></main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
