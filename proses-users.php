<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
cek_csrf();
$aksi = $_POST['aksi'] ?? '';

// AKSI: TAMBAH - admin bikin akun baru + langsung pilih role, gak lewat proses-users
if ($aksi === 'tambah') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $no_hp = trim($_POST['no_hp'] ?? '');
    $role = $_POST['role'] ?? 'user';

    if ($nama === '') die('Nama wajib diisi.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die('Format email tidak valid.');
    if (strlen($password) < 6) die('Password minimal 6 karakter.');
    if (!in_array($role, ['admin', 'user'], true)) die('Role tidak valid.');

    $stmt = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
        mysqli_stmt_close($stmt);
        die('Email sudah terdaftar.');
    }
    mysqli_stmt_close($stmt);

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($koneksi, 'INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $password_hash, $no_hp, $role);
    if (!mysqli_stmt_execute($stmt)) die('User gagal ditambahkan.');
    mysqli_stmt_close($stmt);

    header('Location: admin-users.php?pesan=tambah-berhasil');
    exit;
}

// AKSI: EDIT - admin ubah data akun user lain (nama/email/no_hp/role sekaligus dari 1 form)
if ($aksi === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) die('User tidak valid.');

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $role = $_POST['role'] ?? '';
    $is_diri_sendiri = $id === (int) $_SESSION['user_id'];

    if ($nama === '') die('Nama wajib diisi.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die('Format email tidak valid.');
    if (!in_array($role, ['admin', 'user'], true)) die('Role tidak valid.');

    $stmt = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE email = ? AND id != ?');
    mysqli_stmt_bind_param($stmt, 'si', $email, $id);
    mysqli_stmt_execute($stmt);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
        mysqli_stmt_close($stmt);
        die('Email sudah dipakai akun lain.');
    }
    mysqli_stmt_close($stmt);

    if ($is_diri_sendiri) {
        // Gak boleh ubah role sendiri (biar gak ada admin yang gak sengaja demote diri sendiri sampai kekunci)
        $stmt = mysqli_prepare($koneksi, 'UPDATE users SET nama = ?, email = ?, no_hp = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'sssi', $nama, $email, $no_hp, $id);
    } else {
        $stmt = mysqli_prepare($koneksi, 'UPDATE users SET nama = ?, email = ?, no_hp = ?, role = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $email, $no_hp, $role, $id);
    }
    if (!mysqli_stmt_execute($stmt)) die('Data user gagal diperbarui.');
    mysqli_stmt_close($stmt);

    header('Location: admin-users.php?pesan=edit-berhasil');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) die('User tidak valid.');
if ($id === (int) $_SESSION['user_id']) die('Akun sendiri tidak dapat diubah atau dihapus.');

if ($aksi === 'hapus') {
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) die('User gagal dihapus.');
    mysqli_stmt_close($stmt);
    header('Location: admin-users.php?pesan=hapus-berhasil');
    exit;
}

die('Aksi tidak dikenali.');