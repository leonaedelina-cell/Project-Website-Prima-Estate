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

// Pesan sukses/gagal dari redirect proses-wishlist.php / proses-transaksi.php (lihat query string ?pesan=...)
$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($properti['judul']) ?> - Estate Prima (data test)</title>
</head>
<body>
    <p><a href="listing.php">&larr; Kembali ke Listing</a></p>

    <?php if ($pesan === 'wishlist-ditambah'): ?>
        <p style="color:green;">Berhasil ditambahkan ke wishlist.</p>
    <?php elseif ($pesan === 'wishlist-dihapus'): ?>
        <p style="color:green;">Berhasil dihapus dari wishlist.</p>
    <?php elseif ($pesan === 'pengajuan-berhasil'): ?>
        <p style="color:green;">Pengajuan beli berhasil dikirim! Cek status di halaman Pesanan.</p>
    <?php elseif ($pesan === 'sudah-diajukan'): ?>
        <p style="color:orange;">Kamu sudah pernah mengajukan pembelian untuk properti ini.</p>
    <?php endif; ?>

    <h1><?= htmlspecialchars($properti['judul']) ?></h1>
    <p>Rp <?= number_format($properti['harga'], 0, ',', '.') ?></p>
    <p><?= htmlspecialchars($properti['alamat']) ?>, <?= htmlspecialchars($properti['kota']) ?></p>
    <p>Status: <b><?= ucfirst($properti['status']) ?></b></p>

    <h3>Fasilitas</h3>
    <ul>
        <li>Tipe: <?= ucfirst($properti['tipe']) ?></li>
        <li>Luas Tanah: <?= $properti['luas_tanah'] ?? '-' ?> m2</li>
        <li>Luas Bangunan: <?= $properti['luas_bangunan'] ?? '-' ?> m2</li>
        <li>Kamar Tidur: <?= $properti['kamar_tidur'] ?></li>
        <li>Kamar Mandi: <?= $properti['kamar_mandi'] ?></li>
        <li>Carport: <?= $properti['carport'] ?></li>
    </ul>

    <h3>Deskripsi</h3>
    <p><?= nl2br(htmlspecialchars($properti['deskripsi'])) ?></p>

    <h3>Galeri</h3>
    <?php if (empty($galeri)): ?>
        <p>Gambar utama: <?= htmlspecialchars($properti['gambar_url'] ?? '-') ?></p>
    <?php else: ?>
        <?php foreach ($galeri as $g): ?>
            <p><?= htmlspecialchars($g['gambar_url']) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($properti['nama_agen']): ?>
        <h3>Agen Sales</h3>
        <p><?= htmlspecialchars($properti['nama_agen']) ?> - <?= htmlspecialchars($properti['no_hp_agen']) ?></p>
    <?php endif; ?>

    <h3>Lokasi</h3>
    <iframe
        src="https://www.google.com/maps?q=<?= urlencode($properti['alamat'] . ', ' . $properti['kota']) ?>&output=embed"
        style="border:0; width:100%; height:300px;"
        allowfullscreen loading="lazy">
    </iframe>

    <hr>

    <?php if (!$user): ?>
        <p>Silakan <a href="login.php">login</a> dulu untuk menambah wishlist atau mengajukan pembelian.</p>
    <?php else: ?>
        <!-- Tombol Wishlist -->
        <form method="POST" action="proses-wishlist.php" style="display:inline;">
            <input type="hidden" name="properti_id" value="<?= $properti['id'] ?>">
            <button type="submit">
                <?= $sudah_wishlist ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' ?>
            </button>
        </form>

        <!-- Form Ajukan Beli -->
        <?php if ($properti['status'] === 'tersedia'): ?>
            <form method="POST" action="proses-transaksi.php" style="display:inline;">
                <input type="hidden" name="properti_id" value="<?= $properti['id'] ?>">
                <select name="metode_bayar" required>
                    <option value="">Pilih Metode Bayar</option>
                    <option value="transfer_bank">Transfer Bank</option>
                    <option value="cicilan_kpr">Cicilan KPR</option>
                    <option value="tunai">Tunai</option>
                </select>
                <button type="submit">Ajukan Beli</button>
            </form>
        <?php else: ?>
            <p><i>Properti ini sudah terjual.</i></p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>