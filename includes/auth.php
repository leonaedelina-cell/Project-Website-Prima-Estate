<?php
/**
 * Auth Helper - Estate Prima
 * Include file ini di paling atas halaman yang butuh proteksi login.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login (role apapun).
 * Kalau belum, redirect ke login.php.
 */
function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

/**
 * Cek apakah yang login adalah admin.
 * Kalau bukan admin (atau belum login sama sekali), tolak akses.
 */
function cek_admin() {
    cek_login();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * Ambil data user yang sedang login, dalam bentuk array asosiatif.
 * Return null kalau belum login.
 */
function user_login() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'nama'  => $_SESSION['nama'],
        'email' => $_SESSION['email'],
        'role'  => $_SESSION['role'],
    ];
}