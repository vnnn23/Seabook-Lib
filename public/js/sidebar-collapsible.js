/**
 * SyBook - Collapsible Sidebar & Staff Layout Controller
 * Mendukung toggle collapse di Desktop (icon-only 76px) dan Drawer di Mobile (<992px)
 * dengan sinkronisasi ke localStorage dan resize event untuk Chart.js.
 */
(function () {
    'use strict';

    const STORAGE_KEY = 'sybook_sidebar_collapsed';

    // 1. Terapkan state tersimpan secepat mungkin
    function applyInitialState() {
        if (window.innerWidth >= 992) {
            const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
            if (isCollapsed) {
                document.body.classList.add('sidebar-collapsed');
            } else {
                document.body.classList.remove('sidebar-collapsed');
            }
        } else {
            // Pada layar mobile, sidebar selalu default tertutup
            document.body.classList.remove('sidebar-collapsed');
            document.body.classList.remove('sidebar-mobile-open');
        }
    }

    // Jalankan segera jika body sudah ada, atau saat DOMContentLoaded
    if (document.body) {
        applyInitialState();
    } else {
        document.addEventListener('DOMContentLoaded', applyInitialState);
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyInitialState();

        // 2. Tombol Toggle Sidebar (Header / Topbar / Sidebar)
        function toggleSidebar(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const isDesktop = window.innerWidth >= 992;

            if (isDesktop) {
                // Desktop: Toggle Collapsed (260px <-> 76px)
                const willCollapse = !document.body.classList.contains('sidebar-collapsed');
                if (willCollapse) {
                    document.body.classList.add('sidebar-collapsed');
                    localStorage.setItem(STORAGE_KEY, 'true');
                } else {
                    document.body.classList.remove('sidebar-collapsed');
                    localStorage.setItem(STORAGE_KEY, 'false');
                }

                // Beritahu Chart.js dan elemen responsif lainnya agar menyesuaikan ukuran
                setTimeout(function () {
                    window.dispatchEvent(new Event('resize'));
                }, 320);
            } else {
                // Mobile: Toggle Drawer
                const isOpen = document.body.classList.contains('sidebar-mobile-open');
                if (isOpen) {
                    document.body.classList.remove('sidebar-mobile-open');
                } else {
                    document.body.classList.add('sidebar-mobile-open');
                }
            }
        }

        // Bind event listeners ke semua tombol toggle (sidebar desktop & mobile topbar)
        const toggleButtons = document.querySelectorAll('.btn-sidebar-toggle, #sidebarCollapseBtn, .btn-topbar-mobile-toggle, #sidebarMobileToggleBtn, .btn-topbar-toggle');
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', toggleSidebar);
        });

        // 3. Tutup mobile drawer saat backdrop diklik
        const backdrop = document.querySelector('.sidebar-backdrop, #sidebarBackdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function () {
                document.body.classList.remove('sidebar-mobile-open');
            });
        }

        // 4. Tutup mobile drawer dengan tombol ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.body.classList.contains('sidebar-mobile-open')) {
                document.body.classList.remove('sidebar-mobile-open');
            }
        });

        // 5. Handle resize window
        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (window.innerWidth >= 992) {
                    // Tutup mobile drawer jika berpindah ke desktop
                    document.body.classList.remove('sidebar-mobile-open');
                    const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
                    if (isCollapsed) {
                        document.body.classList.add('sidebar-collapsed');
                    } else {
                        document.body.classList.remove('sidebar-collapsed');
                    }
                } else {
                    // Mobile tidak menggunakan mode icon-only
                    document.body.classList.remove('sidebar-collapsed');
                }
            }, 100);
        });
    });
})();

