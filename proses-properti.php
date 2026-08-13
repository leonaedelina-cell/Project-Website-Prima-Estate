<?php
/**
 * pages/admin/proses-properti.php - Estate Prima
 * Satu file buat handle 3 aksi CRUD properti: tambah, edit, hapus.
 * Dibedakan lewat field hidden "aksi" di masing-masing form.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

cek_admin();

$aksi = $_POST['aksi'] ?? '';

// ------------------------------------------------------------------
// AKSI: HAPUS
// ------------------------------------------------------------------
if ($aksi === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = mysqli_prepare($koneksi, "DELETE FROM properti WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // Catatan: wishlist, galeri_properti, & transaksi terkait ikut kehapus otomatis
    // karena FOREIGN KEY-nya di schema.sql pakai ON DELETE CASCADE.

    header('Location: admin-properti.php?pesan=hapus-berhasil');
    exit;
}

// ------------------------------------------------------------------
// AKSI: TAMBAH atau EDIT (field form-nya sama persis, cuma beda query)
// ------------------------------------------------------------------
if ($aksi === 'tambah' || $aksi === 'edit') {

    $judul         = trim($_POST['judul'] ?? '');
    $deskripsi     = trim($_POST['deskripsi'] ?? '');
    $harga         = (int)($_POST['harga'] ?? 0);
    $tipe          = $_POST['tipe'] ?? 'rumah';
    $status        = $_POST['status'] ?? 'tersedia'; // cuma dipakai saat edit, saat tambah selalu default 'tersedia'
    $alamat        = trim($_POST['alamat'] ?? '');
    $kota          = trim($_POST['kota'] ?? '');
    $lat           = $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
    $lng           = $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;
    $luas_tanah    = $_POST['luas_tanah'] !== '' ? (int)$_POST['luas_tanah'] : null;
    $luas_bangunan = $_POST['luas_bangunan'] !== '' ? (int)$_POST['luas_bangunan'] : null;
    $kamar_tidur   = (int)($_POST['kamar_tidur'] ?? 0);
    $kamar_mandi   = (int)($_POST['kamar_mandi'] ?? 0);
    $carport       = (int)($_POST['carport'] ?? 0);
    $gambar_url    = trim($_POST['gambar_url'] ?? '');
    $agen_id       = $_POST['agen_id'] !== '' ? (int)$_POST['agen_id'] : null;

    // Validasi dasar
    if ($judul === '' || $harga <= 0 || $alamat === '' || $kota === '') {
        die('Data tidak lengkap. Judul, harga, alamat, dan kota wajib diisi.');
    }

    if ($aksi === 'tambah') {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO properti
                (judul, deskripsi, harga, tipe, alamat, kota, lat, lng,
                 luas_tanah, luas_bangunan, kamar_tidur, kamar_mandi, carport, gambar_url, agen_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt, "ssisssddiiiiisi",
            $judul, $deskripsi, $harga, $tipe, $alamat, $kota, $lat, $lng,
            $luas_tanah, $luas_bangunan, $kamar_tidur, $kamar_mandi, $carport, $gambar_url, $agen_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header('Location: admin-properti.php?pesan=tambah-berhasil');
        exit;
    }

    if ($aksi === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            die('ID properti tidak valid.');
        }

        $stmt = mysqli_prepare($koneksi,
            "UPDATE properti SET
                judul = ?, deskripsi = ?, harga = ?, tipe = ?, status = ?, alamat = ?, kota = ?,
                lat = ?, lng = ?, luas_tanah = ?, luas_bangunan = ?, kamar_tidur = ?, kamar_mandi = ?,
                carport = ?, gambar_url = ?, agen_id = ?
             WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt, "ssissssddiiiiisii",
            $judul, $deskripsi, $harga, $tipe, $status, $alamat, $kota, $lat, $lng,
            $luas_tanah, $luas_bangunan, $kamar_tidur, $kamar_mandi, $carport, $gambar_url, $agen_id, $id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header('Location: admin-properti.php?pesan=edit-berhasil');
        exit;
    }
}

// Kalau $aksi gak dikenali sama sekali
die('Aksi tidak dikenali.');