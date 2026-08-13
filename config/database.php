<?php
/**
 * Koneksi Database - Estate Prima
 * Pakai mysqli (procedural) untuk semua query, termasuk CRUD.
 */

// Base URL project - dipakai biar redirect/link selalu benar dari kedalaman folder manapun
// (pages/user/, pages/admin/, dst). SESUAIKAN kalau path project kamu beda.
define('BASE_URL', '/belajar-php/9-Ujian-Project/Project-Website/');

$DB_HOST = 'localhost';
$DB_NAME = 'estate_prima';
$DB_USER = 'root';      // sesuaikan dengan user MySQL kamu (XAMPP/Laragon default: root)
$DB_PASS = '';          // sesuaikan dengan password MySQL kamu (XAMPP/Laragon default: kosong)

$koneksi = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');