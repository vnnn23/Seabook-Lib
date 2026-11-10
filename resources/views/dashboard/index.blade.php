<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Statistik & Metrik - {{ config('app.name', 'SeaBook') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Collapsible Sidebar Style & Staff Layout -->
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">

    <!-- Chart.js Pinned Version 4.4.1 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        // Cegah layout flash jika preferensi sidebar tersimpan adalah collapsed
        if (localStorage.getItem('sybook_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border: #e2e8f0;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --card-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
            --card-hover-shadow: 0 12px 28px -4px rgba(37, 99, 235, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); }
        a { text-decoration: none; color: inherit; }

        .dashboard-container {
            padding: 28px 32px;
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
        }

        /* Header Banner & Title */
        .dash-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }
        .dash-title-group h1 {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .dash-title-group p {
            color: var(--text-sub);
            font-size: 0.88rem;
            margin-top: 4px;
        }

        .dash-badge-live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .live-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
            animation: livePulse 2s infinite;
        }
        @keyframes livePulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Filter Section (Modern Glass / Card) */
        .filter-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px 24px;
            box-shadow: var(--card-shadow);
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .filter-presets {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .preset-label {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-sub);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 4px;
        }
        .preset-btn {
            padding: 7px 14px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: #f8fafc;
            color: var(--text-sub);
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .preset-btn:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: #bfdbfe;
        }
        .preset-btn.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .date-input-group {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 4px 10px;
            gap: 8px;
            transition: border-color 0.2s;
        }
        .date-input-group:focus-within {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .date-input-group label {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-sub);
            text-transform: uppercase;
        }
        .date-input-group input[type="date"] {
            border: none;
            background: transparent;
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--text-main);
            outline: none;
            padding: 4px 2px;
        }

        .btn-apply-filter {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: var(--radius-sm);
            font-size: 0.86rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transition: all 0.2s;
        }
        .btn-apply-filter:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }

        .btn-reset-filter {
            background: #f1f5f9;
            color: var(--text-sub);
            border: 1px solid var(--border);
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            font-size: 0.86rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-reset-filter:hover {
            background: #e2e8f0;
            color: var(--text-main);
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 22px;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-hover-shadow);
            border-color: #cbd5e1;
        }
        .kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .kpi-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-sub);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }
        .kpi-card:hover .kpi-icon {
            transform: scale(1.08);
        }

        .kpi-icon.blue { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color: #2563eb; }
        .kpi-icon.purple { background: linear-gradient(135deg, #faf5ff 0%, #ede9fe 100%); color: #7c3aed; }
        .kpi-icon.green { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); color: #059669; }
        .kpi-icon.orange { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); color: #ea580c; }
        .kpi-icon.amber { background: linear-gradient(135deg, #fefce8 0%, #fef08a 100%); color: #ca8a04; }
        .kpi-icon.red { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); color: #dc2626; }
        .kpi-icon.slate { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); color: #475569; }

        .kpi-value {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1.1;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .kpi-footer {
            font-size: 0.78rem;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .kpi-pill {
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.72rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .kpi-pill.pill-green { background: #dcfce7; color: #166534; }
        .kpi-pill.pill-orange { background: #ffedd5; color: #9a3412; }
        .kpi-pill.pill-red { background: #fee2e2; color: #991b1b; }

        .kpi-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #2563eb;
            background: #eff6ff;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none !important;
            border: 1px solid #dbeafe;
            transition: all 0.2s ease;
        }
        .kpi-action-btn:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            text-decoration: none !important;
        }

        /* Chart Cards & Grids */
        .charts-row-main {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        .charts-row-triple {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            position: relative;
            min-height: 380px;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .chart-card:hover {
            box-shadow: var(--card-hover-shadow);
            border-color: #cbd5e1;
        }
        .chart-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .chart-card-title-group h3 {
            font-size: 1.08rem;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .chart-card-title-group p {
            font-size: 0.8rem;
            color: var(--text-sub);
            margin-top: 3px;
        }
        .chart-tag {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .chart-wrapper {
            flex: 1;
            position: relative;
            min-height: 270px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Empty State */
        .chart-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 30px 20px;
            color: var(--text-sub);
            gap: 12px;
        }
        .empty-icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #94a3b8;
        }
        .chart-empty-state h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }
        .chart-empty-state p {
            font-size: 0.82rem;
            margin: 0;
            max-width: 260px;
        }

        /* Modern Table Card */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }
        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        .table-card-title-group h3 {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-card-title-group p {
            font-size: 0.82rem;
            color: var(--text-sub);
            margin-top: 3px;
        }
        .btn-view-all {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            background: var(--primary-light);
            transition: all 0.2s;
        }
        .btn-view-all:hover {
            background: #dbeafe;
        }

        .modern-table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid #f1f5f9;
        }
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
        }
        .modern-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
        }
        .modern-table td {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.88rem;
            vertical-align: middle;
        }
        .modern-table tbody tr:hover td {
            background: #f8fafc;
        }
        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-cell-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bfdbfe;
        }
        .user-cell-info h5 {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }
        .user-cell-info p {
            font-size: 0.76rem;
            color: var(--text-sub);
            margin: 0;
        }

        .badge-poin-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge-poin-status.danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-poin-status.warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-poin-status.safe { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        @media (max-width: 1200px) {
            .charts-row-main { grid-template-columns: 1fr; }
        }
        @media (max-width: 991px) {
            .dashboard-container { padding: 20px 16px; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-row-triple { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .filter-panel { flex-direction: column; align-items: stretch; }
            .filter-form { flex-direction: column; align-items: stretch; }
            .date-input-group { width: 100%; }
        }
    </style>
    @include('partials.motion')
</head>
<body class="staff-layout">

    <!-- Collapsible Staff Sidebar -->
    @include('partials.staff-sidebar', ['active' => 'statistik'])

    <!-- Main Content Area -->
    <div class="staff-main-content">
        <!-- Top Navigation Bar -->
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <h2 class="staff-page-title">Statistik & Metrik Perpustakaan</h2>
            </div>
            <div class="staff-topbar-right">
                <a href="{{ route('chat.inbox') }}" class="btn-reset-filter" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.84rem; text-decoration: none;">
                    <i class="fa-solid fa-headset" style="color: #2563eb;"></i> Live Chat
                </a>
                <span class="role-pill {{ Auth::user()->role === 'admin' ? 'role-admin' : 'role-petugas' }}">
                    <i class="fa-solid {{ Auth::user()->role === 'admin' ? 'fa-user-shield' : 'fa-id-badge' }}"></i>
                    {{ ucfirst(Auth::user()->role) }}
                </span>
            </div>
        </header>

        <!-- Main Body Container -->
        <div class="dashboard-container">
            <!-- Header Section -->
            <div class="dash-header">
                <div class="dash-title-group">
                    <h1>
                        <i class="fa-solid fa-chart-simple" style="color: #2563eb;"></i>
                        Statistik & Metrik Perpustakaan
                    </h1>
                    <p>Pemantauan aktivitas peminjaman, stok koleksi buku, dan evaluasi kepatuhan anggota secara real-time.</p>
                </div>
                <div>
                    <span class="dash-badge-live">
                        <span class="live-dot"></span>
                        Data Diperbarui Real-time
                    </span>
                </div>
            </div>

            <!-- Panel Filter Periode -->
            <div class="filter-panel">
                <div class="filter-presets">
                    <span class="preset-label"><i class="fa-solid fa-clock-rotate-left"></i> Preset:</span>
                    <a href="{{ route('dashboard.index') }}" class="preset-btn {{ !$startDate && !$endDate ? 'active' : '' }}">
                        Semua Periode
                    </a>
                    @php
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        $startThisMonth = $now->copy()->startOfMonth()->format('Y-m-d');
                        $endThisMonth = $now->copy()->endOfMonth()->format('Y-m-d');
                        $start30Days = $now->copy()->subDays(30)->format('Y-m-d');
                        $today = $now->format('Y-m-d');
                        $startThisYear = $now->copy()->startOfYear()->format('Y-m-d');
                        $endThisYear = $now->copy()->endOfYear()->format('Y-m-d');
                    @endphp
                    <a href="{{ route('dashboard.index', ['start_date' => $startThisMonth, 'end_date' => $endThisMonth]) }}" 
                       class="preset-btn {{ $startDate === $startThisMonth && $endDate === $endThisMonth ? 'active' : '' }}">
                        Bulan Ini
                    </a>
                    <a href="{{ route('dashboard.index', ['start_date' => $start30Days, 'end_date' => $today]) }}" 
                       class="preset-btn {{ $startDate === $start30Days && $endDate === $today ? 'active' : '' }}">
                        30 Hari Terakhir
                    </a>
                    <a href="{{ route('dashboard.index', ['start_date' => $startThisYear, 'end_date' => $endThisYear]) }}" 
                       class="preset-btn {{ $startDate === $startThisYear && $endDate === $endThisYear ? 'active' : '' }}">
                        Tahun Ini
                    </a>
                </div>

                <form method="GET" action="{{ route('dashboard.index') }}" class="filter-form">
                    <div class="date-input-group">
                        <label for="start_date">Dari</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="date-input-group">
                        <label for="end_date">Sampai</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}">
                    </div>
                    <button type="submit" class="btn-apply-filter">
                        <i class="fa-solid fa-filter"></i> Terapkan
                    </button>
                    @if($startDate || $endDate)
                        <a href="{{ route('dashboard.index') }}" class="btn-reset-filter" title="Hapus Filter">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- KPI Summary Cards -->
            <div class="kpi-grid">
                <!-- Total Judul -->
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-title">Total Judul</span>
                        <div class="kpi-icon blue"><i class="fa-solid fa-book"></i></div>
                    </div>
                    <div class="kpi-value">{{ number_format($summary['total_judul']) }}</div>
                    <div class="kpi-footer">
                        <span>Koleksi judul terdaftar</span>
                    </div>
                </div>

                <!-- Total Eksemplar -->
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-title">Total Eksemplar</span>
                        <div class="kpi-icon purple"><i class="fa-solid fa-layer-group"></i></div>
                    </div>
                    <div class="kpi-value">{{ number_format($summary['total_eksemplar']) }}</div>
                    <div class="kpi-footer">
                        <span>Fisik buku keseluruhan</span>
                    </div>
                </div>

                <!-- Tersedia -->
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-title">Buku Tersedia</span>
                        <div class="kpi-icon green"><i class="fa-solid fa-circle-check"></i></div>
                    </div>
                    <div class="kpi-value" style="color: #059669;">{{ number_format($summary['tersedia']) }}</div>
                    <div class="kpi-footer">
                        @php
                            $persenTersedia = $summary['total_eksemplar'] > 0 
                                ? round(($summary['tersedia'] / $summary['total_eksemplar']) * 100, 1) 
                                : 0;
                        @endphp
                        <span class="kpi-pill pill-green"><i class="fa-solid fa-arrow-up"></i> {{ $persenTersedia }}% Siap Pinjam</span>
                    </div>
                </div>

                <!-- Dipinjam -->
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-title">Sedang Dipinjam</span>
                        <div class="kpi-icon orange"><i class="fa-solid fa-book-bookmark"></i></div>
                    </div>
                    <div class="kpi-value" style="color: #ea580c;">{{ number_format($summary['dipinjam']) }}</div>
                    <div class="kpi-footer">
                        @php
                            $persenDipinjam = $summary['total_eksemplar'] > 0 
                                ? round(($summary['dipinjam'] / $summary['total_eksemplar']) * 100, 1) 
                                : 0;
                        @endphp
                        <span class="kpi-pill pill-orange"><i class="fa-solid fa-hand-holding"></i> {{ $persenDipinjam }}% Sirkulasi</span>
                    </div>
                </div>

                <!-- Menunggu Persetujuan -->
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-title">Menunggu</span>
                        <div class="kpi-icon amber"><i class="fa-solid fa-hourglass-half"></i></div>
                    </div>
                    <div class="kpi-value" style="color: #ca8a04;">{{ number_format($summary['menunggu_persetujuan']) }}</div>
                    <div class="kpi-footer">
                        <span>Permintaan peminjaman baru</span>
                    </div>
                </div>

                <!-- Terlambat -->
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-title">Terlambat</span>
                        <div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                    <div class="kpi-value" style="color: #dc2626;">{{ number_format($summary['terlambat']) }}</div>
                    <div class="kpi-footer">
                        @if($summary['terlambat'] > 0)
                            <span class="kpi-pill pill-red"><i class="fa-solid fa-bell"></i> Butuh Tindakan</span>
                        @else
                            <span class="kpi-pill pill-green"><i class="fa-solid fa-check"></i> Tidak ada keterlambatan</span>
                        @endif
                    </div>
                </div>

                <!-- Akun Pending (Khusus Admin) -->
                @if(isset($summary['akun_pending']))
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-title">Akun Pending</span>
                        <div class="kpi-icon slate"><i class="fa-solid fa-user-clock"></i></div>
                    </div>
                    <div class="kpi-value" style="color: #475569;">{{ number_format($summary['akun_pending']) }}</div>
                    <div class="kpi-footer">
                        <a href="{{ route('admin.members.approval') }}" class="kpi-action-btn">
                            Tinjau persetujuan <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Baris 1 Grafik: Tren Bulanan & 10 Buku Populer -->
            <div class="charts-row-main">
                <!-- Grafik Tren Garis -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div class="chart-card-title-group">
                            <h3><i class="fa-solid fa-chart-line" style="color: #2563eb;"></i> Tren Peminjaman Buku</h3>
                            <p>Fluktuasi jumlah peminjaman dalam 12 bulan terakhir</p>
                        </div>
                        <span class="chart-tag">Sirkulasi</span>
                    </div>
                    <div class="chart-wrapper">
                        @if($charts['tren_peminjaman']['empty'])
                            <div class="chart-empty-state">
                                <div class="empty-icon-circle"><i class="fa-solid fa-chart-line"></i></div>
                                <h4>Belum Ada Aktivitas</h4>
                                <p>Belum ada data peminjaman buku yang tercatat pada periode ini.</p>
                            </div>
                        @else
                            <canvas id="chartTren"></canvas>
                        @endif
                    </div>
                </div>

                <!-- Grafik Batang Horizontal: Top 10 Buku -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div class="chart-card-title-group">
                            <h3><i class="fa-solid fa-ranking-star" style="color: #f59e0b;"></i> 10 Buku Terpopuler</h3>
                            <p>Judul dengan frekuensi peminjaman tertinggi</p>
                        </div>
                        <span class="chart-tag">Paling Banyak Dibaca</span>
                    </div>
                    <div class="chart-wrapper">
                        @if($charts['buku_terpopuler']['empty'])
                            <div class="chart-empty-state">
                                <div class="empty-icon-circle"><i class="fa-solid fa-book-open"></i></div>
                                <h4>Data Belum Tersedia</h4>
                                <p>Belum ada buku yang dipinjam pada rentang waktu yang dipilih.</p>
                            </div>
                        @else
                            <canvas id="chartTopBooks"></canvas>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Baris 2 Grafik: Donat Status, Donat Stok, dan Batang Kategori -->
            <div class="charts-row-triple">
                <!-- Status Peminjaman -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div class="chart-card-title-group">
                            <h3><i class="fa-solid fa-chart-pie" style="color: #7c3aed;"></i> Status Transaksi</h3>
                            <p>Proporsi status seluruh peminjaman</p>
                        </div>
                        <span class="chart-tag">Distribusi</span>
                    </div>
                    <div class="chart-wrapper">
                        @if($charts['status_peminjaman']['empty'])
                            <div class="chart-empty-state">
                                <div class="empty-icon-circle"><i class="fa-solid fa-chart-pie"></i></div>
                                <h4>Belum Ada Transaksi</h4>
                                <p>Tidak ditemukan rekaman transaksi peminjaman.</p>
                            </div>
                        @else
                            <canvas id="chartStatus"></canvas>
                        @endif
                    </div>
                </div>

                <!-- Stok Tersedia vs Dipinjam -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div class="chart-card-title-group">
                            <h3><i class="fa-solid fa-boxes-stacked" style="color: #059669;"></i> Rasio Stok Buku</h3>
                            <p>Perbandingan buku di rak vs di tangan peminjam</p>
                        </div>
                        <span class="chart-tag">Inventaris</span>
                    </div>
                    <div class="chart-wrapper">
                        @if($charts['stok_tersedia_dipinjam']['empty'])
                            <div class="chart-empty-state">
                                <div class="empty-icon-circle"><i class="fa-solid fa-boxes-stacked"></i></div>
                                <h4>Koleksi Kosong</h4>
                                <p>Belum ada eksemplar buku yang tersimpan di sistem.</p>
                            </div>
                        @else
                            <canvas id="chartStok"></canvas>
                        @endif
                    </div>
                </div>

                <!-- Peminjaman per Kategori -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div class="chart-card-title-group">
                            <h3><i class="fa-solid fa-tags" style="color: #0284c7;"></i> Peminjaman per Kategori</h3>
                            <p>Minat baca berdasarkan klasifikasi genre</p>
                        </div>
                        <span class="chart-tag">Kategori</span>
                    </div>
                    <div class="chart-wrapper">
                        @if($charts['peminjaman_kategori']['empty'])
                            <div class="chart-empty-state">
                                <div class="empty-icon-circle"><i class="fa-solid fa-tags"></i></div>
                                <h4>Belum Ada Peminjaman</h4>
                                <p>Belum ada peminjaman buku berdasarkan kategori.</p>
                            </div>
                        @else
                            <canvas id="chartKategori"></canvas>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tabel Anggota dengan Poin Pelanggaran -->
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-card-title-group">
                        <h3>
                            <i class="fa-solid fa-shield-halved" style="color: #dc2626;"></i>
                            Anggota dengan Catatan Poin Pelanggaran
                        </h3>
                        <p>10 pemustaka dengan pengurangan skor tertinggi untuk evaluasi hak pinjam</p>
                    </div>
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.poin.index') }}" class="btn-view-all">
                            Kelola Semua Poin & Sanksi <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @endif
                </div>

                @if($topUsersPoin->isEmpty())
                    <div class="chart-empty-state" style="padding: 40px 20px;">
                        <div class="empty-icon-circle" style="background: #ecfdf5; color: #059669;">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <h4>Semua Anggota Memiliki Catatan Bersih</h4>
                        <p>Tidak ada pemustaka yang tercatat memiliki pengurangan skor pelanggaran saat ini.</p>
                    </div>
                @else
                    <div class="modern-table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Nama Anggota</th>
                                    <th>Email</th>
                                    <th>Skor / Poin Saat Ini</th>
                                    <th>Status Akun</th>
                                    <th>Izin Peminjaman</th>
                                    @if(Auth::user()->role === 'admin')
                                        <th>Tindakan</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUsersPoin as $u)
                                    @php
                                        // Poin pelanggaran pada sistem baru: 100 poin penuh, <= 60 tinjau
                                        $poin = $u->poin_pelanggaran;
                                        $isTinjau = $poin <= config('perpustakaan.ambang_tinjau', 60) || $u->status_akun === 'tinjau';
                                        $isPeringatan = $poin <= config('perpustakaan.ambang_peringatan', 75) && !$isTinjau;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-cell-avatar">
                                                    {{ substr($u->nama ?? 'U', 0, 1) }}
                                                </div>
                                                <div class="user-cell-info">
                                                    <h5>{{ $u->nama }}</h5>
                                                    <p>ID: #{{ $u->UserID }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $u->email }}</td>
                                        <td>
                                            <strong style="font-size: 1rem; color: {{ $isTinjau ? '#dc2626' : ($isPeringatan ? '#d97706' : '#166534') }};">
                                                {{ $poin }}
                                            </strong>
                                            <span style="font-size: 0.78rem; color: #64748b;"> / 100 poin</span>
                                        </td>
                                        <td>
                                            @if($isTinjau)
                                                <span class="badge-poin-status danger"><i class="fa-solid fa-triangle-exclamation"></i> Perlu Ditinjau (≤ 60)</span>
                                            @elseif($isPeringatan)
                                                <span class="badge-poin-status warning"><i class="fa-solid fa-circle-exclamation"></i> Peringatan (≤ 75)</span>
                                            @else
                                                <span class="badge-poin-status safe"><i class="fa-solid fa-circle-check"></i> Normal</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($u->isBlockedFromBorrowing())
                                                <span style="color: #dc2626; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                                                    <i class="fa-solid fa-ban"></i> Terblokir
                                                </span>
                                            @else
                                                <span style="color: #16a34a; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                                                    <i class="fa-solid fa-check"></i> Aktif Diizinkan
                                                </span>
                                            @endif
                                        </td>
                                        @if(Auth::user()->role === 'admin')
                                            <td>
                                                <a href="{{ route('admin.poin.show', $u->UserID) }}" class="btn-view-all" style="padding: 4px 10px; font-size: 0.78rem;">
                                                    Tinjau <i class="fa-solid fa-arrow-right"></i>
                                                </a>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Collapsible Sidebar Controller Script -->
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>

    <!-- Script Inisialisasi Chart.js Modern & Interaktif -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($charts);

            // Global Chart Defaults
            Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
            Chart.defaults.color = '#64748b';

            // 1. Chart Tren Peminjaman Bulanan (Line Chart dengan Gradient Fill Elegan)
            const canvasTren = document.getElementById('chartTren');
            if (canvasTren && !chartData.tren_peminjaman.empty) {
                const ctx = canvasTren.getContext('2d');
                const gradientBlue = ctx.createLinearGradient(0, 0, 0, 300);
                gradientBlue.addColorStop(0, 'rgba(37, 99, 235, 0.35)');
                gradientBlue.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.tren_peminjaman.labels,
                        datasets: [{
                            label: 'Jumlah Peminjaman',
                            data: chartData.tren_peminjaman.data,
                            borderColor: '#2563eb',
                            borderWidth: 3,
                            backgroundColor: gradientBlue,
                            fill: true,
                            tension: 0.38,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#2563eb',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { weight: 'bold', size: 13 },
                                bodyFont: { size: 12 },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + ' buku dipinjam';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, padding: 8 },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { padding: 8 }
                            }
                        }
                    }
                });
            }

            // 2. Chart Top 10 Buku Terpopuler (Horizontal Rounded Bar)
            const canvasTopBooks = document.getElementById('chartTopBooks');
            if (canvasTopBooks && !chartData.buku_terpopuler.empty) {
                const ctx = canvasTopBooks.getContext('2d');
                const gradientBar = ctx.createLinearGradient(0, 0, 400, 0);
                gradientBar.addColorStop(0, '#3b82f6');
                gradientBar.addColorStop(1, '#60a5fa');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.buku_terpopuler.labels,
                        datasets: [{
                            label: 'Total Peminjaman',
                            data: chartData.buku_terpopuler.data,
                            backgroundColor: gradientBar,
                            hoverBackgroundColor: '#2563eb',
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(ctx) {
                                        return ' ' + ctx.raw + ' kali dipinjam';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { precision: 0, padding: 6 },
                                grid: { color: '#f1f5f9' }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    callback: function(val, index) {
                                        const label = this.getLabelForValue(val);
                                        return label.length > 22 ? label.substr(0, 22) + '...' : label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Chart Status Peminjaman (Modern Doughnut)
            const canvasStatus = document.getElementById('chartStatus');
            if (canvasStatus && !chartData.status_peminjaman.empty) {
                const statusColorMap = {
                    'menunggu persetujuan': '#f59e0b',
                    'disetujui': '#06b6d4',
                    'sedang dipinjam': '#3b82f6',
                    'menunggu konfirmasi pengembalian': '#f97316',
                    'dikembalikan': '#10b981',
                    'ditolak': '#ef4444',
                    'dibatalkan': '#94a3b8',
                    'kadaluarsa': '#64748b'
                };
                const bgColors = chartData.status_peminjaman.labels.map(l => statusColorMap[l] || '#94a3b8');

                new Chart(canvasStatus, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.status_peminjaman.labels,
                        datasets: [{
                            data: chartData.status_peminjaman.data,
                            backgroundColor: bgColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    padding: 12,
                                    font: { size: 11, weight: '600' }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 8
                            }
                        }
                    }
                });
            }

            // 4. Chart Rasio Stok Buku (Tersedia vs Dipinjam)
            const canvasStok = document.getElementById('chartStok');
            if (canvasStok && !chartData.stok_tersedia_dipinjam.empty) {
                new Chart(canvasStok, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.stok_tersedia_dipinjam.labels,
                        datasets: [{
                            data: chartData.stok_tersedia_dipinjam.data,
                            backgroundColor: ['#10b981', '#f97316'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    padding: 14,
                                    font: { size: 12, weight: '600' }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 8
                            }
                        }
                    }
                });
            }

            // 5. Chart Peminjaman per Kategori (Vertical Bar)
            const canvasKategori = document.getElementById('chartKategori');
            if (canvasKategori && !chartData.peminjaman_kategori.empty) {
                const ctx = canvasKategori.getContext('2d');
                const gradientKat = ctx.createLinearGradient(0, 0, 0, 250);
                gradientKat.addColorStop(0, '#6366f1');
                gradientKat.addColorStop(1, '#818cf8');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.peminjaman_kategori.labels,
                        datasets: [{
                            label: 'Jumlah Peminjaman',
                            data: chartData.peminjaman_kategori.data,
                            backgroundColor: gradientKat,
                            hoverBackgroundColor: '#4f46e5',
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
