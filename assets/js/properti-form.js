/**
 * properti-form.js - Estate Prima
 * Dipakai di properti-tambah.php dan properti-edit.php.
 * Field "Durasi Minimal Sewa" cuma relevan kalau Tipe Transaksi = Sewa,
 * jadi disembunyikan/dimunculin otomatis pas dropdown-nya ganti.
 */
(function () {
    var tipeTransaksi = document.getElementById('tipe_transaksi');
    var wrapDurasi = document.getElementById('wrap-durasi-minimal');
    if (!tipeTransaksi || !wrapDurasi) return;

    function toggleDurasi() {
        wrapDurasi.style.display = tipeTransaksi.value === 'sewa' ? '' : 'none';
    }
    tipeTransaksi.addEventListener('change', toggleDurasi);
    toggleDurasi();
})();
