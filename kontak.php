<?php
/**
 * kontak.php - Estate Prima
 * Halaman contact sales: info kontak + Google Maps embed kantor + form kontak.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if ($nama === '') $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if ($pesan === '') $errors[] = 'Pesan wajib diisi.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO pesan_kontak (nama, email, no_hp, pesan) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $no_hp, $pesan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $berhasil = true;
    }
}

$user = user_login();
$page_title = 'Hubungi Kami — Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>
    <!-- ============ PAGE HEADER ============ -->
    <div class="page-header page-header-photo">
        <div class="container">
            <p class="eyebrow mb-2">Kami Siap Membantu</p>
            <h1 class="mb-2">Hubungi Tim Sales Kami</h1>
            <div class="breadcrumb-estate">
                <a href="index.php">Beranda</a>
                <span class="sep">/</span>
                <span class="current">Kontak</span>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">

            <!-- ============ BARIS ATAS: INFO KONTAK (KIRI) + MAPS (KANAN) ============ -->
            <div class="row g-4 mb-5">
                <div class="col-lg-5">
                    <div class="info-card h-100">
                        <h5 class="mb-4">Kantor Estate Prima</h5>
                        <div class="item d-flex gap-3 mb-3">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Jl. Sudirman, Jakarta Selatan, Indonesia</span>
                        </div>
                        <div class="item d-flex gap-3 mb-3">
                            <i class="bi bi-telephone-fill"></i>
                            <span>(021) 555-0182</span>
                        </div>
                        <div class="item d-flex gap-3 mb-3">
                            <i class="bi bi-envelope-fill"></i>
                            <span>halo@estateprima.test</span>
                        </div>
                        <div class="item d-flex gap-3">
                            <i class="bi bi-clock-fill"></i>
                            <span>Senin - Sabtu, 09.00 - 18.00 WIB</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="rounded overflow-hidden h-100" style="border:1px solid var(--ivory-100); min-height:280px;">
                        <iframe
                            src="https://www.google.com/maps?q=Jakarta+Selatan&output=embed"
                            style="border:0; width:100%; height:100%; min-height:280px;"
                            allowfullscreen loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>

            <!-- ============ BARIS BAWAH: FORM KONTAK (FULL WIDTH) ============ -->
            <p class="section-eyebrow mb-2">Kirim Pesan</p>
            <h2 class="section-title mb-4" style="font-size:1.75rem;">Ada pertanyaan seputar properti?</h2>

            <?php if ($berhasil): ?>
                <div class="alert-estate-success p-3 mb-4">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Pesan kamu berhasil terkirim! Tim kami akan segera menghubungi kamu.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert-estate-error p-3 mb-4">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="kontak.php" class="field-panel">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="d-block">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control"
                               value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block">No. HP (opsional)</label>
                        <input type="text" name="no_hp" class="form-control"
                               value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="d-block">Pesan</label>
                        <textarea name="pesan" rows="5" class="form-control" required><?= htmlspecialchars($_POST['pesan'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-gold px-4 py-2">
                            <i class="bi bi-send-fill me-1"></i> Kirim Pesan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>