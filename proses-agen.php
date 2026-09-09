<?php
/**
 * proses-agen.php - Estate Prima
 * Menangani aksi tambah, edit, dan hapus agen dari panel admin.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
cek_csrf();

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        die('ID agen tidak valid.');
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM agen WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header('Location: admin-agen.php?pesan=hapus-berhasil');
    exit;
}

if ($aksi === 'tambah' || $aksi === 'edit') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $foto_url = trim($_POST['foto_url'] ?? '');

    if ($nama === '' || $no_hp === '' || $email === '') {
        die('Nama, nomor HP, dan email agen wajib diisi.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Format email agen tidak valid.');
    }

    $foto_url = trim($_POST['foto_url'] ?? '');
    $foto_baru = $_FILES['foto'] ?? null;
    $foto_lama = '';

    if ($aksi === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            die('ID agen tidak valid.');
        }

        $stmt = mysqli_prepare($koneksi, "SELECT foto_url FROM agen WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $data_lama = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        if (!$data_lama) {
            die('Agen tidak ditemukan.');
        }
        $foto_lama = $data_lama['foto_url'] ?? '';
    }

    if ($foto_baru && $foto_baru['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($foto_baru['error'] !== UPLOAD_ERR_OK) {
            die('Upload foto gagal.');
        }
        if ($foto_baru['size'] > 400 * 1024) {
            die('Ukuran foto maksimal 400 KB.');
        }

        $info_gambar = @getimagesize($foto_baru['tmp_name']);
        $tipe_gambar = $info_gambar['mime'] ?? '';
        $tipe_diizinkan = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!$info_gambar || !isset($tipe_diizinkan[$tipe_gambar])) {
            die('Format foto harus JPG, PNG, atau WEBP.');
        }

        $nama_file = bin2hex(random_bytes(16)) . '.' . $tipe_diizinkan[$tipe_gambar];
        $folder_upload = __DIR__ . '/assets/uploads/agen/';
        if (!is_dir($folder_upload) && !mkdir($folder_upload, 0755, true)) {
            die('Folder upload foto tidak dapat dibuat.');
        }
        if (!move_uploaded_file($foto_baru['tmp_name'], $folder_upload . $nama_file)) {
            die('Foto gagal disimpan.');
        }

        $foto_url = BASE_URL . 'assets/uploads/agen/' . $nama_file;
    } elseif ($aksi === 'edit') {
        $foto_url = $foto_lama;
    }

    if ($aksi === 'tambah') {
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO agen (nama, no_hp, email, foto_url) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $no_hp, $email, $foto_url);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header('Location: admin-agen.php?pesan=tambah-berhasil');
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE agen SET nama = ?, no_hp = ?, email = ?, foto_url = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssssi", $nama, $no_hp, $email, $foto_url, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($foto_baru && $foto_baru['error'] === UPLOAD_ERR_OK && $foto_lama !== '') {
        $path_lama = parse_url($foto_lama, PHP_URL_PATH);
        if (strpos($path_lama, '/assets/uploads/agen/') !== false) {
            $file_lama = __DIR__ . '/assets/uploads/agen/' . basename($path_lama);
            if (is_file($file_lama)) {
                unlink($file_lama);
            }
        }
    }

    header('Location: admin-agen.php?pesan=edit-berhasil');
    exit;
}

die('Aksi tidak dikenali.');
