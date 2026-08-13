<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
cek_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('Agen tidak valid.');

$stmt = mysqli_prepare($koneksi, "SELECT * FROM agen WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$agen = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$agen) die('Agen tidak ditemukan.');
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Edit Agen - Estate Prima (data test)</title></head>
<body>
    <p><a href="admin-agen.php">&larr; Kelola Agen</a></p>
    <h1>Edit Agen: <?= htmlspecialchars($agen['nama']) ?></h1>

    <form method="POST" action="proses-agen.php">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id" value="<?= $agen['id'] ?>">
        <p>Nama: <input type="text" name="nama" value="<?= htmlspecialchars($agen['nama']) ?>" required></p>
        <p>No. HP: <input type="text" name="no_hp" value="<?= htmlspecialchars($agen['no_hp'] ?? '') ?>"></p>
        <p>Email: <input type="email" name="email" value="<?= htmlspecialchars($agen['email'] ?? '') ?>"></p>
        <p>URL Foto: <input type="text" name="foto_url" value="<?= htmlspecialchars($agen['foto_url'] ?? '') ?>"></p>
        <button type="submit">Update</button>
    </form>
</body>
</html>