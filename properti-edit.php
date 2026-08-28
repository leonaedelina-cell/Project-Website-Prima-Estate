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
$admin_sidebar = true;
$dashboard_sidebar_active = 'properti';
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

        <p class="section-eyebrow mb-2">Info Dasar</p>
        <div class="row g-3 mb-4">
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
            <div class="col-md-6 field-panel"><label class="form-label">Tipe Transaksi</label><select class="form-select" name="tipe_transaksi" id="tipe_transaksi" required>
                <option value="jual" <?= is_selected($properti['tipe_transaksi'], 'jual') ?>>Jual</option>
                <option value="sewa" <?= is_selected($properti['tipe_transaksi'], 'sewa') ?>>Sewa</option>
            </select></div>
            <div class="col-md-6 field-panel" id="wrap-durasi-minimal"><label class="form-label">Durasi Minimal Sewa</label><select class="form-select" name="durasi_minimal">
                <option value="6 bulan" <?= is_selected($properti['durasi_minimal'], '6 bulan') ?>>6 Bulan</option>
                <option value="1 tahun" <?= is_selected($properti['durasi_minimal'], '1 tahun') ?>>1 Tahun</option>
            </select></div>
            <div class="col-md-8 field-panel"><label class="form-label">Alamat</label><input class="form-control" type="text" name="alamat" value="<?= htmlspecialchars($properti['alamat']) ?>" required></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kota</label><input class="form-control" type="text" name="kota" value="<?= htmlspecialchars($properti['kota']) ?>" required></div>
        </div>

        <p class="section-eyebrow mb-2">Detail Fisik</p>
        <div class="row g-3 mb-4">
            <div class="col-md-6 field-panel"><label class="form-label">Latitude</label><input class="form-control" type="text" name="lat" value="<?= htmlspecialchars($properti['lat'] ?? '') ?>"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Longitude</label><input class="form-control" type="text" name="lng" value="<?= htmlspecialchars($properti['lng'] ?? '') ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Luas Tanah (m2)</label><input class="form-control" type="number" name="luas_tanah" value="<?= $properti['luas_tanah'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Luas Bangunan (m2)</label><input class="form-control" type="number" name="luas_bangunan" value="<?= $properti['luas_bangunan'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kamar Tidur</label><input class="form-control" type="number" name="kamar_tidur" value="<?= $properti['kamar_tidur'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Kamar Mandi</label><input class="form-control" type="number" name="kamar_mandi" value="<?= $properti['kamar_mandi'] ?>"></div>
            <div class="col-md-4 field-panel"><label class="form-label">Carport</label><input class="form-control" type="number" name="carport" value="<?= $properti['carport'] ?>"></div>
        </div>

        <p class="section-eyebrow mb-2">Fasilitas &amp; Status</p>
        <div class="row g-3 mb-4">
            <div class="col-12 field-panel"><label class="form-label">Fasilitas (pisahkan pakai koma)</label><input class="form-control" type="text" name="fasilitas" value="<?= htmlspecialchars($properti['fasilitas'] ?? '') ?>" placeholder="AC, Garasi, Kolam Renang"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Status Hunian</label><select class="form-select" name="status_hunian">
                <option value="kosong" <?= is_selected($properti['status_hunian'], 'kosong') ?>>Kosong</option>
                <option value="terisi" <?= is_selected($properti['status_hunian'], 'terisi') ?>>Terisi</option>
            </select></div>
            <div class="col-md-6 field-panel"><label class="form-label">Agen</label><select class="form-select" name="agen_id">
                <option value="">- Tanpa Agen -</option>
                <?php foreach ($daftar_agen as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= is_selected($properti['agen_id'], $a['id']) ?>>
                        <?= htmlspecialchars($a['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select></div>
        </div>

        <p class="section-eyebrow mb-2">Gambar Utama</p>
        <div class="row g-3">
            <?php if (!empty($properti['gambar_url'])): ?>
                <div class="col-12"><img src="<?= htmlspecialchars($properti['gambar_url']) ?>" alt="Gambar saat ini" style="max-height:140px;border-radius:6px;" class="mb-2"></div>
            <?php endif; ?>
            <div class="col-md-6 field-panel"><label class="form-label">Ganti Gambar (JPG/PNG/WEBP, maks 2MB)</label><input class="form-control" type="file" name="gambar" accept="image/jpeg,image/png,image/webp"></div>
            <div class="col-md-6 field-panel"><label class="form-label">Atau URL Gambar</label><input class="form-control" type="text" name="gambar_url" value="<?= htmlspecialchars($properti['gambar_url'] ?? '') ?>"></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-check2-circle me-1"></i> Update Properti</button></div>
                </form>
                <script>
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
