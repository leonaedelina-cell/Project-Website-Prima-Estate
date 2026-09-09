<?php
/**
 * cara-kerja.php - Estate Prima
 * Panduan singkat proses membeli atau menyewa properti.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$stmt_agen = mysqli_prepare($koneksi, "SELECT nama, no_hp, email, foto_url FROM agen ORDER BY nama LIMIT 6");
mysqli_stmt_execute($stmt_agen);
$agen = mysqli_fetch_all(mysqli_stmt_get_result($stmt_agen), MYSQLI_ASSOC);
mysqli_stmt_close($stmt_agen);

$user = user_login();
$page_title = 'Cara Kerja — Estate Prima';
require_once __DIR__ . '/includes/header.php';
?>

<header class="page-header page-header-photo">
    <div class="container">
        <p class="eyebrow mb-2">Panduan Estate Prima</p>
        <h1 class="mb-2">Cara Kerja</h1>
        <p class="lead mb-0">Proses mencari hunian, mengajukan transaksi, dan menyelesaikannya bersama tim sales kami.</p>
    </div>
</header>

<main>
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-eyebrow mb-2">Mudah dan Transparan</p>
                <h2 class="section-title">Dari pencarian sampai serah terima</h2>
                <p class="text-muted mx-auto" style="max-width:680px;">Kami membantu Anda di setiap tahap. Pembayaran dan negosiasi dilakukan langsung bersama tim sales melalui WhatsApp.</p>
            </div>

            <div class="row g-4">
                <?php
                $langkah = [
                    ['bi-search', 'Cari Properti', 'Gunakan pencarian dan filter untuk menemukan rumah, apartemen, tanah, atau ruko yang sesuai.'],
                    ['bi-house-check', 'Lihat Detail', 'Periksa harga, lokasi, fasilitas, status hunian, galeri, dan informasi agen sales.'],
                    ['bi-chat-dots', 'Ajukan Transaksi', 'Login lalu pilih Ajukan Beli atau Ajukan Sewa. Data pengajuan langsung tersimpan.'],
                    ['bi-whatsapp', 'Diskusi via WhatsApp', 'Anda akan diarahkan ke WhatsApp admin untuk membahas survei, nego harga, dan jadwal.'],
                    ['bi-receipt', 'Pantau Status', 'Lihat perkembangan pengajuan Anda dari dashboard Pesanan Saya.'],
                    ['bi-key', 'Selesaikan Transaksi', 'Setelah pembayaran dikonfirmasi admin, status transaksi menjadi selesai atau lunas.'],
                ];
                foreach ($langkah as $nomor => $item):
                ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="field-panel h-100 position-relative">
                            <span class="badge rounded-pill text-bg-dark mb-3"><?= $nomor + 1 ?></span>
                            <i class="bi <?= $item[0] ?> text-gold fs-2 d-block mb-3"></i>
                            <h3 class="h5"><?= htmlspecialchars($item[1]) ?></h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($item[2]) ?></p>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if ($agen): ?>
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5">
                        <p class="section-eyebrow mb-2">Tim Kami</p>
                        <h2 class="section-title mb-3">Didampingi agen yang siap membantu</h2>
                        <p class="text-muted">Hubungi tim sales untuk mendapatkan informasi lebih lanjut atau menjadwalkan survei properti.</p>
                        <a class="btn btn-gold" href="https://wa.me/<?= WHATSAPP_ADMIN ?>?text=<?= urlencode('Halo Estate Prima, saya ingin berkonsultasi tentang properti.') ?>" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp me-1"></i> Hubungi Tim Sales
                        </a>
                    </div>
                    <div class="col-lg-7">
                        <div id="agenCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($agen as $index => $orang): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="agent-card bg-white p-4">
                                            <div class="avatar" style="<?= !empty($orang['foto_url']) ? "background-image:url('" . htmlspecialchars($orang['foto_url']) . "');" : '' ?>">
                                                <?= empty($orang['foto_url']) ? strtoupper(substr($orang['nama'], 0, 1)) : '' ?>
                                            </div>
                                            <div>
                                                <div class="role">Agen Sales</div>
                                                <div class="name"><?= htmlspecialchars($orang['nama']) ?></div>
                                                <div class="contact">
                                                    <i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($orang['no_hp'] ?: '-') ?>
                                                    <?php if (!empty($orang['email'])): ?>
                                                        &nbsp;&middot;&nbsp;<i class="bi bi-envelope-fill me-1"></i><?= htmlspecialchars($orang['email']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($agen) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#agenCarousel" data-bs-slide="prev" aria-label="Agen sebelumnya"><span class="carousel-control-prev-icon"></span></button>
                                <button class="carousel-control-next" type="button" data-bs-target="#agenCarousel" data-bs-slide="next" aria-label="Agen berikutnya"><span class="carousel-control-next-icon"></span></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <p class="section-eyebrow mb-2 text-center">Pertanyaan Umum</p>
                    <h2 class="section-title text-center mb-4">FAQ</h2>
                    <div class="accordion" id="faqCaraKerja">
                        <?php
                        $faq = [
                            ['Apakah pembayaran dilakukan langsung di website?', 'Tidak. Estate Prima tidak menggunakan payment gateway. Pembayaran dan konfirmasi dilakukan secara manual bersama admin setelah berdiskusi melalui WhatsApp.'],
                            ['Apakah saya harus login untuk mengajukan transaksi?', 'Ya. Login diperlukan agar pengajuan tersimpan ke akun Anda dan statusnya dapat dipantau melalui halaman Pesanan Saya.'],
                            ['Apa perbedaan beli dan sewa?', 'Properti beli menggunakan harga jual, sedangkan properti sewa memiliki harga per periode dan durasi minimal yang ditampilkan di detail properti.'],
                            ['Bagaimana cara menghubungi agen?', 'Gunakan tombol Hubungi Tim Sales atau informasi agen pada halaman detail properti untuk memulai konsultasi.'],
                        ];
                        foreach ($faq as $index => $item):
                        ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="faqHeading<?= $index ?>">
                                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="faqCollapse<?= $index ?>">
                                        <?= htmlspecialchars($item[0]) ?>
                                    </button>
                                </h3>
                                <div id="faqCollapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="faqHeading<?= $index ?>" data-bs-parent="#faqCaraKerja">
                                    <div class="accordion-body"><?= htmlspecialchars($item[1]) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
