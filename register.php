<?php
/**
 * register.php - Estate Prima
 * Menangani submit form register (dari register.html nanti).
 * Field form yang diharapkan: nama, email, password, konfirmasi_password, no_hp
 */

session_start();
require_once __DIR__ . '/config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Ambil & bersihkan input
    $nama                = trim($_POST['nama'] ?? '');
    $email               = trim($_POST['email'] ?? '');
    $password            = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
    $no_hp               = trim($_POST['no_hp'] ?? '');

    // 2. Validasi
    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirmasi_password) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // 3. Cek email sudah dipakai atau belum (pakai prepared statement mysqli)
    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($hasil)) {
            $errors[] = 'Email sudah terdaftar, silakan pakai email lain atau login.';
        }
        mysqli_stmt_close($stmt);
    }

    // 4. Simpan kalau lolos validasi
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, 'user')"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $password_hash, $no_hp);
        mysqli_stmt_execute($stmt);

        // Langsung login-kan user setelah register (opsional, tapi umum dipakai)
        $_SESSION['user_id'] = mysqli_insert_id($koneksi);
        $_SESSION['nama']    = $nama;
        $_SESSION['email']   = $email;
        $_SESSION['role']    = 'user';

        mysqli_stmt_close($stmt);

        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Kalau sampai sini berarti ada $errors atau method GET (baru buka halaman)
// -> nanti di sinilah tempat nge-include tampilan register.html / render pesan error
?>