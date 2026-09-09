<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
cek_csrf();
$aksi = $_POST['aksi'] ?? '';
$id = (int)($_POST['id'] ?? 0);
if ($aksi === 'tambah') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || !in_array($role, ['admin', 'user'], true)) {
        die('Data user tidak valid.');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($koneksi, 'INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $hash, $no_hp, $role);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        die('User gagal ditambahkan. Pastikan email belum digunakan.');
    }
    mysqli_stmt_close($stmt);
    header('Location: admin-users.php?pesan=tambah-berhasil');
    exit;
}

if ($id <= 0) die('User tidak valid.');

if ($aksi === 'edit' || $aksi === 'ubah-role') {
    $id_user_login = $id === (int)$_SESSION['user_id'];
    if ($aksi === 'ubah-role') {
        if ($id === (int) $_SESSION['user_id']) die('Role akun sendiri tidak dapat diubah.');
        $role = $_POST['role'] ?? '';
        if (!in_array($role, ['admin', 'user'], true)) die('Role tidak valid.');
        $stmt = mysqli_prepare($koneksi, 'UPDATE users SET role = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'si', $role, $id);
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $role = $_POST['role'] ?? 'user';
        if ($id === (int) $_SESSION['user_id']) {
            $role = $_SESSION['role'];
        }
        if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'user'], true)) die('Data user tidak valid.');
        $stmt = mysqli_prepare($koneksi, 'UPDATE users SET nama = ?, email = ?, no_hp = ?, role = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $email, $no_hp, $role, $id);
    }
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        die('User gagal diperbarui. Pastikan email belum digunakan.');
    }
    mysqli_stmt_close($stmt);

    if ($aksi === 'edit' && $id_user_login) {
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
    }

    header('Location: admin-users.php?pesan=edit-berhasil');
    exit;
}

if ($aksi === 'hapus') {
    if ($id === (int) $_SESSION['user_id']) die('Akun sendiri tidak dapat dihapus.');
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) die('User gagal dihapus.');
    mysqli_stmt_close($stmt);
    header('Location: admin-users.php?pesan=hapus-berhasil');
    exit;
}

die('Aksi tidak dikenali.');