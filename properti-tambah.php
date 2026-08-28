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
$admin_sidebar = true;
$dashboard_sidebar_active = 'properti';
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

        <p class="section-eyebrow mb-2">Info Dasar</p>
        <div class="row g-3 mb-4">
            <div class="col-12 field-panel"><label class="form-label">Judul</label><input class="form-control" type="text" name="judul" required></div>
            <div class="col-12 field-panel"><label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="4"></textarea></div>
            <div class="col-md-6 field-panel"><label class="form-label">Harga (Rp)</label><input class="form-control" type="number" name="harga" required min="0"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Tipe</label><select class="form-select" name="tipe" required>
                <option value="rumah">Rumah</option>
                <option value="apartemen">Apartemen</option>
                <option value="tanah">Tanah</option>
                <option value="ruko">Ruko</option>
            </select></div>
            <div class="col-md-6 field-panel"><label class="form-label">Tipe Transaksi</label><select class="form-select" name="tipe_transaksi" id="tipe_transaksi" required>
                <option value="jual">Jual</option>
                <option value="sewa">Sewa</option>
            </select></div>
            <div class="col-md-6 field-panel" id="wrap-durasi-minimal" style="display:none;"><label class="form-label">Durasi Minimal Sewa</label><select class="form-select" name="durasi_minimal">
                <option value="6 bulan">6 Bulan</option>
                <option value="1 tahun">1 Tahun</option>
            </select></div>
            <div class="col-md-8 field-panel"><label class="form-label">Alamat</label><input class="form-control" type="text" name="alamat" required></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kota</label><input class="form-control" type="text" name="kota" required></div>
        </div>

        <p class="section-eyebrow mb-2">Detail Fisik</p>
        <div class="row g-3 mb-4">
            <div class="col-md-6 field-panel"><label class="form-label">Latitude (opsional, buat Maps)</label><input class="form-control" type="text" name="lat"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Longitude (opsional, buat Maps)</label><input class="form-control" type="text" name="lng"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Luas Tanah (m2)</label><input class="form-control" type="number" name="luas_tanah"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Luas Bangunan (m2)</label><input class="form-control" type="number" name="luas_bangunan"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kamar Tidur</label><input class="form-control" type="number" name="kamar_tidur" value="0"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kamar Mandi</label><input class="form-control" type="number" name="kamar_mandi" value="0"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Carport</label><input class="form-control" type="number" name="carport" value="0"></div>
        </div>

        <p class="section-eyebrow mb-2">Fasilitas &amp; Status</p>
        <div class="row g-3 mb-4">
            <div class="col-12 field-panel"><label class="form-label">Fasilitas (pisahkan pakai koma)</label><input class="form-control" type="text" name="fasilitas" placeholder="AC, Garasi, Kolam Renang"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Status Hunian</label><select class="form-select" name="status_hunian">
                <option value="kosong">Kosong</option>
                <option value="terisi">Terisi</option>
            </select></div>
            <div class="col-md-6 field-panel"><label class="form-label">Agen</label><select class="form-select" name="agen_id">
                <option value="">- Tanpa Agen -</option>
                <?php foreach ($daftar_agen as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama']) ?></option>
                <?php endforeach; ?>
            </select></div>
        </div>

        <p class="section-eyebrow mb-2">Gambar Utama</p>
        <div class="row g-3">
            <div class="col-md-6 field-panel"><label class="form-label">Upload Gambar (JPG/PNG/WEBP, maks 2MB)</label><input class="form-control" type="file" name="gambar" accept="image/jpeg,image/png,image/webp"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Atau URL Gambar</label><input class="form-control" type="text" name="gambar_url" placeholder="https://..."></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-house-add-fill me-1"></i> Simpan Properti</button></div>
                </form>
                <script>
                    // Field "Durasi Minimal" cuma relevan kalau tipe transaksi = sewa
                    (function () {
                        var tipeTransaksi = document.getElementById('tipe_transaksi');
                        var wrapDurasi = document.getElementById('wrap-durasi-minimal');
                        function toggleDurasi() {
                            wrapDurasi.style.display = tipeTransaksi.value === 'sewa' ? '' : 'none';
                        }
                        tipeTransaksi.addEventListener('change', toggleDurasi);
                        toggleDurasi();
                    })();
                </script>
            </div>
        </div>
    </main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
