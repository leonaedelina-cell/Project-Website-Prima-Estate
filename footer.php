<?php
/**
 * includes/footer.php - Estate Prima
 * Partial: footer + penutup </body></html>. Include di akhir halaman PUBLIK.
 */
?>
    <!-- ============ FOOTER ============ -->
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 pb-4">
                <div class="col-lg-4">
                    <h5>ESTATE <span class="text-gold">PRIMA</span></h5>
                    <p class="small mt-2">Platform pencarian dan pengajuan pembelian properti — rumah, apartemen, tanah, dan ruko di lokasi-lokasi pilihan.</p>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="fs-6">Jelajahi</h5>
                    <ul class="list-unstyled mt-2 d-flex flex-column gap-2">
                        <li><a href="<?= BASE_URL ?>index.php">Beranda</a></li>
                        <li><a href="<?= BASE_URL ?>listing.php">Semua Properti</a></li>
                        <li><a href="<?= BASE_URL ?>kontak.php">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="fs-6">Akun</h5>
                    <ul class="list-unstyled mt-2 d-flex flex-column gap-2">
                        <li><a href="<?= BASE_URL ?>login.php">Masuk</a></li>
                        <li><a href="<?= BASE_URL ?>register.php">Daftar</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="fs-6">Kontak</h5>
                    <ul class="list-unstyled mt-2 d-flex flex-column gap-2 small">
                        <li><i class="bi bi-geo-alt me-2"></i>Jakarta Selatan, Indonesia</li>
                        <li><i class="bi bi-envelope me-2"></i>halo@estateprima.test</li>
                    </ul>
                </div>
            </div>
            <hr>
            <p class="small text-center py-3 mb-0">&copy; <?= date('Y') ?> Estate Prima. Dibuat untuk keperluan akademik.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>