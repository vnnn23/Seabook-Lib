@php
    $currentUser = Auth::user();
    $currentActive = $active ?? '';

    $koleksiCount = 0;
    $activeLoansCount = 0;

    try {
        if ($currentUser) {
            $koleksiCount = \App\Models\KoleksiPribadi::where('UserID', $currentUser->UserID)->count();
            $activeLoansCount = \App\Models\Peminjaman::where('UserID', $currentUser->UserID)
                ->whereIn('statuspeminjaman', \App\Models\Peminjaman::STATUS_AKTIF_LOAN ?? ['menunggu', 'disetujui', 'dipinjam', 'menunggu_pengembalian'])
                ->count();
        }
    } catch (\Throwable $e) {
        // Fallback aman
    }
@endphp

<!-- Sidebar Backdrop untuk Mobile -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Collapsible Sidebar User -->
<aside class="sidebar" id="sybookUserSidebar">
    <!-- Header Sidebar -->
    <div class="sidebar-header">
        <a href="{{ route('books') }}" class="sidebar-logo">
            <i class="fa-solid fa-book-open-reader"></i>
            <span class="logo-text">Sea<span>Book</span></span>
        </a>
        <button type="button" class="btn-sidebar-toggle" id="sidebarCollapseBtn" title="Buka / Tutup Sidebar" aria-label="Buka atau Tutup Sidebar">
            <i class="fa-solid fa-angles-left"></i>
        </button>
    </div>

    <!-- Menu Navigasi User -->
    <nav class="sidebar-nav-menu">
        <ul>
            <li class="sidebar-nav-item">
                <a href="{{ route('books') }}" class="sidebar-nav-link {{ $currentActive === 'katalog' ? 'active' : '' }}" data-tooltip="Katalog Buku">
                    <i class="fa-solid fa-book nav-icon"></i>
                    <span class="nav-text">Katalog Buku</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="{{ route('koleksi.index') }}" class="sidebar-nav-link {{ $currentActive === 'koleksi' ? 'active' : '' }}" data-tooltip="Koleksi Saya">
                    <i class="fa-solid fa-bookmark nav-icon"></i>
                    <span class="nav-text">Koleksi Saya</span>
                    @if($koleksiCount > 0)
                        <span class="sidebar-badge badge-amber" title="{{ $koleksiCount }} buku disimpan">{{ $koleksiCount }}</span>
                    @endif
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="{{ route('peminjaman.riwayat') }}" class="sidebar-nav-link {{ $currentActive === 'riwayat' ? 'active' : '' }}" data-tooltip="Riwayat Pinjaman">
                    <i class="fa-solid fa-clock-rotate-left nav-icon"></i>
                    <span class="nav-text">Riwayat Pinjaman</span>
                    @if($activeLoansCount > 0)
                        <span class="sidebar-badge" title="{{ $activeLoansCount }} pinjaman aktif">{{ $activeLoansCount }}</span>
                    @endif
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="{{ route('profile.edit') }}" class="sidebar-nav-link {{ $currentActive === 'profil' ? 'active' : '' }}" data-tooltip="Profil & Akun">
                    <i class="fa-solid fa-user-gear nav-icon"></i>
                    <span class="nav-text">Profil & Akun</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar Footer Profile -->
    <div class="sidebar-footer">
        @auth
            <div class="sidebar-footer-row">
                <a href="{{ route('profile.edit') }}" class="sidebar-user-card" title="Profil Anggota">
                    <div class="sidebar-avatar">
                        @if($currentUser && $currentUser->foto_profil)
                            <img src="{{ asset('storage/' . $currentUser->foto_profil) }}" alt="{{ $currentUser->nama }}">
                        @else
                            {{ strtoupper(substr($currentUser->nama ?? 'U', 0, 1)) }}
                        @endif
                    </div>
                    <div class="sidebar-user-info">
                        <h4>{{ $currentUser->nama ?? 'Anggota' }}</h4>
                        <p>Anggota Perpustakaan</p>
                    </div>
                </a>
                <button type="button" class="sidebar-logout-btn" data-action="logout" title="Keluar / Logout" aria-label="Keluar">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </div>
        @else
            <div style="padding: 4px;">
                <a href="{{ route('login') }}" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 14px; border-radius: 10px; background: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span class="nav-text">Masuk / Daftar</span>
                </a>
            </div>
        @endauth
    </div>
</aside>
