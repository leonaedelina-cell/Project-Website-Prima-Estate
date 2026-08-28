<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
cek_csrf();
$aksi = $_POST['aksi'] ?? '';
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) die('User tidak valid.');
if ($id === (int) $_SESSION['user_id']) die('Akun sendiri tidak dapat diubah atau dihapus.');

if ($aksi === 'ubah-role') {
    $role = $_POST['role'] ?? '';
    if (!in_array($role, ['admin', 'user'], true)) die('Role tidak valid.');
    $stmt = mysqli_prepare($koneksi, 'UPDATE users SET role = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'si', $role, $id);
    if (!mysqli_stmt_execute($stmt)) die('Role gagal diperbarui.');
    mysqli_stmt_close($stmt);
    header('Location: admin-users.php?pesan=role-berhasil');
    exit;
}

if ($aksi === 'hapus') {
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) die('User gagal dihapus.');
    mysqli_stmt_close($stmt);
    header('Location: admin-users.php?pesan=hapus-berhasil');
    exit;
}

die('Aksi tidak dikenali.');