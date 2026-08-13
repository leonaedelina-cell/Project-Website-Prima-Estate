<?php
/**
 * pages/admin/admin-pesan.php - Estate Prima
 * Lihat semua pesan masuk dari form kontak.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

cek_admin();

$daftar_pesan = mysqli_fetch_all(mysqli_query($koneksi,
    "SELECT * FROM pesan_kontak ORDER BY created_at DESC"
), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesan Kontak - Estate Prima (data test)</title>
</head>
<body>
    <p>
        <a href="admin-dashboard.php">&larr; Dashboard</a> |
        <a href="admin-properti.php">Kelola Properti</a> |
        <a href="admin-transaksi.php">Kelola Transaksi</a> |
        <a href="admin-agen.php">Kelola Agen</a>
    </p>

    <h1>Pesan Kontak Masuk</h1>

    <?php if (empty($daftar_pesan)): ?>
        <p>Belum ada pesan masuk.</p>
    <?php else: ?>
        <?php foreach ($daftar_pesan as $p): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px; <?= $p['status_dibaca'] === 'belum' ? 'background:#fffbe6;' : '' ?>">
                <p>
                    <b><?= htmlspecialchars($p['nama']) ?></b>
                    (<?= htmlspecialchars($p['email']) ?><?= $p['no_hp'] ? ', ' . htmlspecialchars($p['no_hp']) : '' ?>)
                    — <?= $p['created_at'] ?>
                    <?= $p['status_dibaca'] === 'belum' ? ' <b style="color:orange;">[BELUM DIBACA]</b>' : '' ?>
                </p>
                <p><?= nl2br(htmlspecialchars($p['pesan'])) ?></p>

                <?php if ($p['status_dibaca'] === 'belum'): ?>
                    <form method="POST" action="proses-pesan.php" style="display:inline;">
                        <input type="hidden" name="aksi" value="tandai-dibaca">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit">Tandai Dibaca</button>
                    </form>
                <?php endif; ?>

                <form method="POST" action="proses-pesan.php" style="display:inline;"
                      onsubmit="return confirm('Hapus pesan ini?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>