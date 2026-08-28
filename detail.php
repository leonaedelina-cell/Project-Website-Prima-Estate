<?php
/**
 * detail.php - Estate Prima
 * Detail 1 properti: galeri, fasilitas, kartu agen, tombol Wishlist & Ajukan Beli
 * Akses: detail.php?id=1
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Properti tidak ditemukan.');
}

// ------------------------------------------------------------------
// 1. Ambil data properti + join data agen
// ------------------------------------------------------------------
$query = "SELECT p.*, a.nama AS nama_agen, a.no_hp AS no_hp_agen, a.email AS email_agen, a.foto_url AS foto_agen
          FROM properti p
          LEFT JOIN agen a ON p.agen_id = a.id
          WHERE p.id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$properti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$properti) {
    die('Properti tidak ditemukan.');
}

// ------------------------------------------------------------------
// 2. Ambil galeri foto tambahan
// ------------------------------------------------------------------
$stmt = mysqli_prepare($koneksi, "SELECT gambar_url FROM galeri_properti WHERE properti_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$galeri = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// ------------------------------------------------------------------
// 3. Cek status: user ini sudah login? sudah wishlist properti ini?
// ------------------------------------------------------------------
$user = user_login();
$sudah_wishlist = false;

if ($user) {
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM wishlist WHERE user_id = ? AND properti_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user['id'], $id);
    mysqli_stmt_execute($stmt);
    $sudah_wishlist = (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

// Pesan sukses/gagal dari redirect proses-wishlist.php / proses-transaksi.php
$pesan = $_GET['pesan'] ?? '';

// Siapkan foto-foto buat grid galeri: gambar utama + max 3 thumbnail
$gambar_utama = $properti['gambar_url'] ?: 'https://images.unsplash.com/photo-1568605114967-8130f3a36994';
$thumbnail    = array_slice($galeri, 0, 3);
$sisa_foto    = max(count($galeri) - 3, 0);

$page_title = htmlspecialchars($properti['judul']) . ' — Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>

    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header">
        <div class="container">
            <div class="breadcrumb-estate mb-2">
                <a href="index.php">Beranda</a>
                <span class="sep">/</span>
                <a href="listing.php">Properti</a>
                <span class="sep">/</span>
                <span class="current"><?= htmlspecialchars($properti['judul']) ?></span>
            </div>
            <h1 class="mb-0" style="font-size:1.7rem;"><?= htmlspecialchars($properti['judul']) ?></h1>
            <p class="text-white-50 mb-0 mt-1"><i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($properti['alamat']) ?>, <?= htmlspecialchars($properti['kota']) ?></p>
        </div>
    </div>

    <section class="py-5">
        <div class="container">

            <?php if ($pesan === 'wishlist-ditambah'): ?>
                <div class="alert-estate-success p-3 mb-4"><i class="bi bi-heart-fill me-1"></i> Berhasil ditambahkan ke wishlist.</div>
            <?php elseif ($pesan === 'wishlist-dihapus'): ?>
                <div class="alert-estate-success p-3 mb-4"><i class="bi bi-check-circle-fill me-1"></i> Berhasil dihapus dari wishlist.</div>
            <?php elseif ($pesan === 'pengajuan-berhasil'): ?>
                <div class="alert-estate-success p-3 mb-4"><i class="bi bi-check-circle-fill me-1"></i> Pengajuan beli berhasil dikirim! Cek status di halaman Pesanan.</div>
            <?php elseif ($pesan === 'sudah-diajukan'): ?>
                <div class="alert-estate-error p-3 mb-4"><i class="bi bi-exclamation-circle-fill me-1"></i> Kamu sudah pernah mengajukan pembelian untuk properti ini.</div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- ============ KOLOM KIRI: GALERI + INFO ============ -->
                <div class="col-lg-8">

                    <!-- Galeri -->
                    <div class="gallery-grid mb-4">
                        <div class="gallery-main" style="background-image:url('<?= htmlspecialchars($gambar_utama) ?>');"></div>
                        <?php if (empty($thumbnail)): ?>
                            <div class="gallery-thumb" style="background-image:url('<?= htmlspecialchars($gambar_utama) ?>');"></div>
                            <div class="gallery-thumb" style="background-image:url('<?= htmlspecialchars($gambar_utama) ?>'); filter:grayscale(30%);"></div>
                            <div class="gallery-thumb" style="background-image:url('<?= htmlspecialchars($gambar_utama) ?>'); filter:grayscale(60%);"></div>
                            <div class="gallery-thumb" style="background-image:url('<?= htmlspecialchars($gambar_utama) ?>'); filter:grayscale(90%);"></div>
                        <?php else: ?>
                            <?php foreach ($thumbnail as $i => $g): ?>
                                <div class="gallery-thumb <?= ($i === 2 && $sisa_foto > 0) ? 'more' : '' ?>"
                                     data-more="+<?= $sisa_foto ?> foto"
                                     style="background-image:url('<?= htmlspecialchars($g['gambar_url']) ?>');"></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Status & Tipe -->
                    <div class="d-flex gap-2 mb-4">
                        <span class="badge-status <?= $properti['status'] ?>">
                            <i class="bi bi-<?= $properti['status'] === 'tersedia' ? 'check-circle-fill' : 'x-circle-fill' ?>"></i>
                            <?= ucfirst($properti['status']) ?>
                        </span>
                        <span class="badge-status" style="background:#f1eee5; color:var(--navy-900);">
                            <i class="bi bi-tag-fill"></i> <?= ucfirst($properti['tipe']) ?>
                        </span>
                        <span class="badge-status" style="background:#eaf1f8; color:var(--navy-900);">
                            <i class="bi bi-cash-coin"></i> <?= $properti['tipe_transaksi'] === 'sewa' ? 'Disewakan' : 'Dijual' ?><?= $properti['tipe_transaksi'] === 'sewa' && $properti['durasi_minimal'] ? ' (min. ' . htmlspecialchars($properti['durasi_minimal']) . ')' : '' ?>
                        </span>
                    </div>

                    <!-- Fasilitas -->
                    <p class="section-eyebrow mb-2">Spesifikasi</p>
                    <h2 class="section-title mb-3" style="font-size:1.5rem;">Fasilitas &amp; Detail</h2>
                    <div class="facility-list mb-4">
                        <div class="facility-item">
                            <i class="bi bi-door-closed-fill"></i>
                            <div><span class="val"><?= $properti['kamar_tidur'] ?></span><span class="lbl">Kamar Tidur</span></div>
                        </div>
                        <div class="facility-item">
                            <i class="bi bi-droplet-fill"></i>
                            <div><span class="val"><?= $properti['kamar_mandi'] ?></span><span class="lbl">Kamar Mandi</span></div>
                        </div>
                        <div class="facility-item">
                            <i class="bi bi-car-front-fill"></i>
                            <div><span class="val"><?= $properti['carport'] ?></span><span class="lbl">Carport</span></div>
                        </div>
                        <div class="facility-item">
                            <i class="bi bi-rulers"></i>
                            <div><span class="val"><?= $properti['luas_tanah'] ?? '-' ?> m&sup2;</span><span class="lbl">Luas Tanah</span></div>
                        </div>
                        <div class="facility-item">
                            <i class="bi bi-building"></i>
                            <div><span class="val"><?= $properti['luas_bangunan'] ?? '-' ?> m&sup2;</span><span class="lbl">Luas Bangunan</span></div>
                        </div>
                    </div>

                    <?php $daftar_fasilitas = array_filter(array_map('trim', explode(',', (string) ($properti['fasilitas'] ?? '')))); ?>
                    <?php if (!empty($daftar_fasilitas)): ?>
                        <p class="section-eyebrow mb-2">Fasilitas Tambahan</p>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php foreach ($daftar_fasilitas as $f): ?>
                                <span class="badge-status" style="background:#f1eee5;color:var(--navy-900);"><i class="bi bi-check2"></i> <?= htmlspecialchars($f) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Deskripsi -->
                    <p class="section-eyebrow mb-2">Tentang Properti</p>
                    <h2 class="section-title mb-3" style="font-size:1.5rem;">Deskripsi</h2>
                    <p class="text-muted mb-4" style="line-height:1.8;"><?= nl2br(htmlspecialchars($properti['deskripsi'])) ?></p>

                    <!-- Agen -->
                    <?php if ($properti['nama_agen']): ?>
                        <p class="section-eyebrow mb-2">Narahubung</p>
                        <h2 class="section-title mb-3" style="font-size:1.5rem;">Agen Sales</h2>
                        <div class="agent-card mb-4">
                            <div class="avatar" style="<?= $properti['foto_agen'] ? "background-image:url('".htmlspecialchars($properti['foto_agen'])."');" : '' ?>">
                                <?= $properti['foto_agen'] ? '' : strtoupper(substr($properti['nama_agen'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="role">Agen Sales</div>
                                <div class="name"><?= htmlspecialchars($properti['nama_agen']) ?></div>
                                <div class="contact">
                                    <i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($properti['no_hp_agen'] ?? '-') ?>
                                    <?php if ($properti['email_agen']): ?>
                                        &nbsp;&middot;&nbsp;<i class="bi bi-envelope-fill me-1"></i><?= htmlspecialchars($properti['email_agen']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Lokasi -->
                    <p class="section-eyebrow mb-2">Peta</p>
                    <h2 class="section-title mb-3" style="font-size:1.5rem;">Lokasi</h2>
                    <div class="rounded overflow-hidden" style="border:1px solid #ece9e1;">
                        <iframe
                            src="https://www.google.com/maps?q=<?= urlencode($properti['alamat'] . ', ' . $properti['kota']) ?>&output=embed"
                            style="border:0; width:100%; height:320px;"
                            allowfullscreen loading="lazy">
                        </iframe>
                    </div>
                </div>

                <!-- ============ KOLOM KANAN: ACTION PANEL ============ -->
                <div class="col-lg-4">
                    <div class="action-panel">
                        <p class="text-muted mb-1 small">Harga</p>
                        <div class="price mb-3">Rp <?= number_format($properti['harga'], 0, ',', '.') ?></div>

                        <?php if (!$user): ?>
                            <p class="text-muted small mb-3">Silakan masuk dulu untuk menambah wishlist atau mengajukan pembelian.</p>
                            <a href="login.php" class="btn btn-gold w-100 py-2 mb-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk untuk Lanjut
                            </a>
                        <?php else: ?>
                            <!-- Tombol Wishlist -->
                            <form method="POST" action="proses-wishlist.php" class="mb-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                <input type="hidden" name="properti_id" value="<?= $properti['id'] ?>">
                                <button type="submit" class="btn btn-wishlist w-100 py-2 <?= $sudah_wishlist ? 'active' : '' ?>">
                                    <i class="bi bi-heart<?= $sudah_wishlist ? '-fill' : '' ?> me-1"></i>
                                    <?= $sudah_wishlist ? 'Tersimpan di Wishlist' : 'Tambah ke Wishlist' ?>
                                </button>
                            </form>

                            <!-- Form Ajukan Beli -->
                            <?php if ($properti['status'] === 'tersedia'): ?>
                                <form method="POST" action="proses-transaksi.php" class="field-panel mt-3 pt-3" style="border-top:1px dashed #e3e1da;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="properti_id" value="<?= $properti['id'] ?>">
                                    <label class="d-block">Metode Pembayaran</label>
                                    <select name="metode_bayar" class="form-select mb-3" required>
                                        <option value="">Pilih Metode Bayar</option>
                                        <option value="transfer_bank">Transfer Bank</option>
                                        <option value="cicilan_kpr">Cicilan KPR</option>
                                        <option value="tunai">Tunai</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline-navy w-100 py-2">
                                        <i class="bi bi-send-check-fill me-1"></i> <?= $properti['tipe_transaksi'] === 'sewa' ? 'Ajukan Sewa' : 'Ajukan Beli' ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert-estate-error p-2 mt-3 text-center small mb-0">
                                    <i class="bi bi-x-circle-fill me-1"></i> Properti ini sudah terjual
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>