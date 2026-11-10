/**
 * SyBook Motion & Page Transition Engine
 * Pure Vanilla JavaScript - Lightweight, Non-blocking, & Accessible
 */
(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let progressBar = null;
    let progressTimer = null;
    let isNavigating = false;

    // --- 1. Initialize DOM Elements & Listeners ---
    function initMotionElements() {
        if (!document.body) return;

        // Apply entrance animation strictly to inner main content (never body or navbar)
        if (!prefersReducedMotion) {
            const mainContent = document.querySelector('main:not(.navbar), .content-body, .page-content');
            if (mainContent && !mainContent.classList.contains('page-motion-enter')) {
                mainContent.classList.add('page-motion-enter');
                mainContent.addEventListener('animationend', function() {
                    mainContent.classList.remove('page-motion-enter');
                    mainContent.style.transform = '';
                }, { once: true });
            }
        }

        // Inject Progress Bar if not present
        if (!document.getElementById('motion-progress-bar')) {
            progressBar = document.createElement('div');
            progressBar.id = 'motion-progress-bar';
            progressBar.setAttribute('aria-hidden', 'true');
            document.body.appendChild(progressBar);
        } else {
            progressBar = document.getElementById('motion-progress-bar');
        }

        // Handle page component skeletons (e.g. Catalog Book Skeleton Grid)
        revealComponentSkeletons();
    }

    // --- 2. Component Skeleton Transition ---
    function revealComponentSkeletons() {
        // Book Catalog Skeleton Grid
        const skeletonGrid = document.getElementById('bookSkeletonGrid');
        const realGrid = document.getElementById('bookGrid');
        const emptyState = document.getElementById('emptyState');

        if (skeletonGrid) {
            // Briefly display skeleton to avoid layout flash, then reveal content
            setTimeout(function () {
                skeletonGrid.style.transition = 'opacity 0.25s ease';
                skeletonGrid.style.opacity = '0';
                setTimeout(function () {
                    skeletonGrid.style.display = 'none';
                    if (realGrid) {
                        realGrid.style.display = 'grid';
                        realGrid.classList.add('page-motion-enter');
                    }
                    if (emptyState && (!realGrid || realGrid.children.length === 0)) {
                        emptyState.style.display = 'block';
                    }
                }, 200);
            }, 180);
        }

        // Detail Book Skeleton
        const detailSkeleton = document.getElementById('detailBookSkeleton');
        const realDetail = document.getElementById('detailBookReal');
        if (detailSkeleton && realDetail) {
            setTimeout(function () {
                detailSkeleton.style.transition = 'opacity 0.25s ease';
                detailSkeleton.style.opacity = '0';
                setTimeout(function () {
                    detailSkeleton.style.display = 'none';
                    realDetail.style.display = 'grid';
                    realDetail.classList.add('page-motion-enter');
                }, 200);
            }, 180);
        }
    }

    // --- 3. Top Navigation Progress Bar ---
    function startProgressBar() {
        if (isNavigating) return;
        isNavigating = true;

        if (progressTimer) clearTimeout(progressTimer);

        // 150ms delay: only show progress bar if request takes noticeable time
        progressTimer = setTimeout(function () {
            if (progressBar) {
                progressBar.classList.remove('finished');
                progressBar.classList.add('active');
            }
        }, 150);
    }

    function finishProgressBar() {
        if (progressTimer) {
            clearTimeout(progressTimer);
            progressTimer = null;
        }
        isNavigating = false;

        if (progressBar) {
            progressBar.classList.add('finished');
            setTimeout(function () {
                progressBar.classList.remove('active', 'finished');
            }, 250);
        }
    }

    // --- 4. Intercept Internal Link Clicks Safely ---
    function handleLinkClick(e) {
        if (e.defaultPrevented || e.button !== 0) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        const anchor = e.target.closest('a');
        if (!anchor) return;

        // Ignore links with target, download, no-transition, or in-page anchors
        if (anchor.target && anchor.target !== '_self') return;
        if (anchor.hasAttribute('download')) return;
        if (anchor.dataset.noTransition !== undefined) return;

        const href = anchor.getAttribute('href');
        if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) return;

        // Ignore logout links and special forms
        if (href.includes('/logout') || anchor.closest('form')) return;

        let url;
        try {
            url = new URL(anchor.href, window.location.href);
        } catch (_) {
            return;
        }

        if (url.origin !== window.location.origin) return;

        // Same URL except hash -> ignore
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash !== window.location.hash) {
            return;
        }

        startProgressBar();

        // Optional smooth exit animation if reduced-motion is not requested
        if (!prefersReducedMotion && !document.startViewTransition) {
            const mainContent = document.querySelector('main:not(.navbar), .content-body, .page-content');
            if (mainContent) {
                mainContent.classList.add('page-motion-leave');
            }
        }
    }

    // --- 5. Intercept Navigation Forms (e.g. GET Search / Filters) ---
    function handleFormSubmit(e) {
        const form = e.target;
        if (!form) return;

        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (form.dataset.noTransition !== undefined || form.enctype === 'multipart/form-data') {
            return;
        }

        if (method === 'GET') {
            startProgressBar();
        }
    }

    // --- 6. Event Listeners ---
    document.addEventListener('DOMContentLoaded', initMotionElements);
    document.addEventListener('click', handleLinkClick);
    document.addEventListener('submit', handleFormSubmit);

    // Bfcache support
    window.addEventListener('pageshow', function (event) {
        finishProgressBar();
        if (event.persisted) {
            const mainContent = document.querySelector('main, .content-body, .container, body');
            if (mainContent) {
                mainContent.classList.remove('page-motion-leave');
                mainContent.classList.add('page-motion-enter');
            }
        }
    });

    window.addEventListener('pagehide', function () {
        if (progressTimer) clearTimeout(progressTimer);
    });

    // Global utility
    window.SyBookMotion = {
        start: startProgressBar,
        finish: finishProgressBar,
        revealSkeletons: revealComponentSkeletons
    };
})();
