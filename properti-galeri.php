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
require_once __DIR__ . '/includes/header.php';
?>
    <div class="page-header page-header-photo"><div class="container">
        <p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Kelola Galeri</h1><div class="breadcrumb-estate"><a href="<?= BASE_URL ?>index.php">Beranda</a><span class="sep">/</span><a href="admin-properti.php">Kelola Properti</a><span class="sep">/</span><span class="current"><?= htmlspecialchars($properti['judul']) ?></span></div>
        <div class="admin-subnav"><a href="admin-dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a><a href="admin-properti.php" class="active"><i class="bi bi-houses-fill me-1"></i> Kelola Properti</a><a href="admin-transaksi.php"><i class="bi bi-receipt me-1"></i> Kelola Transaksi</a><a href="admin-agen.php"><i class="bi bi-person-badge-fill me-1"></i> Kelola Agen</a><a href="admin-pesan.php"><i class="bi bi-envelope-fill me-1"></i> Pesan Kontak</a></div>
    </div></div>
    <section class="py-5"><div class="container"><div class="admin-form-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><p class="section-eyebrow mb-2">Properti</p><h2 class="section-title mb-0">Galeri Foto</h2><p class="text-muted mb-0 mt-2"><?= htmlspecialchars($properti['judul']) ?></p></div><a href="admin-properti.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
        <p class="section-eyebrow mb-2">Tambah Media</p><h3 class="section-title mb-3" style="font-size:1.35rem;">Foto Baru</h3>
    <form method="POST" action="proses-galeri.php" class="row g-3 align-items-end">
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
                <form method="POST" action="proses-galeri.php"
                      onsubmit="return confirm('Hapus foto ini dari galeri?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                    <input type="hidden" name="properti_id" value="<?= $properti_id ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash3 me-1"></i> Hapus Foto</button>
                </form>
            </div></div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
