<?php
/**
 * admin-transaksi-detail.php - Estate Prima
 * Detail dan pengelolaan status transaksi oleh admin.
 * Akses: admin-transaksi-detail.php?id=1
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    die('Transaksi tidak valid.');
}

$status_valid = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai'];
$metode_valid = ['transfer_bank', 'cicilan_kpr', 'tunai'];
$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $metode_bayar = $_POST['metode_bayar'] ?? '';
    $bukti_bayar = trim($_POST['bukti_bayar'] ?? '');
    $status = $_POST['status'] ?? '';
    $catatan_admin = trim($_POST['catatan_admin'] ?? '');

    // Upload file bukti bayar (opsional) - kalau ada file baru, dipakai; kalau enggak, tetep pakai URL yang diisi manual
    $bukti_baru = $_FILES['bukti_bayar_file'] ?? null;
    if ($bukti_baru && $bukti_baru['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($bukti_baru['error'] !== UPLOAD_ERR_OK) {
            $pesan_error = 'Upload bukti pembayaran gagal.';
        } elseif ($bukti_baru['size'] > 2 * 1024 * 1024) {
            $pesan_error = 'Ukuran bukti pembayaran maksimal 2 MB.';
        } else {
            $info_bukti = @getimagesize($bukti_baru['tmp_name']);
            $tipe_bukti = $info_bukti['mime'] ?? '';
            $tipe_diizinkan = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!$info_bukti || !isset($tipe_diizinkan[$tipe_bukti])) {
                $pesan_error = 'Format bukti pembayaran harus JPG, PNG, atau WEBP.';
            } else {
                $nama_file = bin2hex(random_bytes(16)) . '.' . $tipe_diizinkan[$tipe_bukti];
                $folder_upload = __DIR__ . '/assets/uploads/bukti-bayar/';
                if (!is_dir($folder_upload) && !mkdir($folder_upload, 0755, true)) {
                    $pesan_error = 'Folder upload bukti pembayaran tidak dapat dibuat.';
                } elseif (!move_uploaded_file($bukti_baru['tmp_name'], $folder_upload . $nama_file)) {
                    $pesan_error = 'Bukti pembayaran gagal disimpan.';
                } else {
                    $bukti_bayar = BASE_URL . 'assets/uploads/bukti-bayar/' . $nama_file;
                }
            }
        }
    }

    if ($pesan_error !== '') {
        // upload bukti bayar gagal, lewati proses update
    } elseif ($status === '' || !in_array($status, $status_valid, true)) {
        $pesan_error = 'Status transaksi tidak valid.';
    } elseif ($metode_bayar !== '' && !in_array($metode_bayar, $metode_valid, true)) {
        $pesan_error = 'Metode pembayaran tidak valid.';
    } else {
        $metode_bayar_db = $metode_bayar !== '' ? $metode_bayar : null;
        $bukti_bayar_db = $bukti_bayar !== '' ? $bukti_bayar : null;

        mysqli_begin_transaction($koneksi);
        try {
            $stmt = mysqli_prepare($koneksi, "SELECT properti_id, status FROM transaksi WHERE id = ? FOR UPDATE");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $transaksi_ref = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if (!$transaksi_ref) {
                throw new RuntimeException('Transaksi tidak ditemukan.');
            }

            if (in_array($status, ['disetujui', 'selesai'], true)) {
                $stmt = mysqli_prepare($koneksi, "SELECT status FROM properti WHERE id = ? FOR UPDATE");
                mysqli_stmt_bind_param($stmt, 'i', $transaksi_ref['properti_id']);
                mysqli_stmt_execute($stmt);
                $properti_ref = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);
                if (!$properti_ref || ($properti_ref['status'] === 'terjual' && !in_array($transaksi_ref['status'], ['disetujui', 'selesai'], true))) {
                    throw new RuntimeException('Properti sudah terjual dan tidak dapat disetujui lagi.');
                }
            }

            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE transaksi
                 SET metode_bayar = ?, bukti_bayar = ?, status = ?, catatan_admin = ?
                 WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, 'ssssi', $metode_bayar_db, $bukti_bayar_db, $status, $catatan_admin, $id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new RuntimeException('Perubahan transaksi gagal disimpan.');
            }
            mysqli_stmt_close($stmt);

            if (in_array($status, ['disetujui', 'selesai'], true)) {
                $status_properti = 'terjual';
            } elseif ($status === 'ditolak') {
                $status_properti = 'tersedia';
            } else {
                $status_properti = null;
            }

            if ($status_properti !== null) {
                $stmt = mysqli_prepare($koneksi, "UPDATE properti SET status = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $status_properti, $transaksi_ref['properti_id']);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new RuntimeException('Status properti gagal diperbarui.');
                }
                mysqli_stmt_close($stmt);
            }

            mysqli_commit($koneksi);
            $pesan_sukses = 'Perubahan transaksi berhasil disimpan.';
        } catch (Throwable $error) {
            mysqli_rollback($koneksi);
            $pesan_error = $error->getMessage();
        }
    }
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT t.id, t.metode_bayar, t.bukti_bayar, t.status, t.catatan_admin,
            t.created_at, t.updated_at,
            u.nama AS nama_user, u.email AS email_user, u.no_hp AS no_hp_user,
            p.id AS properti_id, p.judul AS judul_properti, p.harga AS harga_properti,
            p.alamat AS alamat_properti, p.kota AS kota_properti, p.status AS status_properti
     FROM transaksi t
     JOIN users u ON t.user_id = u.id
     JOIN properti p ON t.properti_id = p.id
     WHERE t.id = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$transaksi) {
    die('Transaksi tidak ditemukan.');
}

$status_warna = [
    'menunggu' => '#f1eee5',
    'diproses' => '#e8f0f8',
    'disetujui' => '#eaf4ec',
    'ditolak' => '#fbeceb',
    'selesai' => '#eaf4ec',
];
$status_teks = [
    'menunggu' => '#766f60',
    'diproses' => '#2c4f74',
    'disetujui' => '#1e5c2c',
    'ditolak' => '#8a2c22',
    'selesai' => '#1e5c2c',
];

$user = user_login();
$page_title = 'Detail Transaksi - Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'transaksi';
require_once __DIR__ . '/includes/header.php';
?>
<style>
    .transaction-detail-card { background:#fff; border:1px solid var(--ivory-100); border-radius:3px; box-shadow:0 16px 38px rgba(10,24,38,0.06); overflow:hidden; }
    .transaction-detail-head { background:var(--navy-950); color:#fff; padding:clamp(1.35rem,3vw,2.25rem); position:relative; }
    .transaction-detail-head::after { content:""; position:absolute; right:-48px; top:-70px; width:190px; height:190px; border:1px solid rgba(227,200,150,0.2); transform:rotate(18deg); pointer-events:none; }
    .transaction-detail-head > * { position:relative; z-index:1; }
    .transaction-detail-head .section-eyebrow { color:var(--gold-300); }
    .transaction-detail-head .section-title { color:#fff; }
    .transaction-id { color:rgba(255,255,255,0.6); font-size:0.82rem; letter-spacing:0.08em; text-transform:uppercase; }
    .detail-block { height:100%; padding:1.25rem; border:1px solid var(--ivory-100); border-radius:3px; background:#fff; }
    .detail-block .detail-label { color:var(--ink-500); font-size:0.72rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; }
    .detail-block .detail-value { color:var(--navy-900); font-weight:700; }
    .detail-block .detail-value a { color:var(--gold-600); }
    .detail-block .detail-value a:hover { color:var(--navy-900); }
    .detail-note { min-height:92px; white-space:pre-line; }
    .payment-editor { background:#fbf8f1; border:1px solid var(--ivory-100); border-radius:3px; padding:1.25rem; }
    .payment-editor .form-control, .payment-editor .form-select { border-color:var(--ivory-100); border-radius:3px; }
    .payment-editor .form-control:focus, .payment-editor .form-select:focus { border-color:var(--gold-500); box-shadow:0 0 0 0.2rem rgba(201,162,75,0.2); }
    @media (max-width:575.98px) {
        .transaction-detail-head { display:block !important; }
        .transaction-detail-head .d-flex { justify-content:flex-start !important; margin-top:1rem; }
        .transaction-detail-head .btn { width:100%; }
    }

    /* ============ Struk Cetak ============ */
    /* Tersembunyi di layar biasa, cuma tampil pas print/save-as-PDF (lihat @media print) */
    #print-area { display:none; }
    .print-sheet {
        max-width: 640px; margin: 0 auto; border: 1px solid #d9d5c7;
        font-family: 'Manrope', sans-serif; color: #24261f;
    }
    .print-letterhead {
        background: var(--navy-950); color: #fff; padding: 1.75rem 2rem;
        display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;
    }
    .print-letterhead .brand { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.3rem; letter-spacing: 0.02em; }
    .print-letterhead .brand span { color: var(--gold-300); }
    .print-letterhead .brand-sub { font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.55); margin-top: 0.15rem; }
    .print-letterhead .doc-label { text-align: right; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold-300); }
    .print-letterhead .doc-number { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 600; margin-top: 0.2rem; }
    .print-status-strip {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.7rem 2rem; background: #eaf4ec; color: #1e5c2c;
        font-size: 0.78rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase;
        border-bottom: 1px solid #d9d5c7;
    }
    .print-body { padding: 1.75rem 2rem; }
    .print-section-title {
        font-size: 0.68rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase;
        color: #a39d87; margin: 1.4rem 0 0.6rem; border-bottom: 1px solid #ece8dc; padding-bottom: 0.4rem;
    }
    .print-section-title:first-child { margin-top: 0; }
    .print-row { display: flex; justify-content: space-between; gap: 1.5rem; padding: 0.4rem 0; font-size: 0.88rem; }
    .print-row .lbl { color: #766f60; }
    .print-row .val { font-weight: 700; text-align: right; }
    .print-total-row {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: 0.5rem; padding: 0.85rem 1rem; background: #fbf8f1; border: 1px solid #ece8dc; border-radius: 4px;
    }
    .print-total-row .lbl { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #766f60; }
    .print-total-row .val { font-family: 'Fraunces', serif; font-size: 1.35rem; font-weight: 700; color: var(--navy-900); }
    .print-note-box { border: 1px solid #ece8dc; border-radius: 4px; padding: 0.85rem 1rem; font-size: 0.85rem; white-space: pre-line; background: #fbf8f1; }
    .print-signature { display: flex; justify-content: flex-end; margin-top: 2.5rem; text-align: center; }
    .print-signature .box { width: 200px; }
    .print-signature .line { border-top: 1px solid #a39d87; margin-top: 3.2rem; padding-top: 0.4rem; font-size: 0.78rem; color: #766f60; }
    .print-footnote { text-align: center; font-size: 0.72rem; color: #a39d87; padding: 1rem 2rem 1.75rem; }

    @media print {
        .dashboard-sidebar, .page-header, .breadcrumb-estate,
        .transaction-detail-head .btn, .payment-editor, .alert-estate-success, .alert-estate-error,
        #print-toolbar { display:none !important; }
        body.has-dashboard-sidebar { padding:0 !important; }
        .transaction-detail-card { box-shadow:none !important; border:none !important; }
        #print-area { display:block !important; padding:1.5rem 0; }
        .print-sheet { border-color:#000; }
    }
</style>
<div class="page-header page-header-photo">
    <div class="container">
        <p class="eyebrow mb-2">Panel Admin</p>
        <h1 class="mb-2">Detail Transaksi</h1>
        <div class="breadcrumb-estate">
            <a href="<?= BASE_URL ?>index.php">Beranda</a><span class="sep">/</span>
            <a href="admin-transaksi.php">Kelola Transaksi</a><span class="sep">/</span>
            <span class="current">Transaksi #<?= $transaksi['id'] ?></span>
        </div>
    </div>
</div>

<main class="py-5">
    <div class="container">
        <?php if ($pesan_sukses): ?>
            <div class="alert-estate-success p-3 mb-4"><i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($pesan_sukses) ?></div>
        <?php elseif ($pesan_error): ?>
            <div class="alert-estate-error p-3 mb-4"><i class="bi bi-exclamation-circle-fill me-1"></i><?= htmlspecialchars($pesan_error) ?></div>
        <?php endif; ?>

        <div class="transaction-detail-card">
            <div class="transaction-detail-head d-flex justify-content-between align-items-start gap-3">
                <div>
                    <p class="section-eyebrow mb-2">Pengajuan Pembelian</p>
                    <h2 class="section-title mb-1">Detail Transaksi</h2>
                    <div class="transaction-id">Nomor transaksi #<?= $transaksi['id'] ?></div>
                    <div class="mt-3"><span class="badge-status" style="background:<?= $status_warna[$transaksi['status']] ?>;color:<?= $status_teks[$transaksi['status']] ?>;"><i class="bi bi-circle-fill"></i> <?= ucfirst($transaksi['status']) ?></span></div>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end" id="print-toolbar">
                    <?php if ($transaksi['status'] === 'selesai'): ?>
                        <button type="button" class="btn btn-outline-gold" onclick="window.print()"><i class="bi bi-printer-fill me-1"></i> Cetak Bukti</button>
                    <?php endif; ?>
                    <a href="admin-transaksi.php" class="btn btn-outline-gold"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
            </div>

            <?php if ($transaksi['status'] === 'selesai'): ?>
                <div id="print-area">
                    <div class="print-sheet">
                        <div class="print-letterhead">
                            <div>
                                <div class="brand">ESTATE <span>PRIMA</span></div>
                                <div class="brand-sub">Bukti Transaksi Properti</div>
                            </div>
                            <div class="doc-label">
                                No. Transaksi
                                <div class="doc-number">#TRX<?= str_pad((string) $transaksi['id'], 4, '0', STR_PAD_LEFT) ?></div>
                            </div>
                        </div>
                        <div class="print-status-strip">
                            <span><i class="bi bi-check-circle-fill me-1"></i> Lunas / Selesai</span>
                            <span>Dicetak <?= date('d M Y, H:i') ?></span>
                        </div>
                        <div class="print-body">
                            <p class="print-section-title">Data Pemohon</p>
                            <div class="print-row"><span class="lbl">Nama</span><span class="val"><?= htmlspecialchars($transaksi['nama_user']) ?></span></div>
                            <div class="print-row"><span class="lbl">Email</span><span class="val"><?= htmlspecialchars($transaksi['email_user']) ?></span></div>
                            <div class="print-row"><span class="lbl">No. HP</span><span class="val"><?= htmlspecialchars($transaksi['no_hp_user'] ?: '-') ?></span></div>

                            <p class="print-section-title">Data Properti</p>
                            <div class="print-row"><span class="lbl">Properti</span><span class="val"><?= htmlspecialchars($transaksi['judul_properti']) ?></span></div>
                            <div class="print-row"><span class="lbl">Alamat</span><span class="val"><?= htmlspecialchars($transaksi['alamat_properti']) ?>, <?= htmlspecialchars($transaksi['kota_properti']) ?></span></div>
                            <div class="print-row"><span class="lbl">Metode Pembayaran</span><span class="val"><?= $transaksi['metode_bayar'] ? ucwords(str_replace('_', ' ', $transaksi['metode_bayar'])) : '-' ?></span></div>

                            <p class="print-section-title">Waktu</p>
                            <div class="print-row"><span class="lbl">Diajukan</span><span class="val"><?= htmlspecialchars($transaksi['created_at']) ?></span></div>
                            <div class="print-row"><span class="lbl">Terakhir Diperbarui</span><span class="val"><?= htmlspecialchars($transaksi['updated_at']) ?></span></div>

                            <div class="print-total-row">
                                <span class="lbl">Total Nilai Transaksi</span>
                                <span class="val">Rp <?= number_format($transaksi['harga_properti'], 0, ',', '.') ?></span>
                            </div>

                            <?php if ($transaksi['catatan_admin']): ?>
                                <p class="print-section-title">Catatan Admin</p>
                                <div class="print-note-box"><?= nl2br(htmlspecialchars($transaksi['catatan_admin'])) ?></div>
                            <?php endif; ?>

                            <div class="print-signature">
                                <div class="box">
                                    <div class="line">Estate Prima — Admin</div>
                                </div>
                            </div>
                        </div>
                        <p class="print-footnote">Dokumen ini dicetak otomatis dari sistem Estate Prima sebagai bukti transaksi telah selesai.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="p-3 p-md-4">
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <p class="section-eyebrow mb-2">Pemohon</p>
                        <div class="detail-block">
                            <h3 class="h5 mb-2" style="font-family:'Fraunces',serif;color:var(--navy-900);"><?= htmlspecialchars($transaksi['nama_user']) ?></h3>
                            <div class="small text-muted mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($transaksi['email_user']) ?></div>
                            <div class="small text-muted"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($transaksi['no_hp_user'] ?: '-') ?></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <p class="section-eyebrow mb-2">Properti</p>
                        <div class="detail-block">
                            <h3 class="h5 mb-2" style="font-family:'Fraunces',serif;color:var(--navy-900);"><?= htmlspecialchars($transaksi['judul_properti']) ?></h3>
                            <div class="small text-muted mb-1"><i class="bi bi-tag me-2"></i>Rp <?= number_format($transaksi['harga_properti'], 0, ',', '.') ?></div>
                            <div class="small text-muted"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($transaksi['alamat_properti']) ?>, <?= htmlspecialchars($transaksi['kota_properti']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="payment-editor">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3 flex-wrap">
                        <div><p class="section-eyebrow mb-1">Pengelolaan Pembayaran</p><h2 class="section-title mb-0" style="font-size:1.45rem;">Perbarui Status Transaksi</h2></div>
                        <span class="small text-muted">Status properti: <strong><?= ucfirst($transaksi['status_properti']) ?></strong></span>
                    </div>
                    <form method="POST" action="admin-transaksi-detail.php?id=<?= $transaksi['id'] ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= $transaksi['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6 field-panel"><label class="form-label" for="metode_bayar">Metode Pembayaran</label><select class="form-select" id="metode_bayar" name="metode_bayar"><option value="">- Belum dipilih -</option><option value="transfer_bank" <?= $transaksi['metode_bayar'] === 'transfer_bank' ? 'selected' : '' ?>>Transfer Bank</option><option value="cicilan_kpr" <?= $transaksi['metode_bayar'] === 'cicilan_kpr' ? 'selected' : '' ?>>Cicilan KPR</option><option value="tunai" <?= $transaksi['metode_bayar'] === 'tunai' ? 'selected' : '' ?>>Tunai</option></select></div>
                            <div class="col-md-6 field-panel"><label class="form-label" for="status">Status Pembayaran</label><select class="form-select" id="status" name="status" required><?php foreach ($status_valid as $status): ?><option value="<?= $status ?>" <?= $transaksi['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?><?= $status === 'selesai' ? ' (Lunas)' : '' ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-6 field-panel"><label class="form-label" for="bukti_bayar_file">Upload Bukti Pembayaran (JPG/PNG/WEBP, maks 2MB)</label><input class="form-control" id="bukti_bayar_file" type="file" name="bukti_bayar_file" accept="image/jpeg,image/png,image/webp"></div>
                            <div class="col-md-6 field-panel"><label class="form-label" for="bukti_bayar">Atau URL Bukti Pembayaran</label><input class="form-control" id="bukti_bayar" type="text" name="bukti_bayar" value="<?= htmlspecialchars($transaksi['bukti_bayar'] ?? '') ?>" placeholder="https://..."></div>
                            <div class="col-12 field-panel"><label class="form-label" for="catatan_admin">Catatan Admin</label><textarea class="form-control" id="catatan_admin" name="catatan_admin" rows="4"><?= htmlspecialchars($transaksi['catatan_admin'] ?? '') ?></textarea></div>
                        </div>
                        <div class="form-actions"><button type="submit" class="btn btn-gold px-4"><i class="bi bi-check2-circle me-1"></i> Simpan Perubahan</button></div>
                    </form>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-6"><p class="section-eyebrow mb-2">Bukti Tersimpan</p><div class="detail-block"><span class="detail-label d-block mb-1">URL Bukti Pembayaran</span><div class="detail-value"><?php if ($transaksi['bukti_bayar']): ?><a href="<?= htmlspecialchars($transaksi['bukti_bayar']) ?>" target="_blank" rel="noopener" class="text-break">Buka bukti pembayaran <i class="bi bi-box-arrow-up-right"></i></a><?php else: ?>-<?php endif; ?></div></div></div>
                    <div class="col-md-6"><p class="section-eyebrow mb-2">Waktu</p><div class="detail-block"><span class="detail-label d-block mb-1">Diajukan</span><strong class="detail-value d-block"><?= htmlspecialchars($transaksi['created_at']) ?></strong><span class="detail-label d-block mt-3 mb-1">Terakhir Diperbarui</span><strong class="detail-value d-block"><?= htmlspecialchars($transaksi['updated_at']) ?></strong></div></div>
                    <div class="col-12"><p class="section-eyebrow mb-2">Catatan Saat Ini</p><div class="detail-block detail-note"><?= $transaksi['catatan_admin'] ? htmlspecialchars($transaksi['catatan_admin']) : '<span class="text-muted">Belum ada catatan admin.</span>' ?></div></div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
