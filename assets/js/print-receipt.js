/**
 * print-receipt.js - Estate Prima
 * Dipakai di admin-transaksi-detail.php dan pesanan.php.
 * Satu halaman bisa punya lebih dari satu struk tersembunyi
 * (`.print-receipt`, lihat assets/css/style.css bagian "Struk Cetak"),
 * tapi yang boleh muncul pas print cuma SATU - yang barusan diklik.
 * Caranya: tempelin class "printing" ke struk yang dipilih sebelum
 * window.print() dipanggil, terus dicopot lagi begitu dialog print
 * ditutup (event "afterprint") biar gak nyangkut ke print berikutnya.
 */
function cetakBukti(id) {
    document.querySelectorAll('.print-receipt').forEach(function (el) {
        el.classList.remove('printing');
    });
    var target = document.getElementById('print-receipt-' + id);
    if (!target) return;
    target.classList.add('printing');
    window.print();
}

window.addEventListener('afterprint', function () {
    document.querySelectorAll('.print-receipt.printing').forEach(function (el) {
        el.classList.remove('printing');
    });
});
