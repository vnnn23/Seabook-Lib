<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - {{ config('app.name', 'SeaBook') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">

    <style>
        /* --- 1. DESIGN SYSTEM & RESET --- */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --text-main: #1e293b;
            --text-sub: #64748b;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            
            /* Status Colors */
            --status-success-bg: #dcfce7;
            --status-success-text: #166534;
            --status-wait-bg: #fef9c3;
            --status-wait-text: #854d0e;
            --status-danger-bg: #fee2e2;
            --status-danger-text: #991b1b;
            --status-info-bg: #e0f2fe;
            --status-info-text: #075985;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body.user-layout {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        .container {
            width: 94%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 10px;
        }

        /* --- 2. NAVBAR (GLASSMORPHISM) --- */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .container {
            width: 92%; max-width: 1280px; margin: 0 auto;
        }

        .nav-content {
            display: flex; justify-content: space-between; align-items: center;
        }

        .logo {
            font-size: 1.5rem; font-weight: 800; color: var(--primary);
            display: flex; align-items: center; gap: 8px;
            letter-spacing: -0.5px; text-decoration: none;
        }
        .logo span { color: var(--text-main); font-weight: 600; font-size: 0.9rem; margin-top: 4px; }

        .nav-links {
            display: flex; align-items: center; gap: 30px;
        }
        .nav-link {
            font-weight: 600; color: var(--text-sub); font-size: 0.95rem;
            transition: color 0.2s; position: relative; text-decoration: none;
        }
        .nav-link:hover, .nav-link.active { color: var(--primary); }

        /* Profile Dropdown */
        .profile-wrapper { position: relative; }
        .avatar-btn {
            width: 42px; height: 42px;
            border-radius: 50%;
            background: var(--primary) no-repeat center / cover;
            color: white; font-weight: 700;
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            overflow: hidden;
            flex-shrink: 0;
        }
        .avatar-btn:hover { transform: scale(1.05); }

        .dropdown-menu {
            position: absolute; top: 55px; right: 0; width: 260px;
            background: white; border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            opacity: 0; visibility: hidden; transform: translateY(-10px);
            transition: all 0.2s ease; overflow: hidden;
        }
        .dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0); }
        
        .dropdown-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 20px; color: white;
        }
        .dropdown-header h4 { font-size: 1rem; margin-bottom: 2px; }
        .dropdown-header p { font-size: 0.8rem; opacity: 0.8; }
        
        .dropdown-item {
            padding: 12px 20px; display: flex; align-items: center; gap: 10px;
            color: var(--text-main); font-weight: 500; cursor: pointer;
            transition: background 0.2s;
        }
        .dropdown-item:hover { background: var(--bg-body); }
        .dropdown-item.danger { color: #ef4444; border-top: 1px solid var(--border); }

        /* --- 3. HEADER --- */
        .page-header {
            margin-top: 40px;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            margin-top: 8px;
            color: var(--text-sub);
            font-size: 0.95rem;
        }

        /* --- 4. MODERN CARD LIST --- */
        .loan-card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .loan-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-color: #cbd5e1;
        }

        /* Left: Book Cover */
        .card-image {
            width: 80px;
            height: 110px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            background-color: #cbd5e1;
            flex-shrink: 0;
        }

        /* Middle: Info */
        .card-content {
            flex: 1;
        }

        .book-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .book-author {
            font-size: 0.9rem;
            color: var(--text-sub);
            margin-bottom: 12px;
            font-weight: 500;
        }

        .meta-info {
            display: flex;
            gap: 15px;
            font-size: 0.85rem;
            color: var(--text-sub);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
        }

        /* Right: Actions & Status */
        .card-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            min-width: 150px;
        }

        /* Status Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge.wait { background: var(--status-wait-bg); color: var(--status-wait-text); }
        .badge.active { background: var(--status-info-bg); color: var(--status-info-text); }
        .badge.danger { background: var(--status-danger-bg); color: var(--status-danger-text); }
        .badge.success { background: var(--status-success-bg); color: var(--status-success-text); }
        .badge.info { background: var(--status-info-bg); color: var(--status-info-text); }

        /* Action Buttons */
        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: white;
            color: var(--text-sub);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .action-btn.primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .action-btn.primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .badge.info { background: var(--status-info-bg); color: var(--status-info-text); }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn.primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .btn.primary:hover { background-color: var(--primary-dark); transform: scale(1.02); }

        /* Detail Modal Styles */
        .detail-modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center; align-items: center;
            z-index: 1000;
        }

        .detail-modal-box {
            background: white;
            width: 500px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .detail-modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 30px 25px;
            color: white;
            position: relative;
        }

        .detail-modal-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .detail-modal-close {
            position: absolute; top: 20px; right: 20px;
            background: rgba(255,255,255,0.2); border: none; 
            font-size: 1.5rem; color: white; cursor: pointer;
            border-radius: 8px; width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }

        .detail-modal-close:hover {
            background: rgba(255,255,255,0.4);
        }

        .detail-modal-body {
            padding: 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--text-sub);
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-label i {
            color: var(--primary);
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .detail-value {
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.95rem;
            text-align: right;
        }

        .detail-status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .detail-status-badge.pending {
            background-color: var(--status-wait-bg);
            color: var(--status-wait-text);
        }

        .detail-status-badge.ready {
            background-color: var(--status-info-bg);
            color: var(--status-info-text);
        }

        .detail-status-badge.borrowed {
            background-color: var(--status-info-bg);
            color: var(--status-info-text);
        }

        .detail-status-badge.returned {
            background-color: var(--status-success-bg);
            color: var(--status-success-text);
        }

        .detail-status-badge.rejected {
            background-color: var(--status-danger-bg);
            color: var(--status-danger-text);
        }

        .detail-modal-footer {
            padding: 20px 30px;
            background-color: #f8fafc;
            display: flex; gap: 10px;
            border-top: 1px solid var(--border);
        }

        .btn-detail-print {
            flex: 1;
            background-color: var(--text-main);
            color: white;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .btn-detail-print:hover {
            background-color: #0f172a;
        }

        /* Print-Only Footer inside modal (Hidden on screen) */
        .print-footer-mark { display: none; }

        /* --- 5. MODAL TICKET STYLE --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center; align-items: center;
            z-index: 1000;
        }

        .ticket-modal {
            background: white;
            width: 380px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Ticket Header */
        .ticket-header {
            background: var(--primary);
            padding: 25px;
            text-align: center;
            color: white;
            position: relative;
        }

        .ticket-logo { font-size: 1.2rem; font-weight: 800; letter-spacing: -0.5px; opacity: 0.9; }
        .ticket-title { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; opacity: 0.8; }
        
        .close-modal {
            position: absolute; top: 20px; right: 20px;
            color: white; background: rgba(255,255,255,0.2);
            border: none; width: 30px; height: 30px; border-radius: 50%;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .close-modal:hover { background: rgba(255,255,255,0.4); }

        /* Ticket Body */
        .ticket-body { padding: 30px 25px; }

        .info-row {
            display: flex; justify-content: space-between;
            margin-bottom: 15px; font-size: 0.9rem;
        }
        .info-label { color: var(--text-sub); }
        .info-value { font-weight: 600; color: var(--text-main); text-align: right; max-width: 60%; }

        /* Dashed Divider */
        .ticket-divider {
            border-top: 2px dashed #e2e8f0;
            margin: 20px 0;
        }

        /* Code Box */
        .code-display {
            background: var(--primary-light);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            border: 2px dashed #bfdbfe;
            margin-top: 10px;
        }
        .code-label { font-size: 0.75rem; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.5px; }
        .code-value { font-size: 1.8rem; font-weight: 800; color: var(--primary); font-family: 'Courier New', monospace; letter-spacing: 2px; margin-top: 5px; }

        /* Ticket Footer */
        .ticket-footer {
            padding: 20px 25px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
        }
        .btn-print {
            width: 100%;
            justify-content: center;
            background: var(--text-main);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: flex; gap: 8px; align-items: center;
            transition: background 0.2s;
        }
        .btn-print:hover { background: black; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-sub);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #cbd5e1;
        }

        .empty-state-text {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        /* --- 6. RESPONSIVE --- */
        @media (max-width: 768px) {
            .loan-card { flex-direction: column; align-items: flex-start; }
            .card-image { width: 100%; height: 150px; object-position: top; }
            .card-actions { width: 100%; align-items: stretch; margin-top: 15px; }
            .ticket-modal { width: 90%; max-width: 380px; }
        }

        /* --- 7. ADVANCED PRINT STYLES --- */
        @media print {
            @page { margin: 2cm; size: A4; } /* Ukuran kertas standar */

            body {
                background-color: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-size: 12pt;
            }

            /* Sembunyikan semua kecuali modal yang aktif */
            body * { visibility: hidden; }

            /* --- CONFIG UNTUK DETAIL MODAL SAAT PRINT --- */
            .detail-modal-overlay, .detail-modal-box, .detail-modal-box * {
                visibility: visible;
            }

            .detail-modal-overlay {
                position: absolute; left: 0; top: 0;
                width: 100%; height: auto; display: block !important;
                background: white; z-index: 9999; padding: 0;
            }

            .detail-modal-box {
                position: relative; left: 0; top: 0;
                width: 100%; max-width: 100%;
                box-shadow: none; border: none;
                border-radius: 0; margin: 0; padding: 0;
                animation: none;
            }

            /* Ubah Header Modal Menjadi Header Dokumen Resmi */
            .detail-modal-header {
                background: white !important;
                color: black !important;
                border-bottom: 2px solid #2563eb;
                padding: 0 0 15px 0;
                margin-bottom: 20px;
                display: flex; justify-content: space-between; align-items: center;
            }

            .detail-modal-title {
                font-size: 24pt; color: #2563eb !important;
                text-align: left;
            }
            
            .detail-modal-header::after {
                content: "SeaBook";
                font-size: 14pt; color: #64748b; font-weight: 700;
                text-transform: uppercase; letter-spacing: 1px;
            }

            /* Sembunyikan Tombol Close & Footer */
            .detail-modal-close, .detail-modal-footer, .btn-detail-print {
                display: none !important;
            }

            /* Body Dokumen */
            .detail-modal-body { padding: 0; }

            .detail-row {
                border-bottom: 1px solid #e2e8f0; padding: 12px 0;
                display: flex; align-items: center;
            }

            .detail-label {
                color: #64748b; width: 40%; font-weight: normal;
            }
            .detail-label i { display: none; }

            .detail-value {
                color: #000; width: 60%; text-align: right;
                font-weight: bold; font-size: 12pt;
            }

            /* Status Badge versi Print (Hitam Putih / Outline) */
            .detail-status-badge {
                background: white !important;
                border: 1px solid #000;
                color: #000 !important;
                padding: 4px 10px;
            }

            /* Tampilkan Footer Khusus Print */
            .print-footer-mark {
                display: block !important; visibility: visible;
                margin-top: 50px; padding-top: 10px;
                border-top: 1px dashed #cbd5e1;
                text-align: center; color: #94a3b8; font-size: 9pt; font-style: italic;
            }

            /* --- CONFIG UNTUK NOTA / TICKET SAAT PRINT --- */
            .ticket-modal, .ticket-modal * { visibility: visible; }
            .ticket-modal {
                position: absolute; left: 0; top: 0;
                border: 2px solid #000; width: 100%; max-width: 100%;
                box-shadow: none; border-radius: 0;
            }
            .ticket-header { background: white; color: black; border-bottom: 2px solid black; }
            .ticket-logo, .ticket-title { color: black; }
            .code-display { border-color: black; background: white; border-width: 2px; }
            .code-value { color: black; }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-sub);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #cbd5e1;
        }

        .empty-state-text {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--text-main);
        }

            .detail-modal-title {
                color: #000;
            }
        }
    </style>
    @include('partials.motion')
