<?php
/**
 * pages/admin/admin-properti.php - Estate Prima
 * Tabel semua properti + modal konfirmasi hapus (versi sederhana: confirm() JS)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

cek_admin();

$daftar_properti = mysqli_fetch_all(mysqli_query($koneksi,
    "SELECT id, judul, harga, tipe, kota, status FROM properti ORDER BY created_at DESC"
), MYSQLI_ASSOC);

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Properti - Estate Prima (data test)</title>
</head>
<body>
    <p>
        <a href="admin-dashboard.php">&larr; Dashboard</a> |
        <a href="admin-transaksi.php">Kelola Transaksi</a>
    </p>

    <h1>Kelola Properti</h1>

    <?php if ($pesan === 'tambah-berhasil'): ?>
        <p style="color:green;">Properti berhasil ditambahkan.</p>
    <?php elseif ($pesan === 'edit-berhasil'): ?>
        <p style="color:green;">Properti berhasil diupdate.</p>
    <?php elseif ($pesan === 'hapus-berhasil'): ?>
        <p style="color:green;">Properti berhasil dihapus.</p>
    <?php endif; ?>

    <p><a href="properti-tambah.php"><button>+ Tambah Properti</button></a></p>

    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <tr>
            <th>Judul</th><th>Harga</th><th>Tipe</th><th>Kota</th><th>Status</th><th>Aksi</th>
        </tr>
        <?php foreach ($daftar_properti as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['judul']) ?></td>
                <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                <td><?= ucfirst($p['tipe']) ?></td>
                <td><?= htmlspecialchars($p['kota']) ?></td>
                <td><?= ucfirst($p['status']) ?></td>
                <td>
                    <a href="properti-edit.php?id=<?= $p['id'] ?>">Edit</a> |
                    <a href="properti-galeri.php?id=<?= $p['id'] ?>">Galeri</a>

                    <!-- Konfirmasi hapus pakai confirm() JS bawaan browser -->
                    <form method="POST" action="proses-properti.php" style="display:inline;"
                          onsubmit="return confirm('Yakin mau hapus properti ini? Data wishlist & transaksi terkait ikut terhapus.');">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>