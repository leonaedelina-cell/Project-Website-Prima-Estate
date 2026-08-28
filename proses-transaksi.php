<?php
/**
 * proses-transaksi.php - Estate Prima
 * Menangani submit "Ajukan Beli" dari detail.php.
 * Insert baris baru ke tabel transaksi dengan status default 'menunggu'.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login();
cek_csrf();

$user_id      = $_SESSION['user_id'];
$properti_id  = (int)($_POST['properti_id'] ?? 0);
$metode_bayar = $_POST['metode_bayar'] ?? '';

$metode_valid = ['transfer_bank', 'cicilan_kpr', 'tunai'];

if ($properti_id <= 0 || !in_array($metode_bayar, $metode_valid)) {
    die('Data pengajuan tidak valid.');
}

mysqli_begin_transaction($koneksi);
try {
    $stmt = mysqli_prepare($koneksi, "SELECT status FROM properti WHERE id = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "i", $properti_id);
    mysqli_stmt_execute($stmt);
    $properti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$properti || $properti['status'] !== 'tersedia') {
        throw new RuntimeException('Properti sudah tidak tersedia.');
    }

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
        mysqli_rollback($koneksi);
        header("Location: detail.php?id={$properti_id}&pesan=sudah-diajukan");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO transaksi (user_id, properti_id, metode_bayar, status) VALUES (?, ?, ?, 'menunggu')"
    );
    mysqli_stmt_bind_param($stmt, "iis", $user_id, $properti_id, $metode_bayar);
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Pengajuan gagal disimpan.');
    }
    mysqli_stmt_close($stmt);
    mysqli_commit($koneksi);
} catch (Throwable $error) {
    mysqli_rollback($koneksi);
    die($error->getMessage());
}

$stmt = mysqli_prepare($koneksi, "SELECT judul FROM properti WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $properti_id);
mysqli_stmt_execute($stmt);
$properti_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$pesan_wa = 'Halo, saya ingin mengajukan pembelian properti "' . ($properti_data['judul'] ?? 'Properti') . '" (ID: ' . $properti_id . ').';
$wa_link = 'https://wa.me/' . WHATSAPP_ADMIN . '?text=' . urlencode($pesan_wa);
header('Location: ' . $wa_link);
exit;