</head>
<body class="user-layout">
    @include('partials.user-sidebar', ['active' => 'riwayat'])

    <div class="user-main-content">
        <!-- Topbar User -->
        <header class="user-topbar">
            <div class="user-topbar-left">
                <button type="button" class="btn-topbar-mobile-toggle" id="sidebarMobileToggleBtn" aria-label="Buka Menu Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="user-page-title">Riwayat Pinjaman</h1>
            </div>
            <div class="user-topbar-right">
                @auth
                    <span class="role-pill role-user"><i class="fa-solid fa-user"></i> {{ Auth::user()->nama }}</span>
                @else
                    <a href="{{ route('login') }}" class="role-pill" style="background:#2563eb; color:white; border-color:#2563eb; text-decoration:none;"><i class="fa-solid fa-right-to-bracket"></i> Masuk</a>
                @endauth
            </div>
        </header>

        <div class="container" style="margin-top: 25px; margin-bottom: 50px;">
        <div class="page-header">
            <h1 class="page-title">Daftar Riwayat Pinjaman</h1>
            <p class="page-subtitle">Kembalikan Buku Dengan Tepat Waktu</p>
            <div class="divider"></div>
        </div>

        @forelse($peminjaman as $p)
            <div class="loan-card">
                <!-- Left: Book Cover -->
                @if($p->buku && $p->buku->cover)
                    <img src="{{ asset('storage/' . $p->buku->cover) }}" alt="{{ $p->buku->judul ?? 'Buku' }}" class="card-image">
                @else
                    <div class="card-image" style="display: flex; align-items: center; justify-content: center; background-color: #cbd5e1; color: #666;">
                        <i class="fa-solid fa-book" style="font-size: 2rem;"></i>
                    </div>
                @endif

                <!-- Middle: Book Info -->
                <div class="card-content">
                    <h3 class="book-title">{{ $p->buku->judul ?? 'Buku terhapus' }}</h3>
                    <p class="book-author">{{ $p->buku->penulis ?? '-' }}</p>
                    <div class="meta-info">
                        <div class="meta-item">
                            <i class="fa-solid fa-calendar-check"></i>
                            {{ $p->tanggal_pinjam->format('d M Y H:i') }}
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-hourglass-end"></i>
                            {{ $p->durasi }} hari
                        </div>
                        @if($p->statuspeminjaman === 'dikembalikan')
                            <div class="badge success">
                                <i class="fa-solid fa-check-circle"></i>
                                Selesai
                            </div>
                        @elseif($p->statuspeminjaman === 'menunggu persetujuan')
                            <div class="badge wait">
                                <i class="fa-solid fa-hourglass-start"></i>
                                Menunggu Persetujuan
                            </div>
                        @elseif($p->statuspeminjaman === 'disetujui')
                            <div class="badge info">
                                <i class="fa-solid fa-check-double"></i>
                                Disetujui
                            </div>
                        @elseif($p->statuspeminjaman === 'sedang dipinjam')
                            @if(now()->gt($p->tanggal_kembali))
                                <div class="badge danger">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    Melewati Tenggat
                                </div>
                            @else
                                <div class="badge active">
                                    <i class="fa-solid fa-book-open"></i>
                                    Sedang Dipinjam
                                </div>
                            @endif
                        @elseif($p->statuspeminjaman === 'menunggu konfirmasi pengembalian')
                            <div class="badge wait">
                                <i class="fa-solid fa-hourglass-start"></i>
                                Menunggu Konfirmasi Pengembalian
                            </div>
                        @elseif($p->statuspeminjaman === 'ditolak')
                            <div class="badge danger">
                                <i class="fa-solid fa-circle-xmark"></i>
                                Ditolak
                            </div>
                        @elseif($p->statuspeminjaman === 'dibatalkan')
                            <div class="badge" style="background: #f1f5f9; color: #64748b;">
                                <i class="fa-solid fa-ban"></i>
                                Dibatalkan
                            </div>
                        @elseif($p->statuspeminjaman === 'kadaluarsa')
                            <div class="badge danger">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                Kadaluarsa
                            </div>
                        @else
                            <div class="badge info">
                                <i class="fa-solid fa-clock"></i>
                                {{ ucfirst($p->statuspeminjaman) }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="card-actions">
                    <button onclick='showDetail({{ $p->PeminjamanID }}, {!! json_encode($p->buku->judul ?? 'Buku terhapus') !!}, {!! json_encode($p->buku->penulis ?? '-') !!}, {!! json_encode($p->buku->penerbit ?? '-') !!}, {!! json_encode($p->statuspeminjaman) !!}, {!! json_encode($p->tanggal_pinjam->format('d M Y H:i')) !!}, {!! json_encode($p->tanggal_kembali->format('d M Y H:i')) !!}, {!! json_encode($p->durasi) !!})' class="action-btn" title="Lihat Detail">
                        <i class="fa-solid fa-circle-info"></i>
                        Detail
                    </button>

                    @if($p->statuspeminjaman === 'disetujui')
                        <button onclick='showNota({!! json_encode($p->buku->judul ?? 'Buku terhapus') !!}, {!! json_encode($p->buku->penulis ?? '-') !!}, {!! json_encode($p->qr_code) !!})' class="action-btn primary">
                            <i class="fa-solid fa-qrcode"></i>
                            Tunjukkan Kode
                        </button>
                    @elseif($p->statuspeminjaman === 'sedang dipinjam')
                        <form action="{{ route('peminjaman.kembalikan', $p->PeminjamanID) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="action-btn primary">
                                <i class="fa-solid fa-undo"></i>
                                Kembalikan
                            </button>
                        </form>
                    @endif

                    @if(in_array($p->statuspeminjaman, ['menunggu persetujuan', 'disetujui']))
                        <form action="{{ route('peminjaman.cancel.user', $p->PeminjamanID) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan peminjaman ini?');">
                            @csrf
                            <button type="submit" class="action-btn" style="color: #dc2626; border-color: #fecaca; background: #fff5f5;">
                                <i class="fa-solid fa-ban"></i>
                                Batal
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <p class="empty-state-text">Belum Ada Riwayat Peminjaman</p>
                <p>Mulai pinjam buku favorit Anda sekarang</p>
            </div>
        @endforelse

    </div> <!-- /.container -->
    </div> <!-- /.user-main-content -->

    <div id="notaModal" class="modal-overlay">
        <div class="ticket-modal">
            
            <button class="close-modal" onclick="closeNota()">
                <i class="fa-solid fa-times"></i>
            </button>

            <div id="printableArea">
                <div class="ticket-header">
                    <div class="ticket-logo">SeaBook</div>
                    <div class="ticket-title">Bukti Reservasi Buku</div>
                </div>

                <div class="ticket-body">
                    <div class="info-row">
                        <span class="info-label">Tanggal</span>
                        <span class="info-value" id="notaDate">31 Jan 2026</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Peminjam</span>
                        <span class="info-value">{{ Auth::user()->nama ?? 'Guest User' }}</span>
                    </div>
                    
                    <div class="ticket-divider"></div>

                    <div class="info-row">
                        <span class="info-label">Judul Buku</span>
                        <span class="info-value" id="notaTitle">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Penulis</span>
                        <span class="info-value" id="notaAuthor">-</span>
                    </div>

                    <div class="code-display">
                        <div class="code-label">Kode Pengambilan</div>
                        <div class="code-value" id="notaCode">------</div>
                    </div>

                    <p style="text-align: center; font-size: 0.75rem; color: var(--text-sub); margin-top: 15px; font-style: italic;">
                        Tunjukkan nota ini kepada petugas perpustakaan untuk pengambilan buku.
                    </p>
                </div>
            </div>

            <div class="ticket-footer">
                <button onclick="window.print()" class="btn-print">
                    <i class="fa-solid fa-print"></i> Cetak Nota
                </button>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="detail-modal-overlay">
        <div class="detail-modal-box">
            <div class="detail-modal-header">
                <h2 class="detail-modal-title">Detail Peminjaman</h2>
                <button class="detail-modal-close" onclick="closeDetail()">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <div class="detail-modal-body">
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fa-solid fa-book"></i>
                        Judul Buku
                    </span>
                    <span class="detail-value" id="detailTitle">-</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fa-solid fa-pen"></i>
                        Penulis
                    </span>
                    <span class="detail-value" id="detailAuthor">-</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fa-solid fa-building"></i>
                        Penerbit
                    </span>
                    <span class="detail-value" id="detailPublisher">-</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fa-solid fa-calendar-check"></i>
                        Tanggal Pinjam
                    </span>
                    <span class="detail-value" id="detailBorrowDate">-</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        Tanggal Kembali
                    </span>
                    <span class="detail-value" id="detailReturnDate">-</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fa-solid fa-hourglass-end"></i>
                        Durasi
                    </span>
                    <span class="detail-value" id="detailDuration">-</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fa-solid fa-circle-info"></i>
                        Status
                    </span>
                    <span class="detail-status-badge" id="detailStatus">-</span>
                </div>

                <div class="print-footer-mark">
                    Dokumen ini dicetak otomatis dari sistem SeaBook Perpustakaan Digital.<br>
                    Harap simpan sebagai bukti peminjaman yang sah.
                </div>
            </div>

            <div class="detail-modal-footer">
                <button onclick="printDetail()" class="btn-detail-print">
                    <i class="fa-solid fa-print"></i> Cetak Detail
                </button>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Buka Detail Modal
        function showDetail(peminjamanId, title, author, publisher, status, borrowDate, returnDate, duration) {
            document.getElementById('detailTitle').innerText = title;
            document.getElementById('detailAuthor').innerText = author;
            document.getElementById('detailPublisher').innerText = publisher;
            document.getElementById('detailBorrowDate').innerText = borrowDate;
            document.getElementById('detailReturnDate').innerText = returnDate;
            document.getElementById('detailDuration').innerText = duration + ' hari';
            
            // Set status dengan badge
            const statusBadge = document.getElementById('detailStatus');
            statusBadge.className = 'detail-status-badge';
            
            if (status === 'menunggu persetujuan') {
                statusBadge.textContent = 'Menunggu Persetujuan Petugas';
                statusBadge.classList.add('pending');
            } else if (status === 'disetujui') {
                statusBadge.textContent = 'Disetujui';
                statusBadge.classList.add('ready');
            } else if (status === 'sedang dipinjam') {
                statusBadge.textContent = 'Sedang Dipinjam';
                statusBadge.classList.add('borrowed');
            } else if (status === 'menunggu konfirmasi pengembalian') {
                statusBadge.textContent = 'Menunggu Konfirmasi Pengembalian';
                statusBadge.classList.add('pending');
            } else if (status === 'dikembalikan') {
                statusBadge.textContent = 'Pinjaman Selesai';
                statusBadge.classList.add('returned');
            } else if (status === 'ditolak') {
                statusBadge.textContent = 'Ditolak Oleh Petugas';
                statusBadge.classList.add('rejected');
            } else if (status === 'dibatalkan') {
                statusBadge.textContent = 'Dibatalkan';
                statusBadge.classList.add('rejected');
            } else if (status === 'kadaluarsa') {
                statusBadge.textContent = 'Kadaluarsa';
                statusBadge.classList.add('rejected');
            }
            
            document.getElementById('detailModal').style.display = 'flex';
        }

        // Fungsi Tutup Detail Modal
        function closeDetail() {
            document.getElementById('detailModal').style.display = 'none';
        }

        // Print Detail
        function printDetail() {
            window.print();
        }

        // Tutup modal jika klik di luar box
        document.getElementById('detailModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeDetail();
            }
        });

        // Fungsi Buka Modal & Isi Data Dinamis
        function showNota(title, author, code) {
            // Set Data ke dalam elemen HTML
            document.getElementById('notaTitle').innerText = title;
            document.getElementById('notaAuthor').innerText = author;
            document.getElementById('notaCode').innerText = code;
            
            // Set Tanggal Hari ini
            const today = new Date();
            const dateStr = today.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            document.getElementById('notaDate').innerText = dateStr;

            // Tampilkan Modal
            document.getElementById('notaModal').style.display = 'flex';
        }

        // Fungsi Tutup Modal
        function closeNota() {
            document.getElementById('notaModal').style.display = 'none';
        }

        // Tutup jika klik di luar box
        window.onclick = function(event) {
            const modal = document.getElementById('notaModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

    <script src="{{ asset('js/sidebar-collapsible.js') }}"></script>
    @include('partials.chat-widget')
    @include('partials.logout-modal')
</body>
</html>
