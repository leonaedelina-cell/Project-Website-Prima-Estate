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
    .admin-subnav { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.25rem; }
    .admin-subnav a {
        padding: 0.5rem 1.1rem; border-radius: 3px; font-weight: 700; font-size: 0.85rem;
        color: rgba(255,255,255,0.8); border: 1px solid rgba(255,255,255,0.25); text-decoration: none;
        transition: all 0.2s ease;
    }
    .admin-subnav a:hover { border-color: var(--gold-500); color: var(--gold-300); }
    .admin-subnav a.active { background: var(--gold-grad); border-color: var(--gold-600); color: var(--navy-950); }

    .pesan-card {
        background: #fff; border: 1px solid var(--ivory-100); border-radius: 3px;
        padding: 1.25rem 1.5rem; margin-bottom: 1rem; position: relative;
    }
    .pesan-card.belum-dibaca { border-left: 3px solid var(--gold-500); background: #fdf9f0; }
    .pesan-card .nama { font-family: 'Fraunces', serif; font-weight: 600; color: var(--navy-900); }
    .pesan-card .meta { font-size: 0.82rem; color: var(--ink-500); }
    .badge-belum {
        background: var(--gold-grad); color: var(--navy-950); font-size: 0.66rem; font-weight: 800;
        letter-spacing: 0.06em; text-transform: uppercase; padding: 0.25rem 0.6rem; border-radius: 3px;
        margin-left: 0.5rem;
    }
    .pesan-card .isi-pesan { color: var(--ink-900); font-size: 0.92rem; margin: 0.85rem 0; line-height: 1.6; }
    .btn-mini {
        font-size: 0.78rem; padding: 0.35rem 0.8rem; border-radius: 3px; font-weight: 700;
        border: 1px solid var(--ivory-100); text-decoration: none; display: inline-block;
    }
    .btn-tandai { color: var(--navy-900); background: #fff; }
    .btn-tandai:hover { border-color: var(--gold-500); color: var(--gold-600); }
    .btn-hapus-pesan { color: #8a2c22; background: #fff; }
    .btn-hapus-pesan:hover { background: #fbeceb; border-color: #f0bdb9; }
</style>

    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Panel Admin</p>
            <h1 class="mb-2">Pesan Kontak Masuk</h1>
            <div class="breadcrumb-estate">
                <a href="<?= BASE_URL ?>index.php">Beranda</a>
                <span class="sep">/</span>
                <span class="current">Pesan Kontak</span>
            </div>

            <div class="admin-subnav">
                <a href="admin-dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                <a href="admin-properti.php"><i class="bi bi-houses-fill me-1"></i> Kelola Properti</a>
                <a href="admin-transaksi.php"><i class="bi bi-receipt me-1"></i> Kelola Transaksi</a>
                <a href="admin-agen.php"><i class="bi bi-person-badge-fill me-1"></i> Kelola Agen</a>
                <a href="admin-pesan.php" class="active"><i class="bi bi-envelope-fill me-1"></i> Pesan Kontak</a>
            </div>
        </div>
    </div>

    <section class="py-5">
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
                                    <input type="hidden" name="aksi" value="tandai-dibaca">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn-mini btn-tandai">
                                        <i class="bi bi-check2 me-1"></i> Tandai Dibaca
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="proses-pesan.php" class="d-inline"
                                  onsubmit="return confirm('Hapus pesan ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-mini btn-hapus-pesan">
                                    <i class="bi bi-trash-fill me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>