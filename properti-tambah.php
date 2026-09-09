<?php
/**
 * pages/admin/properti-tambah.php - Estate Prima
 * Form tambah properti baru.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$daftar_agen = mysqli_fetch_all(mysqli_query($koneksi, "SELECT id, nama FROM agen ORDER BY nama"), MYSQLI_ASSOC);

$user = user_login();
$page_title = 'Tambah Properti - Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'properti';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Tambah Properti</h1>
            <p class="lead mb-0">Tambahkan properti baru lengkap dengan informasi transaksi, fasilitas, dan foto.</p>
        </div>
    </div>

    <main class="py-5">
        <div class="container">
            <div class="admin-form-card">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div><p class="section-eyebrow mb-2">Data Properti</p><h2 class="section-title mb-0">Tambah Properti Baru</h2></div>
                    <a href="admin-properti.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
                <form method="POST" action="proses-properti.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="aksi" value="tambah">

                    <div class="row g-3">
                        <div class="col-12 field-panel"><label class="form-label">Judul</label><input class="form-control" type="text" name="judul" required></div>
                        <div class="col-12 field-panel"><label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="4" required></textarea></div>
                        
                        <!-- Pemasaran & Transaksi -->
                        <div class="col-md-4 field-panel">
                            <label class="form-label">Tipe Transaksi</label>
                            <select class="form-select" name="tipe_transaksi" id="tipe_transaksi" required>
                                <option value="jual">Dijual</option>
                                <option value="sewa">Disewakan</option>
                            </select>
                        </div>
                        <div class="col-md-4 field-panel" id="durasi_minimal_container" style="display:none;">
                            <label class="form-label">Durasi Minimal Sewa</label>
                            <select class="form-select" name="durasi_minimal" id="durasi_minimal">
                                <option value="">Pilih durasi</option>
                                <option value="6 bulan">6 bulan</option>
                                <option value="1 tahun">1 tahun</option>
                            </select>
                        </div>
                        <div class="col-md-4 field-panel">
                            <label class="form-label">Harga Jual (Rp)</label>
                            <input class="form-control" type="number" name="harga" id="harga_jual" value="" min="0" placeholder="0" required>
                        </div>

                        <div id="rental_fields" class="col-12 row g-3" style="display:none; margin:0;">
                            <div class="col-md-4 field-panel">
                                <label class="form-label">Harga Sewa (Rp)</label>
                                <input class="form-control" type="number" name="harga_sewa" id="harga_sewa" value="" min="0" placeholder="0">
                            </div>
                            <div class="col-md-4 field-panel">
                                <label class="form-label">Periode Sewa</label>
                                <select class="form-select" name="periode_sewa">
                                    <option value="">Pilih periode</option>
                                    <option value="bulan">Per Bulan</option>
                                    <option value="tahun">Per Tahun</option>
                                </select>
                            </div>
                            <div class="col-md-4 field-panel">
                                <label class="form-label">Minimal Sewa (Durasi)</label>
                                <input class="form-control" type="number" name="minimal_sewa" value="" min="1" placeholder="1">
                            </div>
                        </div>

                        <div class="col-md-6 field-panel"><label class="form-label">Tipe Properti</label><select class="form-select" name="tipe" required>
                            <option value="">Pilih tipe properti</option>
                            <option value="rumah">Rumah</option>
                            <option value="apartemen">Apartemen</option>
                            <option value="tanah">Tanah</option>
                            <option value="ruko">Ruko</option>
                        </select></div>
                        <div class="col-md-6 field-panel"><label class="form-label">Kota</label><input class="form-control" type="text" name="kota" required></div>
                        <div class="col-12 field-panel"><label class="form-label">Alamat</label><input class="form-control" type="text" name="alamat" required></div>
                        <div class="col-md-6 field-panel"><label class="form-label">Latitude</label><input class="form-control" type="text" name="lat"></div>
                        <div class="col-md-6 field-panel"><label class="form-label">Longitude</label><input class="form-control" type="text" name="lng"></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Luas Tanah (m²)</label><input class="form-control" type="number" name="luas_tanah" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Luas Bangunan (m²)</label><input class="form-control" type="number" name="luas_bangunan" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Kamar Tidur</label><input class="form-control" type="number" name="kamar_tidur" value="0" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Kamar Mandi</label><input class="form-control" type="number" name="kamar_mandi" value="0" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Carport</label><input class="form-control" type="number" name="carport" value="0" min="0" required></div>
                        <div class="col-md-4 field-panel"><label class="form-label">Status Hunian</label><select class="form-select" name="status_hunian" required><option value="">Pilih status hunian</option><option value="kosong">Kosong</option><option value="terisi">Terisi</option></select></div>
                        <div class="col-12 field-panel"><label class="form-label">Fasilitas</label><input class="form-control" type="text" name="fasilitas" value="" placeholder="Contoh: Taman, Garasi, Kolam Renang" required><small class="text-muted">Pisahkan beberapa fasilitas dengan koma.</small></div>
                        <div class="col-md-6 field-panel"><label class="form-label">Upload Gambar Utama</label><input class="form-control" type="file" name="gambar_file" accept="image/jpeg,image/png,image/webp" required><small class="text-muted">JPG, PNG, atau WEBP. Maksimal 2 MB.</small></div>
                        <div class="col-md-6 field-panel"><label class="form-label">Atau URL Gambar Utama</label><input class="form-control" type="url" name="gambar_url" placeholder="https://..."></div>
                        <div class="col-12 field-panel"><label class="form-label">Agen</label><select class="form-select" name="agen_id">
                            <option value="">- Tanpa Agen -</option>
                            <?php foreach ($daftar_agen as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    </div>
                    <div class="form-actions mt-4"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-house-add-fill me-1"></i> Simpan Properti</button></div>
                </form>
            </div>
        </div>
    </main>
<script src="<?= BASE_URL ?>assets/js/properti-form.js?v=<?= filemtime(__DIR__ . '/assets/js/properti-form.js') ?>"></script>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>