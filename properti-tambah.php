<?php
/**
 * pages/admin/properti-tambah.php - Estate Prima
 * Form tambah properti baru.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

// Ambil daftar agen buat dropdown
$daftar_agen = mysqli_fetch_all(mysqli_query($koneksi, "SELECT id, nama FROM agen ORDER BY nama"), MYSQLI_ASSOC);

$user = user_login();
$page_title = 'Tambah Properti - Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Tambah Properti</h1>
            <div class="breadcrumb-estate">
                <a href="<?= BASE_URL ?>index.php">Beranda</a><span class="sep">/</span>
                <a href="admin-properti.php">Kelola Properti</a><span class="sep">/</span>
                <span class="current">Tambah Properti</span>
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
                    <div><p class="section-eyebrow mb-2">Data Properti</p><h2 class="section-title mb-0">Tambah Properti Baru</h2></div>
                    <a href="admin-properti.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
                <form method="POST" action="proses-properti.php">
        <input type="hidden" name="aksi" value="tambah">

        <div class="row g-3">
            <div class="col-12 field-panel"><label class="form-label">Judul</label><input class="form-control" type="text" name="judul" required></div>
            <div class="col-12 field-panel"><label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="4"></textarea></div>
            <div class="col-md-6 field-panel"><label class="form-label">Harga (Rp)</label><input class="form-control" type="number" name="harga" required min="0"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Tipe</label><select class="form-select" name="tipe" required>
                <option value="rumah">Rumah</option>
                <option value="apartemen">Apartemen</option>
                <option value="tanah">Tanah</option>
                <option value="ruko">Ruko</option>
            </select></div>
            <div class="col-md-8 field-panel"><label class="form-label">Alamat</label><input class="form-control" type="text" name="alamat" required></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kota</label><input class="form-control" type="text" name="kota" required></div>
            <div class="col-md-6 field-panel"><label class="form-label">Latitude (opsional, buat Maps)</label><input class="form-control" type="text" name="lat"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Longitude (opsional, buat Maps)</label><input class="form-control" type="text" name="lng"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Luas Tanah (m2)</label><input class="form-control" type="number" name="luas_tanah"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Luas Bangunan (m2)</label><input class="form-control" type="number" name="luas_bangunan"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kamar Tidur</label><input class="form-control" type="number" name="kamar_tidur" value="0"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kamar Mandi</label><input class="form-control" type="number" name="kamar_mandi" value="0"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Carport</label><input class="form-control" type="number" name="carport" value="0"></div>
            <div class="col-md-4 field-panel"><label class="form-label">URL Gambar Utama</label><input class="form-control" type="text" name="gambar_url" placeholder="https://..."></div>
            <div class="col-12 field-panel"><label class="form-label">Agen</label><select class="form-select" name="agen_id">
                <option value="">- Tanpa Agen -</option>
                <?php foreach ($daftar_agen as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama']) ?></option>
                <?php endforeach; ?>
            </select></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-house-add-fill me-1"></i> Simpan Properti</button></div>
                </form>
            </div>
        </div>
    </section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
