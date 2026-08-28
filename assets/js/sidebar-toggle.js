/**
 * sidebar-toggle.js - Estate Prima
 * Dipakai di includes/header.php (sidebar admin & user).
 * Ngatur tombol collapse/expand sidebar (id="sidebarToggleBtn") dan
 * nyimpen pilihan user ke localStorage biar gak balik ke default
 * tiap pindah halaman. Kalau gak ada sidebar di halaman (halaman
 * publik), script ini gak ngapa-ngapain (langsung return).
 */
(function () {
    var sidebar = document.querySelector('.dashboard-sidebar');
    if (!sidebar) return;

    var STORAGE_KEY = 'estateprima-sidebar-collapsed';
    var collapsed = false;
    try { collapsed = localStorage.getItem(STORAGE_KEY) === '1'; } catch (e) {}

    function terapkan(state) {
        sidebar.classList.toggle('collapsed', state);
        document.body.classList.toggle('sidebar-collapsed', state);
    }
    terapkan(collapsed);

    var tombol = document.getElementById('sidebarToggleBtn');
    if (tombol) {
        tombol.addEventListener('click', function () {
            collapsed = !collapsed;
            terapkan(collapsed);
            try { localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0'); } catch (e) {}
        });
    }
})();
