// Toggle and persist the dashboard sidebar state across pages.
(function () {
    const sidebars = document.querySelectorAll('.dashboard-sidebar');
    if (!sidebars.length) return;
    let collapsed = localStorage.getItem('estateprima-sidebar-collapsed') === '1';
    const setState = (value) => {
        const active = window.innerWidth >= 1200 && value;
        sidebars.forEach((sidebar) => {
            sidebar.classList.toggle('collapsed', active);
            const toggle = sidebar.querySelector('.sidebar-toggle');
            if (toggle) {
                toggle.setAttribute('aria-label', active ? 'Lebarkan sidebar' : 'Ciutkan sidebar');
                toggle.innerHTML = '<i class="bi bi-chevron-double-' + (active ? 'right' : 'left') + '"></i>';
            }
        });
        document.body.classList.toggle('sidebar-collapsed', active);
    };
    setState(collapsed);
    window.addEventListener('resize', () => setState(collapsed));
    sidebars.forEach((sidebar) => {
        const toggle = sidebar.querySelector('.sidebar-toggle');
        if (toggle) toggle.addEventListener('click', () => {
            collapsed = !sidebar.classList.contains('collapsed');
            localStorage.setItem('estateprima-sidebar-collapsed', collapsed ? '1' : '0');
            setState(collapsed);
        });
    });
})();
