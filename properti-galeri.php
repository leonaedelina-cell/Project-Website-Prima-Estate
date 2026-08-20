<?php
/**
 * pages/admin/properti-galeri.php - Estate Prima
 * Kelola galeri foto tambahan untuk 1 properti (banyak foto per properti).
 * Akses: properti-galeri.php?id=1
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$properti_id = (int)($_GET['id'] ?? 0);
if ($properti_id <= 0) {
    die('Properti tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "SELECT id, judul FROM properti WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $properti_id);
mysqli_stmt_execute($stmt);
$properti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$properti) {
    die('Properti tidak ditemukan.');
}

$stmt = mysqli_prepare($koneksi, "SELECT id, gambar_url FROM galeri_properti WHERE properti_id = ?");
mysqli_stmt_bind_param($stmt, "i", $properti_id);
mysqli_stmt_execute($stmt);
$galeri = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Galeri - Estate Prima (data test)</title>
</head>
<body>
    <p><a href="admin-properti.php">&larr; Kelola Properti</a></p>
    <h1>Galeri Foto: <?= htmlspecialchars($properti['judul']) ?></h1>

    <h3>Tambah Foto</h3>
    <form method="POST" action="proses-galeri.php">
        <input type="hidden" name="aksi" value="tambah">
        <input type="hidden" name="properti_id" value="<?= $properti_id ?>">
        <input type="text" name="gambar_url" placeholder="https://..." style="width:300px;" required>
        <button type="submit">Tambah</button>
    </form>

    <h3>Foto yang Sudah Ada (<?= count($galeri) ?>)</h3>
    <?php if (empty($galeri)): ?>
        <p>Belum ada foto galeri.</p>
    <?php else: ?>
        <?php foreach ($galeri as $g): ?>
            <div style="margin-bottom:8px;">
                <?= htmlspecialchars($g['gambar_url']) ?>
                <form method="POST" action="proses-galeri.php" style="display:inline;"
                      onsubmit="return confirm('Hapus foto ini dari galeri?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                    <input type="hidden" name="properti_id" value="<?= $properti_id ?>">
                    <button type="submit">Hapus</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
