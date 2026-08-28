<?php
/**
 * admin-transaksi.php - Estate Prima
 * List semua transaksi, filter by status.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$filter_status = $_GET['status'] ?? '';
$status_valid  = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai'];
$status_icon   = [
    'menunggu'  => 'bi-hourglass-split',
    'diproses'  => 'bi-arrow-repeat',
    'disetujui' => 'bi-check-circle-fill',
    'ditolak'   => 'bi-x-circle-fill',
    'selesai'   => 'bi-check2-circle',
];

$q = trim($_GET['q'] ?? '');
$data_per_halaman = 10;
$halaman = max((int)($_GET['page'] ?? 1), 1);
$offset = ($halaman - 1) * $data_per_halaman;

$kondisi = [];
$parameter = [];
$tipe_data = '';
if (in_array($filter_status, $status_valid, true)) {
    $kondisi[] = 't.status = ?';
    $parameter[] = $filter_status;
    $tipe_data .= 's';
}
if ($q !== '') {
    $kondisi[] = '(u.nama LIKE ? OR p.judul LIKE ?)';
    $keyword = "%{$q}%";
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $tipe_data .= 'ss';
}
$where_sql = $kondisi ? 'WHERE ' . implode(' AND ', $kondisi) : '';

$query_base = "FROM transaksi t
          JOIN users u ON t.user_id = u.id
          JOIN properti p ON t.properti_id = p.id
          {$where_sql}";

$stmt_total = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total {$query_base}");
if (!empty($parameter)) {
    mysqli_stmt_bind_param($stmt_total, $tipe_data, ...$parameter);
}
mysqli_stmt_execute($stmt_total);
$total_transaksi = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['total'];
$total_halaman = max((int)ceil($total_transaksi / $data_per_halaman), 1);
mysqli_stmt_close($stmt_total);

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT t.id, t.status, t.metode_bayar, t.created_at, u.nama AS nama_user, p.judul, p.harga
     {$query_base}
     ORDER BY t.created_at DESC LIMIT ? OFFSET ?"
);
$tipe_data_full = $tipe_data . 'ii';
$parameter_full = array_merge($parameter, [$data_per_halaman, $offset]);
mysqli_stmt_bind_param($stmt, $tipe_data_full, ...$parameter_full);
mysqli_stmt_execute($stmt);
$daftar_transaksi = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$user = user_login();
$page_title = 'Kelola Transaksi — Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'transaksi';
require_once __DIR__ . '/includes/header.php';
?>
<style>
    .page-header-photo {
        background-image:
            linear-gradient(180deg, rgba(13,31,51,0.72) 0%, rgba(13,31,51,0.6) 55%, rgba(13,31,51,0.94) 100%),
            url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?fm=jpg&q=80&w=2000&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
    }

    .filter-panel { background: #fff; border: 1px solid var(--ivory-100); border-radius: 3px; padding: 1rem 1.25rem; }
    .filter-link { color: var(--ink-500); text-decoration: none; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .filter-link:hover, .filter-link.active { color: var(--gold-600); }

    .table-estate {
        width: 100%; background: #fff; border-collapse: collapse;
        border: 1px solid var(--ivory-100); border-radius: 3px; overflow: hidden;
    }
    .table-estate thead { background: var(--navy-950); }
    .table-estate thead th {
        color: rgba(255,255,255,0.85); font-size: 0.72rem; font-weight: 700;
        letter-spacing: 0.08em; text-transform: uppercase; padding: 0.85rem 1rem; text-align: left; white-space: nowrap;
    }
    .table-estate tbody td { padding: 0.85rem 1rem; border-top: 1px solid var(--ivory-100); font-size: 0.9rem; vertical-align: middle; }
    .table-estate tbody tr:hover { background: #fbf8f1; }
    .table-estate .property-name { color: var(--navy-900); font-weight: 700; }
    .table-estate .price { font-family: 'Fraunces', serif; font-weight: 600; color: var(--navy-900); white-space: nowrap; }

    .status-pill {
        display: inline-flex; align-items: center; gap: 0.35rem; border-radius: 2px;
        padding: 0.35rem 0.65rem; font-size: 0.68rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;
    }
    .status-pill.menunggu { background: #f1eee5; color: #766f60; }
    .status-pill.diproses { background: #e8f0f8; color: #2c4f74; }
    .status-pill.disetujui, .status-pill.selesai { background: #eaf4ec; color: #1e5c2c; }
    .status-pill.ditolak { background: #fbeceb; color: #8a2c22; }

    .btn-mini {
        font-size: 0.78rem; padding: 0.3rem 0.7rem; border-radius: 3px; font-weight: 700;
        text-decoration: none; display: inline-block; border: 1px solid var(--ivory-100); color: var(--navy-900);
    }
    .btn-mini:hover { border-color: var(--gold-500); color: var(--gold-600); }
</style>

    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Kelola Transaksi</h1>
            <p class="lead mb-0">Tinjau pengajuan customer dan perbarui status pembayaran.</p>

        </div>
    </div>

    <main class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <p class="section-eyebrow mb-2">Daftar Pengajuan</p>
                    <h2 class="section-title mb-0" style="font-size:1.6rem;"><?= $total_transaksi ?> Transaksi</h2>
                </div>
                <form method="GET" class="d-flex gap-2">
                    <?php if ($filter_status !== ''): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>"><?php endif; ?>
                    <input type="search" name="q" class="form-control" placeholder="Cari customer/properti..." value="<?= htmlspecialchars($q) ?>">
                    <button type="submit" class="btn btn-outline-navy"><i class="bi bi-search"></i></button>
                </form>
            </div>

            <div class="filter-panel d-flex align-items-center gap-3 flex-wrap mb-4">
                <span class="small fw-bold text-uppercase text-muted">Filter Status</span>
                <a href="admin-transaksi.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>" class="filter-link <?= $filter_status === '' ? 'active' : '' ?>">Semua</a>
                <?php foreach ($status_valid as $s): ?>
                    <a href="admin-transaksi.php?status=<?= $s ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="filter-link <?= $filter_status === $s ? 'active' : '' ?>"><i class="bi <?= $status_icon[$s] ?> me-1"></i><?= ucfirst($s) ?></a>
                <?php endforeach; ?>
            </div>

            <div class="table-responsive">
                <table class="table-estate">
                    <thead>
                        <tr><th>No</th><th>User</th><th>Properti</th><th>Harga</th><th>Metode Bayar</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_transaksi)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada transaksi ditemukan.</td></tr>
                        <?php else: ?>
                            <?php $nomor = $offset + 1; foreach ($daftar_transaksi as $t): ?>
                                <tr>
                                    <td><?= $nomor++ ?></td>
                                    <td><strong><?= htmlspecialchars($t['nama_user']) ?></strong></td>
                                    <td class="property-name"><?= htmlspecialchars($t['judul']) ?></td>
                                    <td class="price">Rp <?= number_format($t['harga'], 0, ',', '.') ?></td>
                                    <td><?= $t['metode_bayar'] ? ucwords(str_replace('_', ' ', $t['metode_bayar'])) : '-' ?></td>
                                    <td><span class="status-pill <?= htmlspecialchars($t['status']) ?>"><i class="bi <?= $status_icon[$t['status']] ?? 'bi-circle-fill' ?>"></i><?= ucfirst($t['status']) ?></span></td>
                                    <td class="text-nowrap text-muted"><?= htmlspecialchars($t['created_at']) ?></td>
                                    <td><a href="admin-transaksi-detail.php?id=<?= $t['id'] ?>" class="btn-mini"><i class="bi bi-pencil-square me-1"></i> Kelola</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_halaman > 1): ?>
                <nav class="pagination-estate mt-4" aria-label="Halaman transaksi">
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <a href="admin-transaksi.php?page=<?= $i ?><?= $filter_status !== '' ? '&status=' . $filter_status : '' ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="<?= $i === $halaman ? 'active' : '' ?>" aria-label="Halaman <?= $i ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>