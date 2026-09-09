<?php
/**
 * pages/admin/properti-edit.php - Estate Prima
 * Form edit properti yang sudah ada.
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
$admin_sidebar = true;
$dashboard_sidebar_active = 'properti';
require_once __DIR__ . '/includes/header.php';

function is_selected($a, $b) {
    return $a == $b ? 'selected' : '';
}
?>
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Edit Properti</h1>
            <p class="lead mb-0">Perbarui informasi properti, transaksi, fasilitas, dan gambar utama.</p>
        </div>
    </div>

    <main class="py-5">
        <div class="container">
            <div class="admin-form-card">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div><p class="section-eyebrow mb-2">Data Properti</p><h2 class="section-title mb-0">Edit Properti</h2></div>
                    <a href="admin-properti.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
                <form method="POST" action="proses-properti.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" value="<?= $properti['id'] ?>">
                    <input type="hidden" name="gambar_url_lama" value="<?= htmlspecialchars($properti['gambar_url'] ?? '') ?>">

                    <div class="row g-3">
                        <div class="col-12 field-panel"><label class="form-label">Judul</label><input class="form-control" type="text" name="judul" value="<?= htmlspecialchars($properti['judul']) ?>" required></div>
                        <div class="col-12 field-panel"><label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="4" required><?= htmlspecialchars($properti['deskripsi']) ?></textarea></div>
                        
                        <!-- Transaksi & Harga -->
                        <div class="col-md-3 field-panel">
                            <label class="form-label">Tipe Transaksi</label>
                            <select class="form-select" name="tipe_transaksi" id="tipe_transaksi" required>
                                <option value="jual" <?= is_selected($properti['tipe_transaksi'] ?? 'jual', 'jual') ?>>Dijual</option>
                                <option value="sewa" <?= is_selected($properti['tipe_transaksi'] ?? 'jual', 'sewa') ?>>Disewakan</option>
                            </select>
                        </div>
                        <div class="col-md-3 field-panel" id="durasi_minimal_container" style="<?= ($properti['tipe_transaksi'] ?? 'jual') === 'sewa' ? '' : 'display:none;' ?>">
                            <label class="form-label">Durasi Minimal Sewa</label>
                            <select class="form-select" name="durasi_minimal" id="durasi_minimal">
                                <option value="">Pilih durasi</option>
                                <option value="6 bulan" <?= is_selected($properti['durasi_minimal'] ?? '', '6 bulan') ?>>6 bulan</option>
                                <option value="1 tahun" <?= is_selected($properti['durasi_minimal'] ?? '', '1 tahun') ?>>1 tahun</option>
                            </select>
                        </div>
                        <div class="col-md-3 field-panel">
                            <label class="form-label">Harga Jual (Rp)</label>
                            <input class="form-control" type="number" name="harga" id="harga_jual" value="<?= (string)($properti['harga'] ?? '') ?>" min="0" placeholder="0" required>
                        </div>

                        <div id="rental_fields" class="col-12 row g-3" style="<?= ($properti['tipe_transaksi'] ?? 'jual') === 'sewa' ? '' : 'display:none;' ?>; margin:0;">
                            <div class="col-md-4 field-panel">
                                <label class="form-label">Harga Sewa (Rp)</label>
                                <input class="form-control" type="number" name="harga_sewa" id="harga_sewa" value="<?= (string)($properti['harga_sewa'] ?? '') ?>" min="0" placeholder="0">
                            </div>
                            <div class="col-md-4 field-panel">
                                <label class="form-label">Periode Sewa</label>
                                <select class="form-select" name="periode_sewa">
                                    <option value="">Pilih periode</option>
                                    <option value="bulan" <?= is_selected($properti['periode_sewa'] ?? '', 'bulan') ?>>Per Bulan</option>
                                    <option value="tahun" <?= is_selected($properti['periode_sewa'] ?? '', 'tahun') ?>>Per Tahun</option>
                                </select>
                            </div>
                            <div class="col-md-4 field-panel"><label class="form-label">Minimal Sewa</label><input class="form-control" type="number" name="minimal_sewa" value="<?= (string)($properti['minimal_sewa'] ?? '') ?>" min="1" placeholder="1"></div>
                        </div>
                        <div class="col-md-4 field-panel"><label class="form-label">Tipe Properti</label><select class="form-select" name="tipe" required>
                            <?php foreach (['rumah', 'apartemen', 'tanah', 'ruko'] as $t): ?>
                                <option value="<?= $t ?>" <?= is_selected($properti['tipe'], $t) ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Status Properti</label><select class="form-select" name="status" required>
                            <option value="tersedia" <?= is_selected($properti['status'], 'tersedia') ?>>Tersedia</option>
                            <option value="terjual" <?= is_selected($properti['status'], 'terjual') ?>>Terjual / Tersewa</option>
                        </select></div>

                        <div class="col-md-8 field-panel"><label class="form-label">Alamat</label><input class="form-control" type="text" name="alamat" value="<?= htmlspecialchars($properti['alamat']) ?>" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Kota</label><input class="form-control" type="text" name="kota" value="<?= htmlspecialchars($properti['kota']) ?>" required></div>
                        <div class="col-md-6 field-panel"><label class="form-label">Latitude</label><input class="form-control" type="text" name="lat" value="<?= htmlspecialchars($properti['lat'] ?? '') ?>"></div>
                        <div class="col-md-6 field-panel"><label class="form-label">Longitude</label><input class="form-control" type="text" name="lng" value="<?= htmlspecialchars($properti['lng'] ?? '') ?>"></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Luas Tanah (m²)</label><input class="form-control" type="number" name="luas_tanah" value="<?= $properti['luas_tanah'] ?>" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Luas Bangunan (m²)</label><input class="form-control" type="number" name="luas_bangunan" value="<?= $properti['luas_bangunan'] ?>" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Kamar Tidur</label><input class="form-control" type="number" name="kamar_tidur" value="<?= $properti['kamar_tidur'] ?? 0 ?>" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Kamar Mandi</label><input class="form-control" type="number" name="kamar_mandi" value="<?= $properti['kamar_mandi'] ?? 0 ?>" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Carport</label><input class="form-control" type="number" name="carport" value="<?= (string)($properti['carport'] ?? '0') ?>" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Status Hunian</label><select class="form-select" name="status_hunian" required><option value="">Pilih status hunian</option><option value="kosong" <?= is_selected($properti['status_hunian'] ?? 'kosong', 'kosong') ?>>Kosong</option><option value="terisi" <?= is_selected($properti['status_hunian'] ?? 'kosong', 'terisi') ?>>Terisi</option></select></div>
                        <div class="col-12 field-panel"><label class="form-label">Fasilitas</label><input class="form-control" type="text" name="fasilitas" value="<?= htmlspecialchars($properti['fasilitas'] ?? '') ?>" placeholder="Contoh: Taman, Garasi, Kolam Renang" required><small class="text-muted">Pisahkan beberapa fasilitas dengan koma.</small></div>
                        <div class="col-md-6 field-panel"><label class="form-label">Upload Gambar Baru</label><input class="form-control" type="file" name="gambar_file" accept="image/jpeg,image/png,image/webp"><small class="text-muted">JPG, PNG, atau WEBP. Maksimal 2 MB.</small></div>
                        <div class="col-md-6 field-panel"><label class="form-label">URL Gambar Utama</label><input class="form-control" type="url" name="gambar_url" value="<?= htmlspecialchars($properti['gambar_url'] ?? '') ?>"><small class="text-muted">Kosongkan jika tetap memakai gambar saat ini.</small></div>
                        <div class="col-12 field-panel"><label class="form-label">Agen</label><select class="form-select" name="agen_id">
                            <option value="">- Tanpa Agen -</option>
                            <?php foreach ($daftar_agen as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= is_selected($properti['agen_id'], $a['id']) ?>><?= htmlspecialchars($a['nama']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    </div>
                    <div class="form-actions mt-4"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-check2-circle me-1"></i> Update Properti</button></div>
                </form>
            </div>
        </div>
    </main>
<script src="<?= BASE_URL ?>assets/js/properti-form.js?v=<?= filemtime(__DIR__ . '/assets/js/properti-form.js') ?>"></script>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>