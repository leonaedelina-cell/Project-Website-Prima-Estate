<?php
/**
 * pages/admin/properti-edit.php - Estate Prima
 * Form edit properti yang sudah ada. Akses: properti-edit.php?id=1
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

cek_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Properti tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM properti WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$properti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$properti) {
    die('Properti tidak ditemukan.');
}

$daftar_agen = mysqli_fetch_all(mysqli_query($koneksi, "SELECT id, nama FROM agen ORDER BY nama"), MYSQLI_ASSOC);

// Helper kecil biar gak nulis berkali-kali: cek apakah suatu <option> harus 'selected'
function is_selected($a, $b) {
    return $a == $b ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Properti - Estate Prima (data test)</title>
</head>
<body>
    <p><a href="admin-properti.php">&larr; Kelola Properti</a></p>
    <h1>Edit Properti: <?= htmlspecialchars($properti['judul']) ?></h1>

    <form method="POST" action="proses-properti.php">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id" value="<?= $properti['id'] ?>">

        <p>Judul: <input type="text" name="judul" value="<?= htmlspecialchars($properti['judul']) ?>" required></p>
        <p>Deskripsi:<br><textarea name="deskripsi" rows="4" cols="50"><?= htmlspecialchars($properti['deskripsi']) ?></textarea></p>
        <p>Harga (Rp): <input type="number" name="harga" value="<?= $properti['harga'] ?>" required min="0"></p>

        <p>Tipe:
            <select name="tipe" required>
                <?php foreach (['rumah', 'apartemen', 'tanah', 'ruko'] as $t): ?>
                    <option value="<?= $t ?>" <?= is_selected($properti['tipe'], $t) ?>><?= ucfirst($t) ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>Status:
            <select name="status" required>
                <option value="tersedia" <?= is_selected($properti['status'], 'tersedia') ?>>Tersedia</option>
                <option value="terjual" <?= is_selected($properti['status'], 'terjual') ?>>Terjual</option>
            </select>
        </p>

        <p>Alamat: <input type="text" name="alamat" value="<?= htmlspecialchars($properti['alamat']) ?>" required></p>
        <p>Kota: <input type="text" name="kota" value="<?= htmlspecialchars($properti['kota']) ?>" required></p>
        <p>Latitude: <input type="text" name="lat" value="<?= htmlspecialchars($properti['lat'] ?? '') ?>"></p>
        <p>Longitude: <input type="text" name="lng" value="<?= htmlspecialchars($properti['lng'] ?? '') ?>"></p>

        <p>Luas Tanah (m2): <input type="number" name="luas_tanah" value="<?= $properti['luas_tanah'] ?>"></p>
        <p>Luas Bangunan (m2): <input type="number" name="luas_bangunan" value="<?= $properti['luas_bangunan'] ?>"></p>
        <p>Kamar Tidur: <input type="number" name="kamar_tidur" value="<?= $properti['kamar_tidur'] ?>"></p>
        <p>Kamar Mandi: <input type="number" name="kamar_mandi" value="<?= $properti['kamar_mandi'] ?>"></p>
        <p>Carport: <input type="number" name="carport" value="<?= $properti['carport'] ?>"></p>

        <p>URL Gambar Utama: <input type="text" name="gambar_url" value="<?= htmlspecialchars($properti['gambar_url'] ?? '') ?>"></p>

        <p>Agen:
            <select name="agen_id">
                <option value="">- Tanpa Agen -</option>
                <?php foreach ($daftar_agen as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= is_selected($properti['agen_id'], $a['id']) ?>>
                        <?= htmlspecialchars($a['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <button type="submit">Update</button>
    </form>
</body>
</html>