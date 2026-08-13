<?php
/**
 * pages/admin/proses-pesan.php - Estate Prima
 * Tandai pesan kontak sebagai sudah dibaca, atau hapus.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

cek_admin();

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    die('ID pesan tidak valid.');
}

if ($aksi === 'tandai-dibaca') {
    $stmt = mysqli_prepare($koneksi, "UPDATE pesan_kontak SET status_dibaca = 'sudah' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} elseif ($aksi === 'hapus') {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM pesan_kontak WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    die('Aksi tidak dikenali.');
}

header('Location: admin-pesan.php');
exit;