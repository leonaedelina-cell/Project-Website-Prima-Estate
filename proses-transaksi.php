<?php
/**
 * proses-transaksi.php - Estate Prima
 * Menangani submit "Ajukan Beli" dari detail.php.
 * Insert baris baru ke tabel transaksi dengan status default 'menunggu'.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login();

$user_id      = $_SESSION['user_id'];
$properti_id  = (int)($_POST['properti_id'] ?? 0);
$metode_bayar = $_POST['metode_bayar'] ?? '';

$metode_valid = ['transfer_bank', 'cicilan_kpr', 'tunai'];

if ($properti_id <= 0 || !in_array($metode_bayar, $metode_valid)) {
    die('Data pengajuan tidak valid.');
}

// Cegah user mengajukan 2x untuk properti yang sama selagi masih 'menunggu'/'diproses'
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM transaksi
     WHERE user_id = ? AND properti_id = ? AND status IN ('menunggu', 'diproses')"
);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $properti_id);
mysqli_stmt_execute($stmt);
$sudah_ada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($sudah_ada) {
    header("Location: detail.php?id={$properti_id}&pesan=sudah-diajukan");
    exit;
}

// Insert pengajuan baru
$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO transaksi (user_id, properti_id, metode_bayar, status) VALUES (?, ?, ?, 'menunggu')"
);
mysqli_stmt_bind_param($stmt, "iis", $user_id, $properti_id, $metode_bayar);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: detail.php?id={$properti_id}&pesan=pengajuan-berhasil");
exit;