<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
cek_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('Agen tidak valid.');

$stmt = mysqli_prepare($koneksi, "SELECT * FROM agen WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$agen = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$agen) die('Agen tidak ditemukan.');

$user = user_login();
$page_title = 'Edit Agen - Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="page-header page-header-photo"><div class="container">
        <p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Edit Agen</h1>
        <div class="breadcrumb-estate"><a href="<?= BASE_URL ?>index.php">Beranda</a><span class="sep">/</span><a href="admin-agen.php">Kelola Agen</a><span class="sep">/</span><span class="current"><?= htmlspecialchars($agen['nama']) ?></span></div>
        <div class="admin-subnav">
            <a href="admin-dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a><a href="admin-properti.php"><i class="bi bi-houses-fill me-1"></i> Kelola Properti</a><a href="admin-transaksi.php"><i class="bi bi-receipt me-1"></i> Kelola Transaksi</a><a href="admin-agen.php" class="active"><i class="bi bi-person-badge-fill me-1"></i> Kelola Agen</a><a href="admin-pesan.php"><i class="bi bi-envelope-fill me-1"></i> Pesan Kontak</a>
        </div>
    </div></div>
    <section class="py-5"><div class="container"><div class="admin-form-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><p class="section-eyebrow mb-2">Data Agen</p><h2 class="section-title mb-0">Edit Agen</h2></div><a href="admin-agen.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
    <form method="POST" action="proses-agen.php">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id" value="<?= $agen['id'] ?>">
        <div class="row g-3"><div class="col-12 field-panel"><label class="form-label">Nama</label><input class="form-control" type="text" name="nama" value="<?= htmlspecialchars($agen['nama']) ?>" required></div><div class="col-md-6 field-panel"><label class="form-label">No. HP</label><input class="form-control" type="text" name="no_hp" value="<?= htmlspecialchars($agen['no_hp'] ?? '') ?>"></div><div class="col-md-6 field-panel"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= htmlspecialchars($agen['email'] ?? '') ?>"></div><div class="col-12 field-panel"><label class="form-label">URL Foto</label><input class="form-control" type="text" name="foto_url" value="<?= htmlspecialchars($agen['foto_url'] ?? '') ?>"></div></div>
        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-check2-circle me-1"></i> Update Agen</button></div>
    </form>
    </div></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
