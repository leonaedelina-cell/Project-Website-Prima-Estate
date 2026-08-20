<?php
/**
 * login.php - Estate Prima
 * Menangani submit form login (dari login.html nanti).
 * Field form yang diharapkan: email, password
 */

session_start();
require_once __DIR__ . '/config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    }

    $user = null;

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id, nama, email, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $user  = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        // Penting: pesan error digeneralisasi ("email/password salah"), jangan spesifik
        // "email tidak ditemukan" -- supaya orang jahat gak bisa nebak email mana yang terdaftar.
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        }
    }

    if (empty($errors)) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        // Redirect beda tujuan tergantung role
        if ($user['role'] === 'admin') {
            header('Location: ' . BASE_URL . 'admin-dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'index.php');
        }
        exit;
    }
}

// Kalau sampai sini berarti ada $errors atau method GET (baru buka halaman)
// -> nanti di sinilah tempat nge-include tampilan login.html / render pesan error
?>
