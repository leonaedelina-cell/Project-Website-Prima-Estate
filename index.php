<?php
/**
 * index.php - Estate Prima
 * Homepage: hero + search cepat (termasuk filter transaksi) + statistik + properti terbaru
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// ------------------------------------------------------------------
// 1. Statistik ringkas
// ------------------------------------------------------------------
$total_properti = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM properti")
)['total'];

$total_terjual = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM properti WHERE status = 'terjual'")
)['total'];
$total_tersedia = $total_properti - $total_terjual;

$total_user = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role = 'user'")
)['total'];

// ------------------------------------------------------------------
// 2. Properti terbaru (6 item)
// ------------------------------------------------------------------
$query_terbaru = "SELECT id, judul, harga, harga_sewa, periode_sewa, tipe_transaksi, tipe, kota, kamar_tidur, kamar_mandi, luas_bangunan, gambar_url
                   FROM properti
                   WHERE status = 'tersedia'
                   ORDER BY created_at DESC
                   LIMIT 6";
$hasil_terbaru = mysqli_query($koneksi, $query_terbaru);
$properti_terbaru = mysqli_fetch_all($hasil_terbaru, MYSQLI_ASSOC);

// Daftar kota unik
$daftar_kota = mysqli_fetch_all(
    mysqli_query($koneksi, "SELECT DISTINCT kota FROM properti ORDER BY kota"),
    MYSQLI_ASSOC
);

$user = user_login();

$page_title = 'Estate Prima — Temukan Hunian Impian Anda';
require_once __DIR__ . '/includes/header.php';
?>
    <!-- ============ HERO ============ -->
    <header class="hero">
        <div class="container pb-5">
            <div class="row">
                <div class="col-lg-7">
                    <p class="hero-eyebrow mb-3">Properti Pilihan &middot; Terverifikasi</p>
                    <h1 class="mb-3">Temukan hunian yang <em>terasa seperti pulang.</em></h1>
                    <p class="lead mb-4">
                        Estate Prima menghadirkan pilihan rumah, apartemen, dan ruko untuk dijual maupun disewakan dengan proses transparan.
                    </p>
                </div>
            </div>

            <div class="row justify-content-start">
                <div class="col-lg-11">
                    <div class="floating-card field-panel mt-4">
                        <span class="corner-tick tl"></span>
                        <span class="corner-tick tr"></span>
                        <span class="corner-tick bl"></span>
                        <span class="corner-tick br"></span>

                        <form action="listing.php" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="d-block">Pilihan Transaksi</label>
                                <select name="tipe_transaksi" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="jual">Beli</option>
                                    <option value="sewa">Sewa</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="d-block">Kata Kunci</label>
                                <input type="text" name="q" class="form-control" placeholder="Nama, alamat, kota...">
                            </div>
                            <div class="col-md-2">
                                <label class="d-block">Kota</label>
                                <select name="kota" class="form-select">
                                    <option value="">Semua Kota</option>
                                    <?php foreach ($daftar_kota as $k): ?>
                                        <option value="<?= htmlspecialchars($k['kota']) ?>"><?= htmlspecialchars($k['kota']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="d-block">Tipe Properti</label>
                                <select name="tipe" class="form-select">
                                    <option value="">Semua Tipe</option>
                                    <option value="rumah">Rumah</option>
                                    <option value="apartemen">Apartemen</option>
                                    <option value="tanah">Tanah</option>
                                    <option value="ruko">Ruko</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-gold w-100 py-2">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ============ STATISTIK ============ -->
    <section class="stats-band">
        <div class="container">
            <div class="row text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-num"><?= $total_properti ?></div>
                    <div class="stat-label">Total Properti</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-num"><?= $total_tersedia ?></div>
                    <div class="stat-label">Siap Dihuni / Disewa</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-num"><?= $total_terjual ?></div>
                    <div class="stat-label">Telah Terjual / Tersewa</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-num"><?= $total_user ?></div>
                    <div class="stat-label">Pengguna Terdaftar</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ PROPERTI TERBARU ============ -->
    <section class="py-5 my-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
                <div>
                    <p class="section-eyebrow mb-2">Baru Ditambahkan</p>
                    <h2 class="section-title mb-0">Properti Pilihan Terbaru</h2>
                </div>
                <a href="listing.php" class="btn btn-outline-navy">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <?php if (empty($properti_terbaru)): ?>
                <p class="text-muted">Belum ada properti yang tersedia saat ini.</p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($properti_terbaru as $p): ?>
                        <?php 
                            $tx_type = $p['tipe_transaksi'] ?? 'jual';
                            $badge_class = 'badge-tx-' . $tx_type;
                            $tx_label = $tx_type === 'sewa' ? 'Disewakan' : 'Dijual';
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="property-card position-relative">
                                <span class="corner-tick tl"></span>
                                <span class="corner-tick br"></span>

                                <div class="thumb" style="background-image:url('<?= htmlspecialchars($p['gambar_url'] ?: 'https://images.unsplash.com/photo-1568605114967-8130f3a36994') ?>');">
                                    <span class="badge-tx-tag <?= $badge_class ?>"><?= ucfirst($p['tipe']) ?> - <?= $tx_label ?></span>
                                    <span class="price-tag">
                                        <?php if ($tx_type === 'sewa'): ?>
                                            Rp <?= number_format($p['harga_sewa'], 0, ',', '.') ?> / <?= $p['periode_sewa'] ?>
                                        <?php else: ?>
                                            Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    <h3 class="mb-1"><?= htmlspecialchars($p['judul']) ?></h3>
                                    <p class="location mb-2"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($p['kota']) ?></p>
                                    <div class="specs d-flex gap-3">
                                        <span><i class="bi bi-door-closed-fill"></i><?= $p['kamar_tidur'] ?> KT</span>
                                        <span><i class="bi bi-droplet-fill"></i><?= $p['kamar_mandi'] ?> KM</span>
                                        <span><i class="bi bi-rulers"></i><?= $p['luas_bangunan'] ?> m&sup2;</span>
                                    </div>
                                    <a href="detail.php?id=<?= $p['id'] ?>" class="stretched-link"></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============ CTA ============ -->
    <section class="container pb-5 mb-4">
        <div class="cta-banner text-center">
            <h2 class="mb-2">Butuh bantuan menemukan hunian yang tepat?</h2>
            <p class="text-white-50 mb-4">Tim sales kami siap membantu, dari konsultasi hingga proses transaksi jual dan sewa.</p>
            <a href="kontak.php" class="btn btn-gold px-4 py-2">Hubungi Tim Sales</a>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>