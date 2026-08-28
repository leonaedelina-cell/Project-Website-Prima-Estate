<?php
/**
 * pages/admin/proses-galeri.php - Estate Prima
 * Tambah/hapus 1 foto dari galeri_properti.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
cek_csrf();

$aksi        = $_POST['aksi'] ?? '';
$properti_id = (int)($_POST['properti_id'] ?? 0);

if ($properti_id <= 0) {
    die('Properti tidak valid.');
}

if ($aksi === 'tambah') {
    $gambar_url = trim($_POST['gambar_url'] ?? '');
    if ($gambar_url === '') {
        die('URL gambar wajib diisi.');
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO galeri_properti (properti_id, gambar_url) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $properti_id, $gambar_url);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

} elseif ($aksi === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = mysqli_prepare($koneksi, "DELETE FROM galeri_properti WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

} else {
    die('Aksi tidak dikenali.');
}

header("Location: properti-galeri.php?id={$properti_id}");
exit;
