<?php
/**
 * listing.php - Estate Prima
 * Semua properti - search + filter (tipe_transaksi, tipe, kota, rentang harga) + pagination
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// ------------------------------------------------------------------
// 1. Ambil & bersihkan input filter dari query string
// ------------------------------------------------------------------
$q              = trim($_GET['q'] ?? '');
$tipe_transaksi = trim($_GET['tipe_transaksi'] ?? '');
$tipe           = trim($_GET['tipe'] ?? '');
$kota           = trim($_GET['kota'] ?? '');
$status         = trim($_GET['status'] ?? '');
$harga_min      = $_GET['harga_min'] ?? '';
$harga_max      = $_GET['harga_max'] ?? '';

$halaman        = max((int)($_GET['page'] ?? 1), 1);
$data_per_halaman = 9;
$offset         = ($halaman - 1) * $data_per_halaman;

// ------------------------------------------------------------------
// 2. Bangun WHERE clause secara dinamis
// ------------------------------------------------------------------
$kondisi = [];
$tipe_data = '';
$parameter = [];

if (in_array($status, ['tersedia', 'terjual'], true)) {
    $kondisi[] = 'status = ?';
    $parameter[] = $status;
    $tipe_data .= 's';
} else {
    $kondisi[] = "status = 'tersedia'";
}

if ($q !== '') {
    $kondisi[]  = "(judul LIKE ? OR alamat LIKE ? OR kota LIKE ?)";
    $keyword    = "%{$q}%";
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $tipe_data  .= 'sss';
}

if ($tipe_transaksi !== '' && in_array($tipe_transaksi, ['jual', 'sewa'], true)) {
    if ($tipe_transaksi === 'jual') {
        $kondisi[] = "tipe_transaksi = 'jual'";
    } else {
        $kondisi[] = "tipe_transaksi = 'sewa'";
    }
}

if ($tipe !== '' && in_array($tipe, ['rumah', 'apartemen', 'tanah', 'ruko'], true)) {
    $kondisi[]  = "tipe = ?";
    $parameter[] = $tipe;
    $tipe_data  .= 's';
}

if ($kota !== '') {
    $kondisi[]  = "kota LIKE ?";
    $parameter[] = "%{$kota}%";
    $tipe_data  .= 's';
}

if ($harga_min !== '' && is_numeric($harga_min)) {
    $kondisi[]  = "(harga >= ? OR harga_sewa >= ?)";
    $val_min = (int)$harga_min;
    $parameter[] = $val_min;
    $parameter[] = $val_min;
    $tipe_data  .= 'ii';
}

if ($harga_max !== '' && is_numeric($harga_max)) {
    $kondisi[]  = "(harga <= ? OR harga_sewa <= ?)";
    $val_max = (int)$harga_max;
    $parameter[] = $val_max;
    $parameter[] = $val_max;
    $tipe_data  .= 'ii';
}

$where_sql = implode(' AND ', $kondisi);

// ------------------------------------------------------------------
// 3. Hitung total data (buat pagination)
// ------------------------------------------------------------------
$query_total = "SELECT COUNT(*) AS total FROM properti WHERE {$where_sql}";
$stmt_total  = mysqli_prepare($koneksi, $query_total);
if (!empty($parameter)) {
    mysqli_stmt_bind_param($stmt_total, $tipe_data, ...$parameter);
}
mysqli_stmt_execute($stmt_total);
$total_data     = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['total'];
$total_halaman  = max((int)ceil($total_data / $data_per_halaman), 1);
mysqli_stmt_close($stmt_total);

// ------------------------------------------------------------------
// 4. Ambil data properti
// ------------------------------------------------------------------
$query_data = "SELECT id, judul, harga, harga_sewa, periode_sewa, tipe_transaksi, tipe, kota, kamar_tidur, kamar_mandi, luas_bangunan, gambar_url
               FROM properti
               WHERE {$where_sql}
               ORDER BY created_at DESC
               LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($koneksi, $query_data);
$parameter_lengkap = array_merge($parameter, [$data_per_halaman, $offset]);
$tipe_data_lengkap = $tipe_data . 'ii';
mysqli_stmt_bind_param($stmt, $tipe_data_lengkap, ...$parameter_lengkap);
mysqli_stmt_execute($stmt);
$daftar_properti = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Daftar kota unik
$daftar_kota = mysqli_fetch_all(
    mysqli_query($koneksi, "SELECT DISTINCT kota FROM properti ORDER BY kota"),
    MYSQLI_ASSOC
);

$user = user_login();
$page_title = 'Semua Properti — Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>
    <!-- ============ HERO ============ -->
    <header class="hero-listing">
        <div class="container pb-5">
            <div class="breadcrumb-estate mb-3">
                <a href="index.php">Beranda</a>
                <span class="sep">/</span>
                <span class="current">Properti</span>
            </div>
            <p class="hero-eyebrow mb-3">Jelajahi Koleksi &middot; <?= $total_data ?> Properti</p>
            <h1 class="mb-3">Semua <em>properti pilihan</em> kami.</h1>
            <p class="lead mb-4">
                Gunakan pencarian di bawah untuk menyaring properti berdasarkan tipe transaksi, kata kunci, kota, hingga harga.
            </p>

        </div>
    </header>

    <!-- ============ HASIL LISTING ============ -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4 align-items-start">
                <aside class="col-lg-3">
                    <form action="listing.php" method="GET" class="filter-panel p-3 sticky-lg-top" style="top:1rem;">
                        <h2 class="h5 fw-bold mb-3"><i class="bi bi-funnel me-1"></i> Filter Properti</h2>
                        <div class="mb-3"><label class="form-label small fw-semibold">Cari</label><input type="text" name="q" class="form-control form-control-sm" placeholder="Judul atau lokasi..." value="<?= htmlspecialchars($q) ?>"></div>
                        <div class="mb-3"><label class="form-label small fw-semibold">Tipe Transaksi</label><select name="tipe_transaksi" class="form-select form-select-sm"><option value="">Jual & Sewa</option><option value="jual" <?= $tipe_transaksi === 'jual' ? 'selected' : '' ?>>Dijual</option><option value="sewa" <?= $tipe_transaksi === 'sewa' ? 'selected' : '' ?>>Disewa</option></select></div>
                        <div class="mb-3"><label class="form-label small fw-semibold">Tipe Properti</label><select name="tipe" class="form-select form-select-sm"><option value="">Semua Tipe</option><?php foreach (['rumah', 'apartemen', 'tanah', 'ruko'] as $t): ?><option value="<?= $t ?>" <?= $tipe === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
                        <div class="mb-3"><label class="form-label small fw-semibold">Status</label><select name="status" class="form-select form-select-sm"><option value="">Tersedia</option><option value="tersedia" <?= $status === 'tersedia' ? 'selected' : '' ?>>Tersedia</option><option value="terjual" <?= $status === 'terjual' ? 'selected' : '' ?>>Terjual</option></select></div>
                        <div class="mb-3"><label class="form-label small fw-semibold">Kota</label><select name="kota" class="form-select form-select-sm"><option value="">Semua Kota</option><?php foreach ($daftar_kota as $k): ?><option value="<?= htmlspecialchars($k['kota']) ?>" <?= $kota === $k['kota'] ? 'selected' : '' ?>><?= htmlspecialchars($k['kota']) ?></option><?php endforeach; ?></select></div>
                        <div class="row g-2 mb-3"><div class="col-6"><label class="form-label small fw-semibold">Harga Min</label><input type="number" name="harga_min" class="form-control form-control-sm" value="<?= htmlspecialchars($harga_min) ?>"></div><div class="col-6"><label class="form-label small fw-semibold">Harga Max</label><input type="number" name="harga_max" class="form-control form-control-sm" value="<?= htmlspecialchars($harga_max) ?>"></div></div>
                        <button type="submit" class="btn btn-gold w-100 mb-2"><i class="bi bi-search me-1"></i> Terapkan Filter</button>
                        <a href="listing.php" class="btn btn-outline-navy btn-sm w-100">Reset</a>
                    </form>
                </aside>
                <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
                <div>
                    <p class="section-eyebrow mb-2">Hasil Pencarian</p>
                    <h2 class="section-title mb-0">
                        Menampilkan <?= count($daftar_properti) ?> dari <?= $total_data ?> Properti
                    </h2>
                </div>
            </div>

            <?php if (empty($daftar_properti)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-house-x" style="font-size:2.5rem; color:var(--gold-500);"></i>
                    <p class="text-muted mt-3">Tidak ada properti yang cocok dengan filter kamu.</p>
                    <a href="listing.php" class="btn btn-outline-navy mt-2">Reset Filter</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($daftar_properti as $p): ?>
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

                <!-- PAGINATION -->
                <?php if ($total_halaman > 1): ?>
                    <div class="pagination-estate mt-5 justify-content-center">
                        <?php
                            $qs = $_GET;
                            if ($halaman > 1):
                                $qs['page'] = $halaman - 1;
                        ?>
                            <a href="listing.php?<?= http_build_query($qs) ?>"><i class="bi bi-chevron-left"></i></a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                            <?php $qs['page'] = $i; ?>
                            <a href="listing.php?<?= http_build_query($qs) ?>" class="<?= $i === $halaman ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($halaman < $total_halaman): $qs['page'] = $halaman + 1; ?>
                            <a href="listing.php?<?= http_build_query($qs) ?>"><i class="bi bi-chevron-right"></i></a>
                        <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>