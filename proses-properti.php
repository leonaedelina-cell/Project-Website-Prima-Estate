<?php
/**
 * pages/admin/proses-properti.php - Estate Prima
 * Satu file buat handle 3 aksi CRUD properti: tambah, edit, hapus.
 * Dibedakan lewat field hidden "aksi" di masing-masing form.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
cek_csrf();

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
    $lat_input     = $_POST['lat'] ?? '';
    $lng_input     = $_POST['lng'] ?? '';
    $tanah_input   = $_POST['luas_tanah'] ?? '';
    $bangunan_input = $_POST['luas_bangunan'] ?? '';
    $agen_input    = $_POST['agen_id'] ?? '';
    $lat           = $lat_input !== '' ? (float)$lat_input : null;
    $lng           = $lng_input !== '' ? (float)$lng_input : null;
    $luas_tanah    = $tanah_input !== '' ? (int)$tanah_input : null;
    $luas_bangunan = $bangunan_input !== '' ? (int)$bangunan_input : null;
    $kamar_tidur   = (int)($_POST['kamar_tidur'] ?? 0);
    $kamar_mandi   = (int)($_POST['kamar_mandi'] ?? 0);
    $carport       = (int)($_POST['carport'] ?? 0);
    $gambar_url    = trim($_POST['gambar_url'] ?? '');
    $agen_id       = $agen_input !== '' ? (int)$agen_input : null;

    if (!in_array($tipe, ['rumah', 'apartemen', 'tanah', 'ruko'], true)) {
        die('Tipe properti tidak valid.');
    }
    if (!in_array($status, ['tersedia', 'terjual'], true)) {
        die('Status properti tidak valid.');
    }
    if ($lat !== null && ($lat < -90 || $lat > 90) || $lng !== null && ($lng < -180 || $lng > 180)) {
        die('Koordinat properti tidak valid.');
    }
    if ($luas_tanah !== null && $luas_tanah < 0 || $luas_bangunan !== null && $luas_bangunan < 0) {
        die('Luas properti tidak valid.');
    }
    if ($gambar_url !== '' && !filter_var($gambar_url, FILTER_VALIDATE_URL)) {
        die('URL gambar tidak valid.');
    }

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
