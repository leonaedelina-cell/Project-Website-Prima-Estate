<?php
/**
 * pages/admin/properti-tambah.php - Estate Prima
 * Form tambah properti baru.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

// Ambil daftar agen buat dropdown
$daftar_agen = mysqli_fetch_all(mysqli_query($koneksi, "SELECT id, nama FROM agen ORDER BY nama"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Properti - Estate Prima (data test)</title>
</head>
<body>
    <p><a href="admin-properti.php">&larr; Kelola Properti</a></p>
    <h1>Tambah Properti Baru</h1>

    <form method="POST" action="proses-properti.php">
        <input type="hidden" name="aksi" value="tambah">

        <p>Judul: <input type="text" name="judul" required></p>
        <p>Deskripsi:<br><textarea name="deskripsi" rows="4" cols="50"></textarea></p>
        <p>Harga (Rp): <input type="number" name="harga" required min="0"></p>

        <p>Tipe:
            <select name="tipe" required>
                <option value="rumah">Rumah</option>
                <option value="apartemen">Apartemen</option>
                <option value="tanah">Tanah</option>
                <option value="ruko">Ruko</option>
            </select>
        </p>

        <p>Alamat: <input type="text" name="alamat" required></p>
        <p>Kota: <input type="text" name="kota" required></p>
        <p>Latitude (opsional, buat Maps): <input type="text" name="lat"></p>
        <p>Longitude (opsional, buat Maps): <input type="text" name="lng"></p>

        <p>Luas Tanah (m2): <input type="number" name="luas_tanah"></p>
        <p>Luas Bangunan (m2): <input type="number" name="luas_bangunan"></p>
        <p>Kamar Tidur: <input type="number" name="kamar_tidur" value="0"></p>
        <p>Kamar Mandi: <input type="number" name="kamar_mandi" value="0"></p>
        <p>Carport: <input type="number" name="carport" value="0"></p>

        <p>URL Gambar Utama: <input type="text" name="gambar_url" placeholder="https://..."></p>

        <p>Agen:
            <select name="agen_id">
                <option value="">- Tanpa Agen -</option>
                <?php foreach ($daftar_agen as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <button type="submit">Simpan</button>
    </form>
</body>
</html>
