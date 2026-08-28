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
$admin_sidebar = true;
$dashboard_sidebar_active = 'agen';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="page-header page-header-photo"><div class="container">
        <p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Edit Agen</h1>
        <div class="breadcrumb-estate"><a href="<?= BASE_URL ?>index.php">Beranda</a><span class="sep">/</span><a href="admin-agen.php">Kelola Agen</a><span class="sep">/</span><span class="current"><?= htmlspecialchars($agen['nama']) ?></span></div>
    </div></div>
    <main class="py-5"><div class="container"><div class="admin-form-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><p class="section-eyebrow mb-2">Data Agen</p><h2 class="section-title mb-0">Edit Agen</h2></div><a href="admin-agen.php" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
    <form method="POST" action="proses-agen.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id" value="<?= $agen['id'] ?>">
        <div class="row g-3"><div class="col-12 field-panel"><label class="form-label">Nama</label><input class="form-control" type="text" name="nama" value="<?= htmlspecialchars($agen['nama']) ?>" required></div><div class="col-md-6 field-panel"><label class="form-label">No. HP</label><input class="form-control" type="text" name="no_hp" value="<?= htmlspecialchars($agen['no_hp'] ?? '') ?>"></div><div class="col-md-6 field-panel"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= htmlspecialchars($agen['email'] ?? '') ?>"></div><div class="col-12 field-panel"><label class="form-label">Foto Agen (opsional)</label><input class="form-control" type="file" name="foto" accept="image/jpeg,image/png,image/webp"><small class="text-muted">Pilih foto baru jika ingin mengganti foto saat ini. Maksimal 400 KB.</small></div></div>
        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-check2-circle me-1"></i> Update Agen</button></div>
    </form>
    </div></div></main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
