function toggleSewaInput() {
    const select = document.getElementById('tipe_transaksi');
    const container = document.getElementById('container_sewa');
    const duration = document.getElementById('durasi_sewa');
    if (!select || !container || !duration) return;
    const sewa = select.value === 'sewa';
    container.style.display = sewa ? '' : 'none';
    duration.required = sewa;
}
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('tipe_transaksi');
    if (select) select.addEventListener('change', toggleSewaInput);
    toggleSewaInput();
});
