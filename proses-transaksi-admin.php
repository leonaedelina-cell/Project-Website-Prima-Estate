<?php
/**
 * pages/admin/proses-transaksi-admin.php - Estate Prima
 * Update transaksi (metode bayar, bukti bayar, status, catatan admin).
 * Kalau status diubah jadi 'disetujui'/'selesai', properti terkait otomatis ditandai 'terjual'.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
cek_csrf();

$id            = (int)($_POST['id'] ?? 0);
$metode_bayar  = $_POST['metode_bayar'] ?? '';
$bukti_bayar   = trim($_POST['bukti_bayar'] ?? '');
$status        = $_POST['status'] ?? '';
$catatan_admin = trim($_POST['catatan_admin'] ?? '');

$status_valid = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai', 'lunas'];

if ($id <= 0 || !in_array($status, $status_valid)) {
    die('Data tidak valid.');
}

// metode_bayar boleh kosong (NULL), tapi kalau diisi harus valid
$metode_bayar_final = $metode_bayar !== '' ? $metode_bayar : null;
$bukti_bayar_final  = $bukti_bayar !== '' ? $bukti_bayar : null;

// Ambil dulu properti_id dari transaksi ini, buat update status properti kalau perlu
$stmt = mysqli_prepare($koneksi, "SELECT properti_id, tipe_transaksi FROM transaksi WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$row) {
    die('Transaksi tidak ditemukan.');
}
$properti_id = $row['properti_id'];
$tipe_transaksi = $row['tipe_transaksi'];

// Update transaksi
$stmt = mysqli_prepare($koneksi,
    "UPDATE transaksi SET metode_bayar = ?, bukti_bayar = ?, status = ?, catatan_admin = ? WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "ssssi", $metode_bayar_final, $bukti_bayar_final, $status, $catatan_admin, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Kalau disetujui/selesai -> tandai properti jadi 'terjual'
if ($tipe_transaksi === 'jual' && in_array($status, ['disetujui', 'selesai', 'lunas'], true)) {
    $stmt = mysqli_prepare($koneksi, "UPDATE properti SET status = 'terjual' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $properti_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Kalau ditolak -> pastikan properti balik 'tersedia' (jaga-jaga kalau sebelumnya sempat disetujui lalu dibatalkan)
if ($tipe_transaksi === 'jual' && $status === 'ditolak') {
    $stmt = mysqli_prepare($koneksi, "UPDATE properti SET status = 'tersedia' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $properti_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: admin-transaksi-detail.php?id={$id}&pesan=update-berhasil");
exit;
