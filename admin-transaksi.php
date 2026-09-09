<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
cek_admin();
$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');
$valid = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai', 'lunas'];
if (!in_array($status, $valid, true)) $status = '';
$per_page = 10; $page = max((int)($_GET['page'] ?? 1), 1); $offset = ($page - 1) * $per_page; $like = "%{$q}%";
$where = []; $types = ''; $params = [];
if ($status !== '') { $where[] = 't.status = ?'; $types .= 's'; $params[] = $status; }
if ($q !== '') { $where[] = '(u.nama LIKE ? OR p.judul LIKE ?)'; $types .= 'ss'; $params[] = $like; $params[] = $like; }
$where_sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$count = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM transaksi t JOIN users u ON u.id=t.user_id JOIN properti p ON p.id=t.properti_id{$where_sql}");
if ($params) mysqli_stmt_bind_param($count, $types, ...$params); mysqli_stmt_execute($count); $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count))['total']; mysqli_stmt_close($count);
$pages = max((int)ceil($total / $per_page), 1);
$stmt = mysqli_prepare($koneksi, "SELECT t.id, t.tipe_transaksi, t.total_harga, t.status, t.metode_bayar, t.created_at, u.nama AS nama_user, p.judul, p.harga FROM transaksi t JOIN users u ON t.user_id=u.id JOIN properti p ON t.properti_id=p.id{$where_sql} ORDER BY t.created_at DESC LIMIT ? OFFSET ?");
$all_params = array_merge($params, [$per_page, $offset]); mysqli_stmt_bind_param($stmt, $types . 'ii', ...$all_params); mysqli_stmt_execute($stmt); $daftar_transaksi = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC); mysqli_stmt_close($stmt);
$user = user_login(); $page_title = 'Kelola Transaksi — Estate Prima'; $admin_sidebar = true; $dashboard_sidebar_active = 'transaksi';
require_once __DIR__ . '/includes/header.php';
?>
<header class="page-header"><div class="container"><p class="eyebrow mb-2">Panel Admin</p><h1 class="mb-2">Kelola Transaksi</h1><p class="lead mb-0">Tinjau, proses, dan perbarui status pengajuan pelanggan.</p></div></header>
<main class="py-5"><div class="container">
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3"><div><p class="section-eyebrow mb-2">Daftar Pengajuan</p><h2 class="section-title mb-0" style="font-size:1.6rem;"><?= $total ?> Transaksi</h2></div><form method="GET" class="d-flex gap-2"><input class="form-control form-control-sm" name="q" placeholder="Cari user / properti..." value="<?= htmlspecialchars($q) ?>"><input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>"><button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button></form></div>
<div class="filter-panel d-flex gap-3 flex-wrap mb-4"><?php $query_base = '&q=' . urlencode($q); $status_icons = ['menunggu'=>'bi-hourglass-split','diproses'=>'bi-arrow-repeat','disetujui'=>'bi-check-circle','ditolak'=>'bi-x-circle','selesai'=>'bi-check2-all','lunas'=>'bi-cash-coin']; ?><a href="?<?= $q ? 'q='.urlencode($q) : '' ?>" class="filter-link <?= $status === '' ? 'active' : '' ?>">Semua</a><?php foreach ($valid as $s): ?><a href="?status=<?= $s ?><?= $query_base ?>" class="filter-link <?= $status === $s ? 'active' : '' ?>"><i class="bi <?= $status_icons[$s] ?> me-1"></i><?= ucfirst($s) ?></a><?php endforeach; ?></div>
<div class="table-responsive"><table class="table-estate"><thead><tr><th>No.</th><th>User</th><th>Tipe</th><th>Properti</th><th>Total Harga</th><th>Metode</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody>
<?php if (!$daftar_transaksi): ?><tr><td colspan="9" class="text-center text-muted py-4">Tidak ada transaksi.</td></tr><?php else: $nomor = $offset + 1; foreach ($daftar_transaksi as $t): $jenis = $t['tipe_transaksi'] ?? 'jual'; $icon = ['menunggu'=>'bi-hourglass-split','diproses'=>'bi-arrow-repeat','disetujui'=>'bi-check-circle','ditolak'=>'bi-x-circle','selesai'=>'bi-check2-all','lunas'=>'bi-cash-coin'][$t['status']] ?? 'bi-circle'; ?>
<tr><td><?= $nomor++ ?></td><td><strong><?= htmlspecialchars($t['nama_user']) ?></strong></td><td><span class="badge rounded-pill <?= $jenis === 'sewa' ? 'text-bg-primary' : 'text-bg-success' ?>"><?= strtoupper($jenis) ?></span></td><td class="property-name"><?= htmlspecialchars($t['judul']) ?></td><td>Rp <?= number_format($t['total_harga'] ?? $t['harga'], 0, ',', '.') ?></td><td><?= $t['metode_bayar'] ? htmlspecialchars(ucwords(str_replace('_', ' ', $t['metode_bayar']))) : '-' ?></td><td><span class="status-pill <?= htmlspecialchars($t['status']) ?>"><i class="bi <?= $icon ?>"></i> <?= ucfirst($t['status']) ?></span></td><td class="text-nowrap"><?= htmlspecialchars($t['created_at']) ?></td><td><a href="admin-transaksi-detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-pencil-square"></i></a></td></tr>
<?php endforeach; endif; ?></tbody></table></div>
<?php if ($pages > 1): ?><nav class="pagination-estate mt-4"><?php for ($i=1;$i<=$pages;$i++): ?><a class="<?= $i === $page ? 'active' : '' ?>" href="?status=<?= urlencode($status) ?>&q=<?= urlencode($q) ?>&page=<?= $i ?>"><?= $i ?></a><?php endfor; ?></nav><?php endif; ?>
</div></main>
<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>
