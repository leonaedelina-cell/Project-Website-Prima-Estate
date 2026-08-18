<?php
/**
 * kontak.php - Estate Prima
 * Halaman contact sales: form kontak + Google Maps embed kantor.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if ($nama === '') $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if ($pesan === '') $errors[] = 'Pesan wajib diisi.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO pesan_kontak (nama, email, no_hp, pesan) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $no_hp, $pesan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $berhasil = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kontak - Estate Prima (data test)</title>
</head>
<body>
    <p><a href="index.php">&larr; Homepage</a></p>
    <h1>Hubungi Kami</h1>

    <?php if ($berhasil): ?>
        <p style="color:green;">Pesan kamu berhasil terkirim! Tim kami akan segera menghubungi kamu.</p>
    <?php endif; ?>

    <?php foreach ($errors as $e): ?>
        <p style="color:red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <form method="POST" action="kontak.php">
        <p>Nama: <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required></p>
        <p>Email: <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></p>
        <p>No. HP: <input type="text" name="no_hp" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>"></p>
        <p>Pesan:<br><textarea name="pesan" rows="4" cols="50" required><?= htmlspecialchars($_POST['pesan'] ?? '') ?></textarea></p>
        <button type="submit">Kirim Pesan</button>
    </form>

    <h3>Lokasi Kantor</h3>
    <iframe
        src="https://www.google.com/maps?q=Jakarta+Selatan&output=embed"
        style="border:0; width:100%; height:300px;"
        allowfullscreen loading="lazy">
    </iframe>
</body>
</html>