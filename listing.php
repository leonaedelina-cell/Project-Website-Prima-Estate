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
$kota      = trim($_GET['kota'] ?? '');
$harga_min = $_GET['harga_min'] ?? '';
$harga_max = $_GET['harga_max'] ?? '';

$halaman        = max((int)($_GET['page'] ?? 1), 1);
$data_per_halaman = 9;
$offset         = ($halaman - 1) * $data_per_halaman;

// ------------------------------------------------------------------
// 2. Bangun WHERE clause secara dinamis, TAPI tetap pakai prepared statement
//    (bukan concat langsung ke query, biar aman dari SQL Injection)
// ------------------------------------------------------------------
$kondisi = ["status = 'tersedia'"]; // default: cuma tampilkan yang masih tersedia
$tipe_data = '';   // buat bind_param, misal "ssdd"
$parameter = [];   // array nilai yang akan di-bind

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
// 3. Hitung total data (buat pagination) dengan filter yang sama
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
$query_data = "SELECT id, judul, harga, tipe, kota, kamar_tidur, kamar_mandi, luas_bangunan, gambar_url
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Semua Properti - Estate Prima (data test)</title>
</head>
<body>
    <h1>Semua Properti</h1>
    <p><a href="index.php">&larr; Kembali ke Homepage</a></p>

    <!-- Form filter, action ke halaman ini sendiri (method GET biar bisa di-bookmark/share URL) -->
    <form method="GET" action="listing.php">
        <input type="text" name="q" placeholder="Cari judul/alamat/kota..." value="<?= htmlspecialchars($q) ?>">

        <select name="tipe">
            <option value="">Semua Tipe</option>
            <?php foreach (['rumah', 'apartemen', 'tanah', 'ruko'] as $t): ?>
                <option value="<?= $t ?>" <?= $tipe === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="kota" placeholder="Kota" value="<?= htmlspecialchars($kota) ?>">
        <input type="number" name="harga_min" placeholder="Harga Min" value="<?= htmlspecialchars($harga_min) ?>">
        <input type="number" name="harga_max" placeholder="Harga Max" value="<?= htmlspecialchars($harga_max) ?>">

        <button type="submit">Cari</button>
    </form>

    <p><?= $total_data ?> properti ditemukan</p>

    <?php if (empty($daftar_properti)): ?>
        <p>Tidak ada properti yang cocok dengan filter kamu.</p>
    <?php else: ?>
        <?php foreach ($daftar_properti as $p): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <h3><a href="detail.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['judul']) ?></a></h3>
                <p>Rp <?= number_format($p['harga'], 0, ',', '.') ?> - <?= htmlspecialchars($p['kota']) ?> (<?= ucfirst($p['tipe']) ?>)</p>
                <p><?= $p['kamar_tidur'] ?> KT | <?= $p['kamar_mandi'] ?> KM | <?= $p['luas_bangunan'] ?> m2</p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pagination sederhana -->
    <div>
        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
            <?php
                $query_string = $_GET;
                $query_string['page'] = $i;
            ?>
            <a href="listing.php?<?= http_build_query($query_string) ?>"
               style="<?= $i === $halaman ? 'font-weight:bold; text-decoration:underline;' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</body>
</html>