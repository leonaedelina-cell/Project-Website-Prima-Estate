<?php
/**
 * pages/admin/properti-edit.php - Estate Prima
 * Form edit properti yang sudah ada. Akses: properti-edit.php?id=1
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Properti tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM properti WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$properti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$properti) {
    die('Properti tidak ditemukan.');
}

$daftar_agen = mysqli_fetch_all(mysqli_query($koneksi, "SELECT id, nama FROM agen ORDER BY nama"), MYSQLI_ASSOC);

$user = user_login();
$page_title = 'Edit Properti - Estate Prima';
require_once __DIR__ . '/includes/header.php';

// Helper kecil biar gak nulis berkali-kali: cek apakah suatu <option> harus 'selected'
function is_selected($a, $b) {
    return $a == $b ? 'selected' : '';
}
?>
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Edit Properti</h1>
            <div class="breadcrumb-estate">
                <a href="<?= BASE_URL ?>index.php">Beranda</a><span class="sep">/</span>
                <a href="admin-properti.php">Kelola Properti</a><span class="sep">/</span>
                <span class="current"><?= htmlspecialchars($properti['judul']) ?></span>
            </div>
            <div class="admin-subnav">
                <a href="admin-dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                <a href="admin-properti.php" class="active"><i class="bi bi-houses-fill me-1"></i> Kelola Properti</a>
                <a href="admin-transaksi.php"><i class="bi bi-receipt me-1"></i> Kelola Transaksi</a>
                <a href="admin-agen.php"><i class="bi bi-person-badge-fill me-1"></i> Kelola Agen</a>
                <a href="admin-pesan.php"><i class="bi bi-envelope-fill me-1"></i> Pesan Kontak</a>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <div class="admin-form-card">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div><p class="section-eyebrow mb-2">Data Properti</p><h2 class="section-title mb-0">Edit Properti</h2></div>
                    <a href="admin-properti.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
                <form method="POST" action="proses-properti.php">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id" value="<?= $properti['id'] ?>">

        <div class="row g-3">
            <div class="col-12 field-panel"><label class="form-label">Judul</label><input class="form-control" type="text" name="judul" value="<?= htmlspecialchars($properti['judul']) ?>" required></div>
            <div class="col-12 field-panel"><label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="4"><?= htmlspecialchars($properti['deskripsi']) ?></textarea></div>
            <div class="col-md-6 field-panel"><label class="form-label">Harga (Rp)</label><input class="form-control" type="number" name="harga" value="<?= $properti['harga'] ?>" required min="0"></div>
            <div class="col-md-3 field-panel"><label class="form-label">Tipe</label><select class="form-select" name="tipe" required>
                <?php foreach (['rumah', 'apartemen', 'tanah', 'ruko'] as $t): ?>
                    <option value="<?= $t ?>" <?= is_selected($properti['tipe'], $t) ?>><?= ucfirst($t) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="col-md-3 field-panel"><label class="form-label">Status</label><select class="form-select" name="status" required>
                <option value="tersedia" <?= is_selected($properti['status'], 'tersedia') ?>>Tersedia</option>
                <option value="terjual" <?= is_selected($properti['status'], 'terjual') ?>>Terjual</option>
            </select></div>
            <div class="col-md-8 field-panel"><label class="form-label">Alamat</label><input class="form-control" type="text" name="alamat" value="<?= htmlspecialchars($properti['alamat']) ?>" required></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kota</label><input class="form-control" type="text" name="kota" value="<?= htmlspecialchars($properti['kota']) ?>" required></div>
            <div class="col-md-6 field-panel"><label class="form-label">Latitude</label><input class="form-control" type="text" name="lat" value="<?= htmlspecialchars($properti['lat'] ?? '') ?>"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Longitude</label><input class="form-control" type="text" name="lng" value="<?= htmlspecialchars($properti['lng'] ?? '') ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Luas Tanah (m2)</label><input class="form-control" type="number" name="luas_tanah" value="<?= $properti['luas_tanah'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Luas Bangunan (m2)</label><input class="form-control" type="number" name="luas_bangunan" value="<?= $properti['luas_bangunan'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kamar Tidur</label><input class="form-control" type="number" name="kamar_tidur" value="<?= $properti['kamar_tidur'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kamar Mandi</label><input class="form-control" type="number" name="kamar_mandi" value="<?= $properti['kamar_mandi'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Carport</label><input class="form-control" type="number" name="carport" value="<?= $properti['carport'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">URL Gambar Utama</label><input class="form-control" type="text" name="gambar_url" value="<?= htmlspecialchars($properti['gambar_url'] ?? '') ?>"></div>
            <div class="col-12 field-panel"><label class="form-label">Agen</label><select class="form-select" name="agen_id">
                <option value="">- Tanpa Agen -</option>
                <?php foreach ($daftar_agen as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= is_selected($properti['agen_id'], $a['id']) ?>>
                        <?= htmlspecialchars($a['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-check2-circle me-1"></i> Update Properti</button></div>
                </form>
            </div>
        </div>
    </section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
