<?php
/**
 * Koneksi Database - Estate Prima
 * Pakai mysqli (procedural) untuk semua query, termasuk CRUD.
 */

// Base URL project. Dibuat dari URL halaman yang sedang dibuka supaya project
// tetap berjalan walau nama folder di Laragon/XAMPP berubah.
$base_path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
define('BASE_URL', ($base_path === '' || $base_path === '/') ? '/' : $base_path . '/');
define('WHATSAPP_ADMIN', '6281234567890');

$DB_HOST = 'localhost';
$DB_NAME = 'estate_prima';
$DB_USER = 'root';      // sesuaikan dengan user MySQL kamu (XAMPP/Laragon default: root)
$DB_PASS = '';          // sesuaikan dengan password MySQL kamu (XAMPP/Laragon default: kosong)

$koneksi = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');

function migrate_property_schema($koneksi) {
    $result = mysqli_query($koneksi, 'SHOW COLUMNS FROM properti');
    if (!$result) {
        return;
    }

    $columns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $columns[$row['Field']] = true;
    }
    mysqli_free_result($result);

    $alter_sql = [
        'tipe_transaksi' => "ALTER TABLE properti ADD COLUMN tipe_transaksi ENUM('jual','sewa') NOT NULL DEFAULT 'jual' AFTER harga",
        'durasi_minimal' => "ALTER TABLE properti ADD COLUMN durasi_minimal ENUM('6 bulan','1 tahun') DEFAULT NULL AFTER tipe_transaksi",
        'fasilitas' => "ALTER TABLE properti ADD COLUMN fasilitas TEXT DEFAULT NULL AFTER carport",
        'status_hunian' => "ALTER TABLE properti ADD COLUMN status_hunian ENUM('kosong','terisi') NOT NULL DEFAULT 'kosong' AFTER fasilitas"
    ];

    foreach ($alter_sql as $field => $sql) {
        if (!isset($columns[$field])) {
            mysqli_query($koneksi, $sql);
        }
    }
}

migrate_property_schema($koneksi);
