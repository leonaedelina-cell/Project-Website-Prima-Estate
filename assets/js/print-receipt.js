// Print one selected transaction receipt while keeping other page content hidden.
function cetakBukti(id) {
    const receipt = document.getElementById('print-receipt-' + id);
    if (!receipt) return;
    receipt.classList.add('printing');
    const cleanup = () => {
        receipt.classList.remove('printing');
        window.removeEventListener('afterprint', cleanup);
    };
    window.addEventListener('afterprint', cleanup);
    window.print();
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-print-receipt]').forEach(function (button) {
        button.addEventListener('click', function () {
            cetakBukti(button.dataset.printReceipt);
        });
    });
});
