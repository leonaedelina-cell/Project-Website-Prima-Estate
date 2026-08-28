<?php
/**
 * listing.php - Estate Prima
 * Semua properti - search + filter (tipe, kota, rentang harga) + pagination
 *
 * Query string yang didukung:
 *   ?q=green valley          -> cari di judul/alamat/kota
 *   &tipe=rumah              -> filter tipe (rumah/apartemen/tanah/ruko)
 *   &kota=Bekasi             -> filter kota
 *   &harga_min=100000000
 *   &harga_max=900000000
 *   &page=2                  -> pagination
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// ------------------------------------------------------------------
// 1. Ambil & bersihkan input filter dari query string
// ------------------------------------------------------------------
$q         = trim($_GET['q'] ?? '');
$tipe      = trim($_GET['tipe'] ?? '');
$tipe_transaksi = trim($_GET['tipe_transaksi'] ?? '');
$kota      = trim($_GET['kota'] ?? '');
$harga_min = $_GET['harga_min'] ?? '';
$harga_max = $_GET['harga_max'] ?? '';

$halaman        = max((int)($_GET['page'] ?? 1), 1);
$data_per_halaman = 9;
$offset         = ($halaman - 1) * $data_per_halaman;

// ------------------------------------------------------------------
// 2. Bangun WHERE clause secara dinamis, TAPI tetap pakai prepared statement
// ------------------------------------------------------------------
$kondisi = ["status = 'tersedia'"];
$tipe_data = '';
$parameter = [];

if ($q !== '') {
    $kondisi[]  = "(judul LIKE ? OR alamat LIKE ? OR kota LIKE ?)";
    $keyword    = "%{$q}%";
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $tipe_data  .= 'sss';
}

if ($tipe !== '' && in_array($tipe, ['rumah', 'apartemen', 'tanah', 'ruko'])) {
    $kondisi[]  = "tipe = ?";
    $parameter[] = $tipe;
    $tipe_data  .= 's';
}

if ($tipe_transaksi !== '' && in_array($tipe_transaksi, ['jual', 'sewa'], true)) {
    $kondisi[]  = "tipe_transaksi = ?";
    $parameter[] = $tipe_transaksi;
    $tipe_data  .= 's';
}

if ($kota !== '') {
    $kondisi[]  = "kota LIKE ?";
    $parameter[] = "%{$kota}%";
    $tipe_data  .= 's';
}

if ($harga_min !== '' && is_numeric($harga_min)) {
    $kondisi[]  = "harga >= ?";
    $parameter[] = (int)$harga_min;
    $tipe_data  .= 'i';
}

if ($harga_max !== '' && is_numeric($harga_max)) {
    $kondisi[]  = "harga <= ?";
    $parameter[] = (int)$harga_max;
    $tipe_data  .= 'i';
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
// 4. Ambil data properti sesuai filter + pagination
// ------------------------------------------------------------------
$query_data = "SELECT id, judul, harga, tipe, tipe_transaksi, kota, kamar_tidur, kamar_mandi, luas_bangunan, gambar_url
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

// Daftar kota unik buat dropdown filter
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
                Gunakan pencarian di bawah untuk menyaring properti berdasarkan kata kunci,
                tipe, kota, hingga rentang harga yang sesuai kebutuhanmu.
            </p>

            <!-- Search card, mengambang di bawah hero -->
            <div class="row justify-content-start">
                <div class="col-lg-12">
                    <div class="floating-card field-panel mt-2">
                        <span class="corner-tick tl"></span>
                        <span class="corner-tick tr"></span>
                        <span class="corner-tick bl"></span>
                        <span class="corner-tick br"></span>

                        <form action="listing.php" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="d-block">Kata Kunci</label>
                                <input type="text" name="q" class="form-control" placeholder="Judul, alamat, kota..."
                                       value="<?= htmlspecialchars($q) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="d-block">Tipe</label>
                                <select name="tipe" class="form-select">
                                    <option value="">Semua Tipe</option>
                                    <?php foreach (['rumah', 'apartemen', 'tanah', 'ruko'] as $t): ?>
                                        <option value="<?= $t ?>" <?= $tipe === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="d-block">Jual/Sewa</label>
                                <select name="tipe_transaksi" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="jual" <?= $tipe_transaksi === 'jual' ? 'selected' : '' ?>>Jual</option>
                                    <option value="sewa" <?= $tipe_transaksi === 'sewa' ? 'selected' : '' ?>>Sewa</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="d-block">Kota</label>
                                <select name="kota" class="form-select">
                                    <option value="">Semua Kota</option>
                                    <?php foreach ($daftar_kota as $k): ?>
                                        <option value="<?= htmlspecialchars($k['kota']) ?>" <?= $kota === $k['kota'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['kota']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="d-block">Harga Min</label>
                                <input type="number" name="harga_min" class="form-control" placeholder="Rp"
                                       value="<?= htmlspecialchars($harga_min) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="d-block">Harga Max</label>
                                <input type="number" name="harga_max" class="form-control" placeholder="Rp"
                                       value="<?= htmlspecialchars($harga_max) ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-gold w-100 py-2" title="Cari Properti">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                        <?php if ($q !== '' || $tipe !== '' || $tipe_transaksi !== '' || $kota !== '' || $harga_min !== '' || $harga_max !== ''): ?>
                            <div class="mt-3">
                                <a href="listing.php" class="btn btn-outline-navy btn-sm px-3">
                                    <i class="bi bi-x-circle me-1"></i> Reset Filter
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ============ HASIL LISTING ============ -->
    <section class="py-5">
        <div class="container">
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
                        <div class="col-md-6 col-lg-4">
                            <div class="property-card">
                                <span class="corner-tick tl"></span>
                                <span class="corner-tick br"></span>

                                <div class="thumb" style="background-image:url('<?= htmlspecialchars($p['gambar_url'] ?: 'https://images.unsplash.com/photo-1568605114967-8130f3a36994') ?>');">
                                    <span class="type-tag"><?= ucfirst($p['tipe']) ?> · <?= $p['tipe_transaksi'] === 'sewa' ? 'Sewa' : 'Jual' ?></span>
                                    <span class="price-tag">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
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

                <!-- ============ PAGINATION ============ -->
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
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>