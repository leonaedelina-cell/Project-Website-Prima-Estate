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
        <div class="cara-kerja-hero-content">
            <p class="eyebrow mb-2">Panduan Estate Prima</p>
            <h1 class="mb-2">Cara Kerja</h1>
            <p class="lead mb-0">Proses mencari hunian, mengajukan transaksi, dan menyelesaikannya bersama tim sales kami.</p>
            <div class="cara-kerja-hero-tags mt-4">
                <span><i class="bi bi-check2-circle me-1"></i> Terpercaya</span>
                <span><i class="bi bi-chat-heart me-1"></i> Didampingi</span>
                <span><i class="bi bi-shield-check me-1"></i> Transparan</span>
            </div>
        </div>
    </div>
</header>

<main>
    <section class="py-5 cara-kerja-steps-section">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-eyebrow mb-2">Mudah dan Transparan</p>
                <h2 class="section-title">Dari pencarian sampai serah terima</h2>
                <p class="text-muted mx-auto" style="max-width:680px;">Kami membantu Anda di setiap tahap. Pembayaran dan negosiasi dilakukan langsung bersama tim sales melalui WhatsApp.</p>
                <div class="cara-kerja-flow-label"><span></span> 6 langkah sederhana <span></span></div>
            </div>

            <div class="row g-4 cara-kerja-steps">
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
                        <article class="cara-kerja-step-card h-100 position-relative">
                            <div class="cara-kerja-step-number"><?= str_pad((string)($nomor + 1), 2, '0', STR_PAD_LEFT) ?></div>
                            <div class="cara-kerja-step-icon"><i class="bi <?= $item[0] ?>"></i></div>
                            <h3 class="h5"><?= htmlspecialchars($item[1]) ?></h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($item[2]) ?></p>
                            <span class="cara-kerja-step-arrow"><i class="bi bi-arrow-up-right"></i></span>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

        <section class="py-5 cara-kerja-team-section">
            <div class="container">
                <div class="text-center mb-4">
                    <p class="section-eyebrow mb-2">Hubungi Tim Sales</p>
                    <h2 class="section-title mb-2">Tim Sales Kami</h2>
                    <p class="cara-kerja-team-intro mb-0">Siap membantu Anda dari mencari properti sampai transaksi selesai.</p>
                </div>
                <div class="cara-kerja-agents">
                        <?php if ($agen): ?>
                        <div id="agenCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($agen as $index => $orang): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="cara-kerja-agent-card">
                                            <?php if (!empty($orang['foto_url'])): ?>
                                                <img class="cara-kerja-agent-photo" src="<?= htmlspecialchars($orang['foto_url']) ?>" alt="Foto <?= htmlspecialchars($orang['nama']) ?>">
                                            <?php else: ?>
                                                <div class="cara-kerja-agent-photo"><?= htmlspecialchars(strtoupper(substr($orang['nama'], 0, 1))) ?></div>
                                            <?php endif; ?>
                                            <h3><?= htmlspecialchars($orang['nama']) ?></h3>
                                            <p class="cara-kerja-agent-role"><span>Sales Agent</span> </p>
                                            <p class="cara-kerja-agent-contact"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($orang['no_hp'] ?: '-') ?></p>
                                            <a class="btn btn-success btn-sm" href="https://wa.me/<?= preg_replace('/\D+/', '', $orang['no_hp'] ?? WHATSAPP_ADMIN) ?>?text=<?= urlencode('Halo ' . $orang['nama'] . ', saya ingin berkonsultasi tentang properti Estate Prima.') ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i> Chat <?= htmlspecialchars($orang['nama']) ?></a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($agen) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#agenCarousel" data-bs-slide="prev" aria-label="Agen sebelumnya"><span class="carousel-control-prev-icon"></span></button>
                                <button class="carousel-control-next" type="button" data-bs-target="#agenCarousel" data-bs-slide="next" aria-label="Agen berikutnya"><span class="carousel-control-next-icon"></span></button>
                            <?php endif; ?>
                            <div class="carousel-indicators position-relative mt-3">
                                <?php foreach ($agen as $index => $orang): ?>
                                    <button type="button" data-bs-target="#agenCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Agen <?= $index + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                            <div class="cara-kerja-agent-card"><p class="mb-0">Tim sales kami siap membantu melalui WhatsApp.</p></div>
                        <?php endif; ?>
                </div>
            </div>
        </section>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="cara-kerja-faq-header text-center">
                        <p class="section-eyebrow mb-2">Pertanyaan Umum</p>
                        <h2 class="section-title mb-3">FAQ</h2>
                        <p class="text-muted">Temukan jawaban singkat tentang proses pencarian, pengajuan, pembayaran, dan pemantauan transaksi di Estate Prima.</p>
                    </div>
                    <div class="accordion cara-kerja-faq" id="faqCaraKerja">
                        <?php
                        $faq = [
                            ['Bagaimana cara mencari properti?', 'Buka halaman Properti, lalu gunakan filter tipe transaksi, tipe properti, kota, kata kunci, dan rentang harga. Klik Lihat Detail untuk memeriksa informasi lengkap properti yang Anda pilih.'],
                            ['Apakah saya harus login untuk mengajukan transaksi?', 'Ya. Login diperlukan agar pengajuan tercatat atas nama Anda. Setelah itu, Anda dapat memantau status pengajuan melalui halaman Pesanan Saya.'],
                            ['Apa perbedaan transaksi beli dan sewa?', 'Pada transaksi beli, Anda membayar harga jual properti. Pada transaksi sewa, Anda membayar harga sesuai periode sewa dan harus memenuhi durasi minimal yang tercantum di detail properti.'],
                            ['Bagaimana proses pembayaran dilakukan?', 'Pembayaran tidak dilakukan langsung melalui website. Setelah berdiskusi dengan admin melalui WhatsApp, Anda akan memperoleh arahan metode pembayaran dan mengunggah bukti pembayaran melalui halaman transaksi.'],
                            ['Bagaimana cara menghubungi agen?', 'Klik tombol Chat via WhatsApp pada bagian Hubungi Tim Sales atau gunakan kontak agen yang tampil di halaman detail properti untuk mendapatkan bantuan dan menjadwalkan survei.'],
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
