<?php
/**
 * proses-wishlist.php - Estate Prima
 * Toggle wishlist: kalau belum ada -> insert, kalau udah ada -> delete.
 * Dipanggil dari form di detail.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login(); // wajib login, kalau belum akan otomatis redirect ke login.php
cek_csrf();

$user_id     = $_SESSION['user_id'];
$properti_id = (int)($_POST['properti_id'] ?? 0);

if ($properti_id <= 0) {
    die('Properti tidak valid.');
}

// Cek apakah sudah ada di wishlist
$stmt = mysqli_prepare($koneksi, "SELECT id FROM wishlist WHERE user_id = ? AND properti_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $properti_id);
mysqli_stmt_execute($stmt);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($existing) {
    // Sudah ada -> hapus (toggle off)
    $stmt = mysqli_prepare($koneksi, "DELETE FROM wishlist WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $existing['id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $pesan = 'wishlist-dihapus';
} else {
    // Belum ada -> tambah (toggle on)
    $stmt = mysqli_prepare($koneksi, "INSERT INTO wishlist (user_id, properti_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $properti_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $pesan = 'wishlist-ditambah';
}

header("Location: detail.php?id={$properti_id}&pesan={$pesan}");
exit;