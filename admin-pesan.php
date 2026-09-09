<?php
/**
 * admin-pesan.php - Estate Prima
 * Lihat semua pesan masuk dari form kontak.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_admin();

$daftar_pesan = mysqli_fetch_all(mysqli_query($koneksi,
    "SELECT * FROM pesan_kontak ORDER BY created_at DESC"
), MYSQLI_ASSOC);

$user = user_login();
$page_title = 'Pesan Kontak — Estate Prima';
$admin_sidebar = true;
$dashboard_sidebar_active = 'pesan';
require_once __DIR__ . '/includes/header.php';
?>
    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Pesan Kontak Masuk</h1>
            <p class="lead mb-0">Baca dan kelola pesan yang dikirim calon pelanggan.</p>

        </div>
    </div>

    <main class="py-5">
        <div class="container">
            <p class="section-eyebrow mb-2">Kotak Masuk</p>
            <h2 class="section-title mb-4" style="font-size:1.6rem;"><?= count($daftar_pesan) ?> Pesan dari Calon Pembeli</h2>

            <?php if (empty($daftar_pesan)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size:2.5rem; color:var(--gold-500);"></i>
                    <p class="text-muted mt-3">Belum ada pesan masuk.</p>
                </div>
            <?php else: ?>
                <?php foreach ($daftar_pesan as $p): ?>
                    <div class="pesan-card <?= $p['status_dibaca'] === 'belum' ? 'belum-dibaca' : '' ?>">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="nama"><?= htmlspecialchars($p['nama']) ?></span>
                                <?php if ($p['status_dibaca'] === 'belum'): ?>
                                    <span class="badge-belum">Belum Dibaca</span>
                                <?php endif; ?>
                                <div class="meta">
                                    <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($p['email']) ?>
                                    <?php if ($p['no_hp']): ?>
                                        &nbsp;&middot;&nbsp;<i class="bi bi-telephone me-1"></i><?= htmlspecialchars($p['no_hp']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="meta"><?= $p['created_at'] ?></div>
                        </div>

                        <p class="isi-pesan"><?= nl2br(htmlspecialchars($p['pesan'])) ?></p>

                        <div class="d-flex gap-2">
    <?php if ($p['status_dibaca'] === 'belum'): ?>
        <form method="POST" action="proses-pesan.php" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="aksi" value="tandai-dibaca">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="btn-mini btn-tandai">
                <i class="bi bi-check2 me-1"></i> Tandai Dibaca
            </button>
        </form>
    <?php endif; ?>

    <button type="button" class="btn-mini btn-hapus-pesan" data-bs-toggle="modal" data-bs-target="#modalHapusPesan<?= $p['id'] ?>">
        <i class="bi bi-trash-fill me-1"></i> Hapus
    </button>

    <!-- Modal Hapus Pesan -->
    <div class="modal fade" id="modalHapusPesan<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Pesan?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    Hapus pesan dari <strong><?= htmlspecialchars($p['nama']) ?></strong>? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-navy" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="proses-pesan.php" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Hapus Pesan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/dashboard-footer.php'; ?>