<?php
/**
 * admin-transaksi-detail.php - Estate Prima
 * Detail dan Manajemen Transaksi oleh Admin
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';
$error = '';

function simpan_bukti_bayar($file) {
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) die('Bukti pembayaran maksimal 2 MB.');
    $info = @getimagesize($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!$info || !isset($allowed[$info['mime']])) die('Bukti pembayaran harus JPG, PNG, atau WEBP.');
    $folder = __DIR__ . '/assets/uploads/bukti-bayar/';
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) die('Folder upload bukti tidak dapat dibuat.');
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$info['mime']];
    if (!move_uploaded_file($file['tmp_name'], $folder . $name)) die('Bukti pembayaran gagal disimpan.');
    return BASE_URL . 'assets/uploads/bukti-bayar/' . $name;
}

function hapus_bukti_upload($url) {
    $path = parse_url($url ?? '', PHP_URL_PATH) ?: '';
    if (strpos($path, '/assets/uploads/bukti-bayar/') === false) return;
    $file = __DIR__ . '/assets/uploads/bukti-bayar/' . basename($path);
    if (is_file($file)) unlink($file);
}

// Update status & detail transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $status        = $_POST['status'] ?? 'menunggu';
    $metode_bayar  = !empty($_POST['metode_bayar']) ? $_POST['metode_bayar'] : NULL;
    $bukti_bayar   = trim($_POST['bukti_bayar'] ?? '');
    $catatan_admin = $_POST['catatan_admin'] ?? '';

    $old_stmt = mysqli_prepare($koneksi, "SELECT bukti_bayar, properti_id, tipe_transaksi FROM transaksi WHERE id = ?");
    mysqli_stmt_bind_param($old_stmt, "i", $id); mysqli_stmt_execute($old_stmt);
    $old_data = mysqli_fetch_assoc(mysqli_stmt_get_result($old_stmt)); mysqli_stmt_close($old_stmt);
    if (!$old_data) die('Transaksi tidak ditemukan.');
    $new_upload = simpan_bukti_bayar($_FILES['bukti_file'] ?? null);
    if ($new_upload !== null) $bukti_bayar = $new_upload;
    elseif ($bukti_bayar === '') $bukti_bayar = $old_data['bukti_bayar'] ?? '';
    $status_valid = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai', 'lunas'];
    if (!in_array($status, $status_valid, true)) die('Status transaksi tidak valid.');
    $metode_valid = ['', 'transfer_bank', 'cicilan_kpr', 'tunai', 'e-wallet', 'qris'];
    if (!in_array($metode_bayar ?? '', $metode_valid, true)) die('Metode pembayaran tidak valid.');
    $bukti_bayar = $bukti_bayar !== '' ? $bukti_bayar : null;
    $stmt = mysqli_prepare($koneksi, "UPDATE transaksi SET status = ?, metode_bayar = ?, bukti_bayar = ?, catatan_admin = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $status, $metode_bayar, $bukti_bayar, $catatan_admin, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $msg = "Transaksi berhasil diperbarui.";
        if ($new_upload !== null && !empty($old_data['bukti_bayar'])) hapus_bukti_upload($old_data['bukti_bayar']);
        if ($old_data['tipe_transaksi'] === 'jual' && in_array($status, ['disetujui', 'selesai', 'lunas', 'ditolak'], true)) {
            $property_status = $status === 'ditolak' ? 'tersedia' : 'terjual';
            $property_stmt = mysqli_prepare($koneksi, "UPDATE properti SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($property_stmt, "si", $property_status, $old_data['properti_id']);
            mysqli_stmt_execute($property_stmt);
            mysqli_stmt_close($property_stmt);
        }
    } else {
        if ($new_upload !== null) hapus_bukti_upload($new_upload);
        $error = "Gagal memperbarui transaksi: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt);
}

// Fetch detail transaksi
$stmt = mysqli_prepare($koneksi, "SELECT t.*, u.nama AS nama_user, u.email, u.no_hp, p.judul AS nama_properti, p.harga AS harga_jual, p.harga_sewa, p.periode_sewa 
                                 FROM transaksi t 
                                 JOIN users u ON t.user_id = u.id 
                                 JOIN properti p ON t.properti_id = p.id 
                                 WHERE t.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: admin-transaksi.php");
    exit;
}

$page_title = "Detail Transaksi #{$data['id']} — Admin Estate Prima";
$user = user_login();
$admin_sidebar = true;
$dashboard_sidebar_active = 'transaksi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header"><div class="container"><p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Detail Transaksi</h1><p class="lead mb-0">Periksa data pengajuan dan perbarui pembayaran atau status transaksi.</p></div></div>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold mb-0">Detail Transaksi #<?= $data['id'] ?></h2>
        <a href="admin-transaksi.php" class="btn btn-outline-navy btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <?php if (in_array($data['status'], ['selesai', 'lunas'], true)): ?>
            <button type="button" class="btn btn-gold btn-sm" data-print-receipt="<?= $data['id'] ?>"><i class="bi bi-printer me-1"></i> Cetak Bukti</button>
        <?php endif; ?>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Info Customer & Properti -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-navy mb-3"><i class="bi bi-person-fill me-2"></i>Informasi Pemohon</h5>
                    <p class="mb-2"><strong>Nama:</strong> <?= htmlspecialchars($data['nama_user']) ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
                    <p class="mb-0"><strong>No HP:</strong> <?= htmlspecialchars($data['no_hp'] ?? '-') ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-navy mb-3"><i class="bi bi-house-door-fill me-2"></i>Detail Pengajuan Properti</h5>
                    <p class="mb-2"><strong>Properti:</strong> <?= htmlspecialchars($data['nama_properti']) ?></p>
                    <p class="mb-2">
                        <strong>Tipe Transaksi:</strong> 
                        <span class="badge bg-<?= $data['tipe_transaksi'] === 'sewa' ? 'warning text-dark' : 'success' ?>">
                            <?= strtoupper($data['tipe_transaksi']) ?>
                        </span>
                    </p>

                    <?php if ($data['tipe_transaksi'] === 'sewa'): ?>
                        <p class="mb-2"><strong>Durasi Sewa:</strong> <?= $data['durasi_sewa'] ?> <?= htmlspecialchars($data['periode_sewa'] ?? 'bulan') ?></p>
                        <p class="mb-2">
                            <strong>Periode Sewa:</strong> 
                            <?= $data['tanggal_mulai'] ? date('d M Y', strtotime($data['tanggal_mulai'])) : '-' ?> 
                            s.d 
                            <?= $data['tanggal_selesai'] ? date('d M Y', strtotime($data['tanggal_selesai'])) : '-' ?>
                        </p>
                    <?php endif; ?>

                    <div class="p-3 bg-light rounded mt-3">
                        <small class="text-muted d-block">Total Nilai Transaksi:</small>
                        <span class="fs-4 fw-bold text-success">
                            Rp <?= number_format($data['total_harga'] ?? $data['harga_jual'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Update Admin -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-navy mb-3"><i class="bi bi-pencil-square me-2"></i>Update Status Transaksi</h5>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <div class="mb-3">
                            <label class="form-label font-semibold">Metode Pembayaran</label>
                            <select name="metode_bayar" class="form-select">
                                <option value="">-- Pilih Metode --</option>
                                <option value="transfer_bank" <?= $data['metode_bayar'] == 'transfer_bank' ? 'selected' : '' ?>>Transfer Bank</option>
                                <option value="tunai" <?= $data['metode_bayar'] == 'tunai' ? 'selected' : '' ?>>Tunai</option>
                                <option value="cicilan_kpr" <?= $data['metode_bayar'] == 'cicilan_kpr' ? 'selected' : '' ?>>Cicilan KPR</option>
                                <option value="e-wallet" <?= $data['metode_bayar'] == 'e-wallet' ? 'selected' : '' ?>>E-Wallet</option>
                                <option value="qris" <?= $data['metode_bayar'] == 'qris' ? 'selected' : '' ?>>QRIS</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Upload Bukti Pembayaran</label>
                            <input type="file" name="bukti_file" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                            <input type="url" name="bukti_bayar" class="form-control mt-2" value="<?= htmlspecialchars($data['bukti_bayar'] ?? '') ?>" placeholder="Atau URL https://...">
                            <?php if (!empty($data['bukti_bayar'])): ?>
                                <small class="mt-1 d-block"><a href="<?= htmlspecialchars($data['bukti_bayar']) ?>" target="_blank" rel="noopener">Lihat Bukti Pembayaran <i class="bi bi-box-arrow-up-right"></i></a></small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Status Transaksi</label>
                            <select name="status" class="form-select">
                                <option value="menunggu" <?= $data['status'] == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                <option value="diproses" <?= $data['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                <option value="disetujui" <?= $data['status'] == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                <option value="selesai" <?= $data['status'] == 'selesai' ? 'selected' : '' ?>>Selesai / Lunas</option>
                                <option value="lunas" <?= $data['status'] == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                <option value="ditolak" <?= $data['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Catatan Admin</label>
                            <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Tambahkan instruksi pembayaran atau alasan penolakan..."><?= htmlspecialchars($data['catatan_admin'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-gold w-100 py-2 fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="print-receipt" id="print-receipt-<?= $data['id'] ?>">
    <div class="receipt-head"><div><h1>ESTATE PRIMA</h1><p>Property & Lifestyle</p></div><div class="receipt-number">BUKTI TRANSAKSI<br><strong>#TRX<?= str_pad($data['id'], 4, '0', STR_PAD_LEFT) ?></strong></div></div>
    <div class="receipt-status">LUNAS / SELESAI</div>
    <div class="receipt-grid"><div><small>PEMOHON</small><strong><?= htmlspecialchars($data['nama_user']) ?></strong><span><?= htmlspecialchars($data['email']) ?></span></div><div><small>PROPERTI</small><strong><?= htmlspecialchars($data['nama_properti']) ?></strong><span><?= ucfirst($data['tipe_transaksi']) ?></span></div></div>
    <div class="receipt-total"><small>TOTAL NILAI TRANSAKSI</small><strong>Rp <?= number_format($data['total_harga'] ?? $data['harga_jual'], 0, ',', '.') ?></strong></div>
    <?php if (!empty($data['catatan_admin'])): ?><p class="receipt-note"><strong>Catatan:</strong> <?= htmlspecialchars($data['catatan_admin']) ?></p><?php endif; ?>
    <div class="receipt-sign">Terima kasih telah mempercayakan kebutuhan properti Anda kepada Estate Prima.<br><strong>Admin Estate Prima</strong></div>
</div>

<script src="<?= BASE_URL ?>assets/js/print-receipt.js?v=<?= filemtime(__DIR__ . '/assets/js/print-receipt.js') ?>"></script>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>