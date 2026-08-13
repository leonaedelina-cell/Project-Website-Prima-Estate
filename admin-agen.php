<?php
/**
 * pages/admin/admin-agen.php - Estate Prima
 * List semua agen sales + tombol tambah/edit/hapus.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

cek_admin();

// Sekalian hitung berapa properti yang dipegang tiap agen (biar admin tau dampak sebelum hapus)
$daftar_agen = mysqli_fetch_all(mysqli_query($koneksi,
    "SELECT a.id, a.nama, a.no_hp, a.email, COUNT(p.id) AS jumlah_properti
     FROM agen a
     LEFT JOIN properti p ON p.agen_id = a.id
     GROUP BY a.id, a.nama, a.no_hp, a.email
     ORDER BY a.nama"
), MYSQLI_ASSOC);

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Agen - Estate Prima (data test)</title>
</head>
<body>
    <p>
        <a href="admin-dashboard.php">&larr; Dashboard</a> |
        <a href="admin-properti.php">Kelola Properti</a> |
        <a href="admin-transaksi.php">Kelola Transaksi</a> |
        <a href="admin-pesan.php">Pesan Kontak</a>
    </p>

    <h1>Kelola Agen Sales</h1>

    <?php if ($pesan === 'tambah-berhasil'): ?>
        <p style="color:green;">Agen berhasil ditambahkan.</p>
    <?php elseif ($pesan === 'edit-berhasil'): ?>
        <p style="color:green;">Agen berhasil diupdate.</p>
    <?php elseif ($pesan === 'hapus-berhasil'): ?>
        <p style="color:green;">Agen berhasil dihapus.</p>
    <?php endif; ?>

    <p><a href="agen-tambah.php"><button>+ Tambah Agen</button></a></p>

    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <tr><th>Nama</th><th>No. HP</th><th>Email</th><th>Jumlah Properti</th><th>Aksi</th></tr>
        <?php foreach ($daftar_agen as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['nama']) ?></td>
                <td><?= htmlspecialchars($a['no_hp'] ?? '-') ?></td>
                <td><?= htmlspecialchars($a['email'] ?? '-') ?></td>
                <td><?= $a['jumlah_properti'] ?></td>
                <td>
                    <a href="agen-edit.php?id=<?= $a['id'] ?>">Edit</a>
                    <form method="POST" action="proses-agen.php" style="display:inline;"
                          onsubmit="return confirm('<?= $a['jumlah_properti'] > 0 ? "Agen ini masih pegang {$a['jumlah_properti']} properti, propertinya akan jadi Tanpa Agen. " : '' ?>Yakin hapus agen ini?');">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <button type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>