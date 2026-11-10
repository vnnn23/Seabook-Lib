/**
 * SyBook Library - Logout Confirmation Modal Handler
 * Intercepts logout triggers, displays styled modal, and handles submission smoothly.
 */

(function () {
    'use strict';

    let pendingLogoutForm = null;

    /**
     * Ensure logout modal elements exist in the DOM.
     * If not already present via Blade partial, injects fallback HTML dynamically.
     */
    function ensureModalExists() {
        let modal = document.getElementById('sybookLogoutModal');
        if (!modal) {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const tokenInput = document.querySelector('input[name="_token"]');
            const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : (tokenInput ? tokenInput.value : '');

            const modalHtml = `
                <div id="sybookLogoutModal" class="sybook-logout-overlay" aria-hidden="true" role="dialog" aria-labelledby="sybookLogoutTitle">
                    <div class="sybook-logout-card" role="document">
                        <button type="button" class="sybook-logout-close" id="sybookLogoutClose" aria-label="Tutup dialog">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <div class="sybook-logout-icon-wrap">
                            <div class="sybook-logout-icon">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </div>
                        </div>
                        <h3 class="sybook-logout-title" id="sybookLogoutTitle">Konfirmasi Logout</h3>
                        <p class="sybook-logout-desc">
                            Apakah Anda yakin ingin keluar dari akun SyBook? Sesi aktif Anda akan berakhir dan Anda perlu masuk kembali untuk mengakses layanan perpustakaan.
                        </p>
                        <div class="sybook-logout-actions">
                            <button type="button" class="btn-logout-cancel" id="sybookLogoutCancelBtn">
                                Batal
                            </button>
                            <button type="button" class="btn-logout-confirm" id="sybookLogoutConfirmBtn">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Ya, Logout
                            </button>
                        </div>
                    </div>
                </div>
                <form id="sybookGlobalLogoutForm" action="/logout" method="POST" style="display: none;">
                    <input type="hidden" name="_token" value="${csrfToken}">
                </form>
            `;
            const container = document.createElement('div');
            container.innerHTML = modalHtml;
            while (container.firstChild) {
                document.body.appendChild(container.firstChild);
            }
            modal = document.getElementById('sybookLogoutModal');
        }
        return modal;
    }

    /**
     * Open confirmation modal
     */
    function openModal(associatedForm) {
        const modal = ensureModalExists();
        if (!modal) return;

        pendingLogoutForm = associatedForm || document.getElementById('sybookGlobalLogoutForm') || document.getElementById('logout-form');

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        const cancelBtn = document.getElementById('sybookLogoutCancelBtn');
        if (cancelBtn) {
            setTimeout(() => cancelBtn.focus(), 50);
        }
    }

    /**
     * Close confirmation modal
     */
    function closeModal() {
        const modal = document.getElementById('sybookLogoutModal');
        if (!modal) return;

        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingLogoutForm = null;
    }

    /**
     * Confirm and submit logout
     */
    function confirmLogout() {
        const confirmBtn = document.getElementById('sybookLogoutConfirmBtn');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengeluarkan...';
        }

        // Cari form logout yang tersedia
        let formToSubmit = pendingLogoutForm;
        if (!formToSubmit) {
            formToSubmit = document.getElementById('sybookGlobalLogoutForm') ||
                           document.getElementById('logout-form') ||
                           document.querySelector('form[action*="logout"]');
        }

        if (formToSubmit && typeof formToSubmit.submit === 'function') {
            formToSubmit.submit();
        } else {
            // Fallback submit form dinamis
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const tokenInput = document.querySelector('input[name="_token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : (tokenInput ? tokenInput.value : '');

            const fallbackForm = document.createElement('form');
            fallbackForm.method = 'POST';
            fallbackForm.action = '/logout';
            fallbackForm.style.display = 'none';

            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = token;
            fallbackForm.appendChild(csrfField);

            document.body.appendChild(fallbackForm);
            fallbackForm.submit();
        }
    }

    // Expose API globally
    window.SyBookLogout = {
        open: openModal,
        close: closeModal,
        confirm: confirmLogout
    };

    /**
     * Bind DOM events
     */
    document.addEventListener('DOMContentLoaded', function () {
        ensureModalExists();

        // 1. Delegasi klik tombol Batal & Close
        document.addEventListener('click', function (e) {
            const cancelBtn = e.target.closest('#sybookLogoutCancelBtn, #cancelLogout');
            const closeBtn = e.target.closest('#sybookLogoutClose, #closeLogout');
            const confirmBtn = e.target.closest('#sybookLogoutConfirmBtn, #confirmLogoutBtn');
            const overlay = e.target.closest('.sybook-logout-overlay');

            if (cancelBtn || closeBtn) {
                e.preventDefault();
                closeModal();
                return;
            }

            if (confirmBtn) {
                e.preventDefault();
                confirmLogout();
                return;
            }

            // Klik di luar kartu modal (overlay background)
            if (overlay && e.target === overlay) {
                closeModal();
                return;
            }

            // 2. Klik pada trigger logout (tombol profil admin, sidebar, dll)
            const trigger = e.target.closest('[data-action="logout"], .btn-logout, .btn-logout-trigger, #btnLogoutTrigger, #sidebarLogoutTrigger');
            if (trigger) {
                e.preventDefault();
                const parentForm = trigger.closest('form');
                openModal(parentForm);
            }
        });

        // 3. Intercept submit form logout di dropdown user / mana pun
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form && form.action && form.action.includes('/logout')) {
                // Jika bukan disubmit lewat konfirmasi popup
                if (form.getAttribute('data-confirmed') !== 'true') {
                    e.preventDefault();
                    form.setAttribute('data-confirmed', 'true');
                    openModal(form);
                }
            }
        });

        // 4. Tombol Escape untuk menutup modal
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('sybookLogoutModal');
                if (modal && modal.classList.contains('show')) {
                    closeModal();
                }
            }
        });
    });
})();

