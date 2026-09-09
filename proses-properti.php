<?php
/**
 * pages/admin/proses-properti.php - Estate Prima
 * Handle 3 aksi CRUD properti: tambah, edit, hapus.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();
cek_csrf();

$aksi = $_POST['aksi'] ?? '';

function simpan_gambar_properti($file) {
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
        die('Upload gambar gagal atau ukuran gambar melebihi 2 MB.');
    }
    $info = @getimagesize($file['tmp_name']);
    $ekstensi = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!$info || !isset($ekstensi[$info['mime']])) {
        die('Format gambar harus JPG, PNG, atau WEBP.');
    }
    $folder = __DIR__ . '/assets/uploads/properti/';
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        die('Folder upload gambar tidak dapat dibuat.');
    }
    $nama = bin2hex(random_bytes(16)) . '.' . $ekstensi[$info['mime']];
    if (!move_uploaded_file($file['tmp_name'], $folder . $nama)) {
        die('Gambar gagal disimpan.');
    }
    return BASE_URL . 'assets/uploads/properti/' . $nama;
}

function hapus_gambar_upload_properti($url) {
    $path = parse_url($url ?? '', PHP_URL_PATH) ?: '';
    if (strpos($path, '/assets/uploads/properti/') === false) {
        return;
    }
    $file = __DIR__ . '/assets/uploads/properti/' . basename($path);
    if (is_file($file)) {
        unlink($file);
    }
}

if ($aksi === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = mysqli_prepare($koneksi, "DELETE FROM properti WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        die('Properti gagal dihapus.');
    }
    mysqli_stmt_close($stmt);

    header('Location: admin-properti.php?pesan=hapus-berhasil');
    exit;
}

if ($aksi === 'tambah' || $aksi === 'edit') {
    $judul          = trim($_POST['judul'] ?? '');
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $tipe_transaksi = $_POST['tipe_transaksi'] ?? 'jual';
    $durasi_minimal = $_POST['durasi_minimal'] ?? null;
    $harga          = ($_POST['harga'] ?? '') === '' ? 0 : (int)$_POST['harga'];
    $harga_sewa     = ($_POST['harga_sewa'] ?? '') === '' ? 0 : (int)$_POST['harga_sewa'];
    $periode_sewa   = trim($_POST['periode_sewa'] ?? '');
    $minimal_sewa   = max((int)($_POST['minimal_sewa'] ?? 1), 1);
    
    $tipe           = $_POST['tipe'] ?? 'rumah';
    $status         = $_POST['status'] ?? 'tersedia';
    $alamat         = trim($_POST['alamat'] ?? '');
    $kota           = trim($_POST['kota'] ?? '');
    $lat_input      = $_POST['lat'] ?? '';
    $lng_input      = $_POST['lng'] ?? '';
    $tanah_input    = $_POST['luas_tanah'] ?? '';
    $bangunan_input = $_POST['luas_bangunan'] ?? '';
    $agen_input     = $_POST['agen_id'] ?? '';
    
    $lat            = $lat_input !== '' ? (float)$lat_input : null;
    $lng            = $lng_input !== '' ? (float)$lng_input : null;
    $luas_tanah     = $tanah_input !== '' ? (int)$tanah_input : null;
    $luas_bangunan  = $bangunan_input !== '' ? (int)$bangunan_input : null;
    $kamar_tidur    = (int)($_POST['kamar_tidur'] ?? 0);
    $kamar_mandi    = (int)($_POST['kamar_mandi'] ?? 0);
    $carport        = (int)($_POST['carport'] ?? 0);
    $fasilitas      = trim($_POST['fasilitas'] ?? '');
    $status_hunian  = $_POST['status_hunian'] ?? 'kosong';
    $gambar_url     = trim($_POST['gambar_url'] ?? '');
    $gambar_lama    = trim($_POST['gambar_url_lama'] ?? '');
    $agen_id        = $agen_input !== '' ? (int)$agen_input : null;

    if (!in_array($tipe_transaksi, ['jual', 'sewa'], true)) die('Tipe transaksi tidak valid.');
    if (!in_array($tipe, ['rumah', 'apartemen', 'tanah', 'ruko'], true)) die('Tipe properti tidak valid.');
    if (!in_array($status, ['tersedia', 'terjual'], true)) die('Status properti tidak valid.');
    if (!in_array($status_hunian, ['kosong', 'terisi'], true)) die('Status hunian tidak valid.');
    if ($judul === '' || $deskripsi === '' || $alamat === '' || $kota === '' || $tipe === '' || $status_hunian === '' || $fasilitas === '') {
        die('Judul, deskripsi, alamat, kota, fasilitas, dan status hunian wajib diisi.');
    }
    if ($tanah_input === '' || $bangunan_input === '' || $luas_tanah < 0 || $luas_bangunan < 0) {
        die('Luas tanah dan luas bangunan wajib diisi dengan angka yang valid.');
    }
    if ($kamar_tidur < 0 || $kamar_mandi < 0 || $carport < 0) {
        die('Jumlah kamar dan carport tidak boleh bernilai negatif.');
    }

    if ($tipe_transaksi === 'jual') {
        $durasi_minimal = null;
        $harga_sewa = 0;
        $periode_sewa = null;
        $minimal_sewa = 1;
        if ($harga <= 0) die('Harga jual wajib diisi dan harus lebih dari 0.');
    } else {
        if ($harga_sewa <= 0) die('Harga sewa wajib diisi dan harus lebih dari 0.');
        if (!in_array($durasi_minimal, ['6 bulan', '1 tahun'], true)) die('Durasi minimal sewa tidak valid.');
        if ($periode_sewa === '' || !in_array($periode_sewa, ['bulan', 'tahun'], true)) die('Periode sewa wajib dipilih.');
        if ($minimal_sewa <= 0) die('Minimal sewa wajib diisi.');
        $minimal_sewa = $durasi_minimal === '1 tahun' ? 12 : 6;
        $harga = 0;
    }

    $gambar_baru = simpan_gambar_properti($_FILES['gambar_file'] ?? null);
    if ($gambar_baru !== null) {
        $gambar_url = $gambar_baru;
    } elseif ($aksi === 'edit' && $gambar_url === '') {
        $gambar_url = $gambar_lama;
    }

    if (($aksi === 'tambah' && !$gambar_baru && $gambar_url === '') || ($aksi === 'edit' && $gambar_url === '' && $gambar_lama === '')) {
        die('Gambar utama wajib diisi.');
    }

    if ($aksi === 'tambah') {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO properti
                (judul, deskripsi, tipe_transaksi, durasi_minimal, harga, harga_sewa, periode_sewa, minimal_sewa, tipe, alamat, kota, lat, lng,
                 luas_tanah, luas_bangunan, kamar_tidur, kamar_mandi, carport, fasilitas, status_hunian, gambar_url, agen_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt, "ssssddsisssddiiiiisssi",
            $judul, $deskripsi, $tipe_transaksi, $durasi_minimal, $harga, $harga_sewa, $periode_sewa, $minimal_sewa, $tipe, $alamat, $kota, $lat, $lng,
            $luas_tanah, $luas_bangunan, $kamar_tidur, $kamar_mandi, $carport, $fasilitas, $status_hunian, $gambar_url, $agen_id
        );
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            if ($gambar_baru !== null) {
                hapus_gambar_upload_properti($gambar_baru);
            }
            die('Properti gagal disimpan.');
        }
        mysqli_stmt_close($stmt);

        header('Location: admin-properti.php?pesan=tambah-berhasil');
        exit;
    }

    if ($aksi === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) die('ID properti tidak valid.');
        $old_stmt = mysqli_prepare($koneksi, "SELECT gambar_url FROM properti WHERE id = ?");
        mysqli_stmt_bind_param($old_stmt, "i", $id);
        mysqli_stmt_execute($old_stmt);
        $old_property = mysqli_fetch_assoc(mysqli_stmt_get_result($old_stmt));
        mysqli_stmt_close($old_stmt);
        if (!$old_property) die('Properti tidak ditemukan.');
        $gambar_lama = $old_property['gambar_url'] ?? '';

        $stmt = mysqli_prepare($koneksi,
            "UPDATE properti SET
                judul = ?, deskripsi = ?, tipe_transaksi = ?, durasi_minimal = ?, harga = ?, harga_sewa = ?, periode_sewa = ?, minimal_sewa = ?,
                tipe = ?, status = ?, alamat = ?, kota = ?, lat = ?, lng = ?, luas_tanah = ?, luas_bangunan = ?, 
                kamar_tidur = ?, kamar_mandi = ?, carport = ?, fasilitas = ?, status_hunian = ?, gambar_url = ?, agen_id = ?
             WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt, "ssssddsissssddiiiiisssii",
            $judul, $deskripsi, $tipe_transaksi, $durasi_minimal, $harga, $harga_sewa, $periode_sewa, $minimal_sewa, $tipe, $status, $alamat, $kota, $lat, $lng,
            $luas_tanah, $luas_bangunan, $kamar_tidur, $kamar_mandi, $carport, $fasilitas, $status_hunian, $gambar_url, $agen_id, $id
        );
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            if ($gambar_baru !== null) {
                hapus_gambar_upload_properti($gambar_baru);
            }
            die('Properti gagal diperbarui.');
        }
        mysqli_stmt_close($stmt);
        if ($gambar_baru !== null && $gambar_lama !== '') {
            hapus_gambar_upload_properti($gambar_lama);
        }

        header('Location: admin-properti.php?pesan=edit-berhasil');
        exit;
    }
}

die('Aksi tidak dikenali.');