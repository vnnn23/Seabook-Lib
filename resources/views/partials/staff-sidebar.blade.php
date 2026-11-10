@php
    $currentUser = Auth::user();
    $isAdmin = $currentUser && $currentUser->role === 'admin';
    $isPetugas = $currentUser && $currentUser->role === 'petugas';
    $currentActive = $active ?? '';

    // Hitung badge notifikasi secara aman
    $pendingMembersCount = 0;
    $waitingChatCount = 0;

    try {
        if ($isAdmin) {
            $pendingMembersCount = \App\Models\User::where('status_approval', 'pending')->count();
        }
        $waitingChatCount = \App\Models\ChatConversation::where('status', 'waiting_staff')->count();
    } catch (\Throwable $e) {
        // Fallback aman jika query gagal
    }
@endphp

<!-- Sidebar Backdrop untuk Mobile -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Collapsible Sidebar -->
<aside class="sidebar" id="sybookStaffSidebar">
    <!-- Header Sidebar -->
    <div class="sidebar-header">
        <a href="{{ $isAdmin ? route('dashboardadmin') : route('dashboardpetugas') }}" class="sidebar-logo">
            <i class="fa-solid fa-book-open-reader"></i>
            <span class="logo-text">Sea<span>Book</span></span>
        </a>
        <button type="button" class="btn-sidebar-toggle" id="sidebarCollapseBtn" title="Buka / Tutup Sidebar" aria-label="Buka atau Tutup Sidebar">
            <i class="fa-solid fa-angles-left"></i>
        </button>
    </div>

    <!-- Menu Navigasi -->
    <nav class="sidebar-nav-menu">
        <ul>
            @if($isAdmin)
                {{-- MENU KHUSUS ADMIN --}}
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboardadmin') }}" class="sidebar-nav-link {{ $currentActive === 'buku' ? 'active' : '' }}" data-tooltip="Daftar Buku">
                        <i class="fa-solid fa-book nav-icon"></i>
                        <span class="nav-text">Daftar Buku</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboardadmin2') }}" class="sidebar-nav-link {{ $currentActive === 'pengguna' ? 'active' : '' }}" data-tooltip="Daftar Pengguna">
                        <i class="fa-solid fa-users nav-icon"></i>
                        <span class="nav-text">Daftar Pengguna</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboardadmin3') }}" class="sidebar-nav-link {{ $currentActive === 'petugas' ? 'active' : '' }}" data-tooltip="Daftar Petugas">
                        <i class="fa-solid fa-user-shield nav-icon"></i>
                        <span class="nav-text">Daftar Petugas</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboardadmin4') }}" class="sidebar-nav-link {{ $currentActive === 'peminjaman' ? 'active' : '' }}" data-tooltip="Peminjaman">
                        <i class="fa-solid fa-clipboard-list nav-icon"></i>
                        <span class="nav-text">Peminjaman</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.members.approval') }}" class="sidebar-nav-link {{ $currentActive === 'approval' ? 'active' : '' }}" data-tooltip="Persetujuan Member">
                        <i class="fa-solid fa-user-check nav-icon"></i>
                        <span class="nav-text">Persetujuan Member</span>
                        @if($pendingMembersCount > 0)
                            <span class="sidebar-badge badge-amber" title="{{ $pendingMembersCount }} menunggu">{{ $pendingMembersCount }}</span>
                        @endif
                    </a>
                </li>

                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboard.index') }}" class="sidebar-nav-link {{ $currentActive === 'statistik' ? 'active' : '' }}" data-tooltip="Statistik & Grafik">
                        <i class="fa-solid fa-chart-pie nav-icon"></i>
                        <span class="nav-text">Statistik & Grafik</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('chat.inbox') }}" class="sidebar-nav-link {{ $currentActive === 'chat' ? 'active' : '' }}" data-tooltip="Live Chat Layanan">
                        <i class="fa-solid fa-headset nav-icon"></i>
                        <span class="nav-text">Live Chat</span>
                        @if($waitingChatCount > 0)
                            <span class="sidebar-badge" title="{{ $waitingChatCount }} pesan menunggu">{{ $waitingChatCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('ulasan.index') }}" class="sidebar-nav-link {{ $currentActive === 'ulasan' ? 'active' : '' }}" data-tooltip="Ulasan Anggota">
                        <i class="fa-solid fa-comments nav-icon"></i>
                        <span class="nav-text">Ulasan Buku</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('peminjaman.exportPdf') }}" class="sidebar-nav-link {{ $currentActive === 'export' ? 'active' : '' }}" data-tooltip="Export Laporan PDF">
                        <i class="fa-solid fa-file-pdf nav-icon"></i>
                        <span class="nav-text">Export PDF</span>
                    </a>
                </li>
            @else
                {{-- MENU KHUSUS PETUGAS --}}
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboardpetugas') }}" class="sidebar-nav-link {{ $currentActive === 'buku' ? 'active' : '' }}" data-tooltip="Daftar Buku">
                        <i class="fa-solid fa-book nav-icon"></i>
                        <span class="nav-text">Daftar Buku</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboardpetugas2') }}" class="sidebar-nav-link {{ $currentActive === 'peminjaman' ? 'active' : '' }}" data-tooltip="Peminjaman">
                        <i class="fa-solid fa-clipboard-list nav-icon"></i>
                        <span class="nav-text">Peminjaman</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('peminjaman.scan.page') }}" class="sidebar-nav-link {{ $currentActive === 'scan' ? 'active' : '' }}" data-tooltip="Scan QR Pickup">
                        <i class="fa-solid fa-qrcode nav-icon"></i>
                        <span class="nav-text">Scan QR Pickup</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboard.index') }}" class="sidebar-nav-link {{ $currentActive === 'statistik' ? 'active' : '' }}" data-tooltip="Statistik & Grafik">
                        <i class="fa-solid fa-chart-pie nav-icon"></i>
                        <span class="nav-text">Statistik & Grafik</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('chat.inbox') }}" class="sidebar-nav-link {{ $currentActive === 'chat' ? 'active' : '' }}" data-tooltip="Live Chat Layanan">
                        <i class="fa-solid fa-headset nav-icon"></i>
                        <span class="nav-text">Live Chat</span>
                        @if($waitingChatCount > 0)
                            <span class="sidebar-badge" title="{{ $waitingChatCount }} pesan menunggu">{{ $waitingChatCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('peminjaman.exportPdf') }}" class="sidebar-nav-link {{ $currentActive === 'export' ? 'active' : '' }}" data-tooltip="Export Laporan PDF">
                        <i class="fa-solid fa-file-pdf nav-icon"></i>
                        <span class="nav-text">Export PDF</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    <!-- Footer Profile Sidebar -->
    <div class="sidebar-footer">
        <div class="sidebar-footer-row">
            <a href="{{ $isAdmin ? route('profile') : route('profile.petugas') }}" class="sidebar-user-card" title="Buka Profil" style="flex: 1; min-width: 0;">
                <div class="sidebar-avatar">
                    @if($currentUser && $currentUser->foto_profil)
                        <img src="{{ asset('storage/' . $currentUser->foto_profil) }}" alt="{{ $currentUser->nama }}">
                    @else
                        {{ substr($currentUser->nama ?? ($isAdmin ? 'Admin' : 'Petugas'), 0, 1) }}
                    @endif
                </div>
                <div class="sidebar-user-info">
                    <h4>{{ $currentUser->nama ?? ($isAdmin ? 'Administrator' : 'Petugas') }}</h4>
                    <p>{{ $isAdmin ? 'Administrator' : 'Petugas Perpustakaan' }}</p>
                </div>
            </a>
            <button type="button" class="sidebar-logout-btn" id="sidebarLogoutTrigger" title="Keluar / Logout" aria-label="Keluar dari akun" data-action="logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </button>
        </div>
    </div>
</aside>

@include('partials.logout-modal')

