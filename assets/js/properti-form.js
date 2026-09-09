function toggleTransaksiFields() {
    const transaksi = document.getElementById('tipe_transaksi');
    const rentalFields = document.getElementById('rental_fields');
    const durationContainer = document.getElementById('durasi_minimal_container');
    const durasi = document.getElementById('durasi_minimal');
    const hargaSewa = document.getElementById('harga_sewa');
    const periodeSewa = document.querySelector('select[name="periode_sewa"]');
    const minimalSewa = document.querySelector('input[name="minimal_sewa"]');

    if (!transaksi) return;

    const sewa = transaksi.value === 'sewa';

    if (rentalFields) rentalFields.style.display = sewa ? '' : 'none';
    if (durationContainer) durationContainer.style.display = sewa ? '' : 'none';

    [hargaSewa, periodeSewa, minimalSewa, durasi].forEach((el) => {
        if (!el) return;
        el.disabled = !sewa;
        el.required = sewa;
        if (!sewa) {
            if (el.tagName === 'SELECT') {
                el.value = '';
            } else if (el.type === 'number') {
                el.value = '';
            }
        }
    });

    const hargaJual = document.getElementById('harga_jual');
    if (hargaJual) {
        hargaJual.required = !sewa;
        hargaJual.disabled = sewa;
        if (sewa) hargaJual.value = '';
    }
}

function clearZeroOnFocus() {
    const numbers = document.querySelectorAll('input[type="number"][name="harga"], input[type="number"][name="harga_sewa"], input[type="number"][name="carport"], input[type="number"][name="minimal_sewa"]');
    numbers.forEach((input) => {
        input.addEventListener('focus', function () {
            if (this.value === '0' || this.value === 0) {
                this.value = '';
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const transaksi = document.getElementById('tipe_transaksi');
    if (transaksi) transaksi.addEventListener('change', toggleTransaksiFields);
    toggleTransaksiFields();
    clearZeroOnFocus();
});
