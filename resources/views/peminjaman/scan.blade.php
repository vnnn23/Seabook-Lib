<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scan QR Code - {{ config('app.name', 'SeaBook') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sybook-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">

    <script>
        if (localStorage.getItem('sybook_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    @include('partials.motion')
</head>
<body class="staff-layout">

    <!-- Collapsible Staff Sidebar -->
    @include('partials.staff-sidebar', ['active' => 'scan'])

    <!-- Main Content Area -->
    <div class="staff-main-content">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <h2 class="staff-page-title">Scan QR Pickup Buku</h2>
            </div>
            <div class="staff-topbar-right">
                <span class="role-pill {{ Auth::user()->role === 'admin' ? 'role-admin' : 'role-petugas' }}">
                    <i class="fa-solid {{ Auth::user()->role === 'admin' ? 'fa-user-shield' : 'fa-id-badge' }}"></i>
                    {{ ucfirst(Auth::user()->role) }}
                </span>
            </div>
        </header>

        <section class="section-common" style="padding: 30px;">
            <div class="container" style="max-width: 620px; margin: 30px auto; padding: 30px; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Validasi Kode Pickup QR</h3>
                        <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b;">Masukkan kode peminjaman atau scan QR barcode pemustaka.</p>
                    </div>
                </div>
                
                @if(session('success'))
                    <div style="background: #e8f5e9; color: #2e7d32; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div style="background: #ffebee; color: #c62828; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('peminjaman.scan') }}" method="POST" id="scanForm">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label for="kode_peminjaman" style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">
                            Kode Peminjaman / Barcode:
                        </label>
                        <input type="text" id="kode_peminjaman" name="kode_peminjaman" placeholder="Contoh: PINJ-XXXXXX..." required autofocus 
                               style="width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 1rem; outline: none;">
                    </div>
                    <button type="submit" class="btn-primary" 
                            style="width: 100%; padding: 12px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-check"></i> Konfirmasi Pengambilan Buku
                    </button>
                </form>
            </div>
        </section>
    </div>

    <!-- Collapsible Sidebar Controller Script -->
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>
