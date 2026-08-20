<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
cek_admin();
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Tambah Agen - Estate Prima (data test)</title></head>
<body>
    <p><a href="admin-agen.php">&larr; Kelola Agen</a></p>
    <h1>Tambah Agen Baru</h1>

    <form method="POST" action="proses-agen.php">
        <input type="hidden" name="aksi" value="tambah">
        <p>Nama: <input type="text" name="nama" required></p>
        <p>No. HP: <input type="text" name="no_hp"></p>
        <p>Email: <input type="email" name="email"></p>
        <p>URL Foto (opsional): <input type="text" name="foto_url" placeholder="https://..."></p>
        <button type="submit">Simpan</button>
    </form>
</body>
</html>
