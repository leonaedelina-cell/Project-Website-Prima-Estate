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

    $judul          = trim($_POST['judul'] ?? '');
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $harga          = (int)($_POST['harga'] ?? 0);
    $tipe           = $_POST['tipe'] ?? 'rumah';
    $tipe_transaksi = $_POST['tipe_transaksi'] ?? 'jual';
    $durasi_minimal_input = $_POST['durasi_minimal'] ?? '';
    $fasilitas      = trim($_POST['fasilitas'] ?? '');
    $status_hunian  = $_POST['status_hunian'] ?? 'kosong';
    $status         = $_POST['status'] ?? 'tersedia'; // cuma dipakai saat edit, saat tambah selalu default 'tersedia'
    $alamat         = trim($_POST['alamat'] ?? '');
    $kota           = trim($_POST['kota'] ?? '');
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
    if (!in_array($tipe_transaksi, ['jual', 'sewa'], true)) {
        die('Tipe transaksi tidak valid.');
    }
    // durasi_minimal cuma relevan buat sewa; kalau jual, paksa NULL biar gak nyampah di data
    $durasi_minimal = null;
    if ($tipe_transaksi === 'sewa') {
        if (!in_array($durasi_minimal_input, ['6 bulan', '1 tahun'], true)) {
            die('Durasi minimal sewa tidak valid.');
        }
        $durasi_minimal = $durasi_minimal_input;
    }
    if (!in_array($status_hunian, ['kosong', 'terisi'], true)) {
        die('Status hunian tidak valid.');
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

    // Kalau edit, ambil dulu gambar_url lama - dipakai buat cleanup file lama setelah upload baru sukses
    $gambar_lama = '';
    if ($aksi === 'edit') {
        $id_cek = (int)($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($koneksi, "SELECT gambar_url FROM properti WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_cek);
        mysqli_stmt_execute($stmt);
        $data_lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        $gambar_lama = $data_lama['gambar_url'] ?? '';
    }

    // Upload gambar utama (opsional) - kalau ada file baru, dipakai; kalau enggak, tetep pakai gambar_url yang diisi manual
    $gambar_baru = $_FILES['gambar'] ?? null;
    if ($gambar_baru && $gambar_baru['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($gambar_baru['error'] !== UPLOAD_ERR_OK) {
            die('Upload gambar properti gagal.');
        }
        if ($gambar_baru['size'] > 2 * 1024 * 1024) {
            die('Ukuran gambar maksimal 2 MB.');
        }
        $info_gambar = @getimagesize($gambar_baru['tmp_name']);
        $tipe_gambar = $info_gambar['mime'] ?? '';
        $tipe_diizinkan = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$info_gambar || !isset($tipe_diizinkan[$tipe_gambar])) {
            die('Format gambar harus JPG, PNG, atau WEBP.');
        }
        $nama_file = bin2hex(random_bytes(16)) . '.' . $tipe_diizinkan[$tipe_gambar];
        $folder_upload = __DIR__ . '/assets/uploads/properti/';
        if (!is_dir($folder_upload) && !mkdir($folder_upload, 0755, true)) {
            die('Folder upload gambar tidak dapat dibuat.');
        }
        if (!move_uploaded_file($gambar_baru['tmp_name'], $folder_upload . $nama_file)) {
            die('Gambar gagal disimpan.');
        }
        $gambar_url = BASE_URL . 'assets/uploads/properti/' . $nama_file;
    }

    if ($aksi === 'tambah') {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO properti
                (judul, deskripsi, harga, tipe, tipe_transaksi, durasi_minimal, fasilitas, status_hunian,
                 alamat, kota, lat, lng, luas_tanah, luas_bangunan, kamar_tidur, kamar_mandi, carport, gambar_url, agen_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt, "ssisssssssddiiiiisi",
            $judul, $deskripsi, $harga, $tipe, $tipe_transaksi, $durasi_minimal, $fasilitas, $status_hunian,
            $alamat, $kota, $lat, $lng, $luas_tanah, $luas_bangunan, $kamar_tidur, $kamar_mandi, $carport, $gambar_url, $agen_id
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
                judul = ?, deskripsi = ?, harga = ?, tipe = ?, tipe_transaksi = ?, durasi_minimal = ?,
                fasilitas = ?, status_hunian = ?, status = ?, alamat = ?, kota = ?,
                lat = ?, lng = ?, luas_tanah = ?, luas_bangunan = ?, kamar_tidur = ?, kamar_mandi = ?,
                carport = ?, gambar_url = ?, agen_id = ?
             WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt, "ssissssssssddiiiiisii",
            $judul, $deskripsi, $harga, $tipe, $tipe_transaksi, $durasi_minimal, $fasilitas, $status_hunian, $status,
            $alamat, $kota, $lat, $lng, $luas_tanah, $luas_bangunan, $kamar_tidur, $kamar_mandi, $carport, $gambar_url, $agen_id, $id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Kalau ada upload gambar baru & gambar lama itu file upload kita sendiri (bukan link eksternal), hapus file lamanya
        if ($gambar_baru && $gambar_baru['error'] === UPLOAD_ERR_OK && $gambar_lama !== '') {
            $path_lama = parse_url($gambar_lama, PHP_URL_PATH);
            if ($path_lama && strpos($path_lama, '/assets/uploads/properti/') !== false) {
                $file_lama = __DIR__ . '/assets/uploads/properti/' . basename($path_lama);
                if (is_file($file_lama)) {
                    unlink($file_lama);
                }
            }
        }

        header('Location: admin-properti.php?pesan=edit-berhasil');
        exit;
    }
}

// Kalau $aksi gak dikenali sama sekali
die('Aksi tidak dikenali.');
