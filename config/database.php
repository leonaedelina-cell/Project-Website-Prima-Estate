<?php
/**
 * Koneksi Database - Estate Prima
 * Pakai mysqli (procedural) untuk semua query, termasuk CRUD.
 */

// Base URL project. Dibuat dari URL halaman yang sedang dibuka supaya project
// tetap berjalan walau nama folder di Laragon/XAMPP berubah.
$base_path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
define('BASE_URL', ($base_path === '' || $base_path === '/') ? '/' : $base_path . '/');

$DB_HOST = 'localhost';
$DB_NAME = 'estate_prima';
$DB_USER = 'root';      // sesuaikan dengan user MySQL kamu (XAMPP/Laragon default: root)
$DB_PASS = '';          // sesuaikan dengan password MySQL kamu (XAMPP/Laragon default: kosong)

$koneksi = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');
