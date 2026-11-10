<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Poin & Tinjau Akun - {{ config('app.name', 'SeaBook') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sybook-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">

    <script>
        if (localStorage.getItem('sybook_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>

    <style>
        :root {
            --primary: #2563eb;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-sub: #64748b;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-body); color: var(--text-main); margin: 0; }
        .container-admin { padding: 28px 32px; max-width: 1400px; width: 100%; margin: 0 auto; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 15px; }
        .page-title { font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.2px; }
        .page-subtitle { font-size: 0.88rem; color: var(--text-sub); margin: 0; line-height: 1.5; }
        .table-card { background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .card-header { padding: 14px 20px; border-bottom: 1px solid var(--border); background: #f8fafc; font-weight: 700; font-size: 0.95rem; color: var(--text-main); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 18px; border-bottom: 1px solid var(--border); }
        th { background: #f8fafc; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-sub); }
        td { font-size: 0.88rem; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .badge-aman { background: #dcfce7; color: #166534; }
        .badge-peringatan { background: #fef3c7; color: #92400e; }
        .badge-tinjau { background: #fee2e2; color: #991b1b; }
        .badge-pending { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-ditolak { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-poin-pending { display: inline-flex; align-items: center; gap: 5px; color: #d97706; font-weight: 700; font-size: 0.95rem; }
        .btn-proses-approval {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white !important;
            font-size: 0.82rem;
            padding: 7px 14px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }
        .btn-proses-approval:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
            color: white !important;
            text-decoration: none !important;
        }
        .btn { padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; border: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-warning { background: #d97706; color: white; }
        .btn-outline { background: white; border: 1px solid var(--border); color: var(--text-main); }
        .alert { padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-success { background: #e8f5e9; color: #1e7e34; border: 1px solid #c3e6cb; }

        .filter-tab {
            display: inline-flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .filter-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: white;
            color: var(--text-main);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
    @include('partials.motion')
</head>
<body class="staff-layout">

    <!-- Collapsible Staff Sidebar -->
    @include('partials.staff-sidebar', ['active' => 'poin'])

    <!-- Main Content Area -->
    <div class="staff-main-content">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <h2 class="staff-page-title">Manajemen Poin & Evaluasi Akun Member</h2>
            </div>
            <div class="staff-topbar-right">
                <span class="role-pill role-admin">
                    <i class="fa-solid fa-user-shield"></i> Administrator
                </span>
            </div>
        </header>

        <div class="container-admin">
        <div class="header-section">
            <div>
                <h1 class="page-title">Manajemen Poin Pelanggaran & Tinjau Akun Anggota</h1>
                <p class="page-subtitle">
                    Setiap akun anggota memiliki skor awal 100 poin. Anggota dengan skor &le; 60 poin otomatis berstatus <strong>Tinjau Akun</strong>.
                </p>
            </div>
            <div>
                <form action="{{ route('admin.poin.index') }}" method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..." style="padding: 8px 14px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem;">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Cari</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Filter Tab -->
        <div class="filter-tab" style="flex-wrap: wrap;">
            <a href="{{ route('admin.poin.index') }}" class="filter-btn {{ !request('filter') ? 'active' : '' }}">
                Semua Anggota
            </a>
            <a href="{{ route('admin.poin.index', ['filter' => 'tinjau']) }}" class="filter-btn {{ request('filter') === 'tinjau' ? 'active' : '' }}" style="{{ $totalTinjau > 0 ? 'border-color: #ef4444; color: #dc2626;' : '' }}">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Perlu Ditinjau
                @if($totalTinjau > 0)
                    <span style="background: #dc2626; color: white; border-radius: 10px; padding: 1px 7px; font-size: 0.75rem; margin-left: 4px;">{{ $totalTinjau }}</span>
                @endif
            </a>
            <a href="{{ route('admin.poin.index', ['filter' => 'peringatan']) }}" class="filter-btn {{ request('filter') === 'peringatan' ? 'active' : '' }}">
                Peringatan (61 - 75 Poin)
            </a>
            <a href="{{ route('admin.poin.index', ['filter' => 'aman']) }}" class="filter-btn {{ request('filter') === 'aman' ? 'active' : '' }}">
                Akun Sehat (> 75 Poin)
            </a>
            <a href="{{ route('admin.poin.index', ['filter' => 'pending']) }}" class="filter-btn {{ request('filter') === 'pending' ? 'active' : '' }}" style="{{ $totalPending > 0 ? 'border-color: #f59e0b; color: #b45309;' : '' }}">
                <i class="fa-solid fa-hourglass-half"></i>
                Menunggu Persetujuan
                @if($totalPending > 0)
                    <span style="background: #f59e0b; color: white; border-radius: 10px; padding: 1px 7px; font-size: 0.75rem; margin-left: 4px;">{{ $totalPending }}</span>
                @endif
            </a>
        </div>

        <div class="table-card">
            <div class="card-header">
                <span>Daftar Anggota, Skor Poin & Status Akun</span>
                <span style="font-weight: normal; font-size: 0.85rem; color: var(--text-sub);">
                    Default: 100 Poin | Peringatan: &le; 75 Poin | Batas Tinjau Akun: &le; 60 Poin
                </span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Anggota</th>
                        <th>Email</th>
                        <th>Skor Poin Akun</th>
                        <th>Status Akun</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $u)
                        @php
                            $isPending = ($u->status_akun === \App\Models\User::STATUS_PENDING);
                            $isDitolak = ($u->status_akun === \App\Models\User::STATUS_DITOLAK);
                            $userPoin = (int) ($u->poin ?? 0);
                            $ambangTinjau = (int) config('perpustakaan.ambang_tinjau', 60);
                            $ambangPeringatan = (int) config('perpustakaan.ambang_peringatan', 75);
                            $isTinjau = (!$isPending && !$isDitolak) && (($userPoin <= $ambangTinjau) || ($u->status_akun === \App\Models\User::STATUS_TINJAU));
                            $isPeringatan = (!$isPending && !$isDitolak && !$isTinjau) && ($userPoin <= $ambangPeringatan);
                        @endphp
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td style="font-weight: 600;">{{ $u->nama }}</td>
                            <td style="color: var(--text-sub);">{{ $u->email }}</td>
                            <td>
                                @if($isPending)
                                    <div class="badge-poin-pending">
                                        <i class="fa-solid fa-hourglass-half"></i> Belum Ada Poin
                                    </div>
                                    <span style="font-size: 0.75rem; color: var(--text-sub); display: block; margin-top: 2px;">(Menunggu Persetujuan)</span>
                                @elseif($isDitolak)
                                    <span style="color: #ef4444; font-weight: 700; font-size: 1.05rem;">0</span>
                                    <span style="font-size: 0.75rem; color: var(--text-sub); display: block;">(Akun Ditolak)</span>
                                @else
                                    <strong style="font-size: 1.25rem; color: {{ $isTinjau ? '#dc2626' : ($isPeringatan ? '#d97706' : '#16a34a') }};">
                                        {{ $userPoin }}
                                    </strong>
                                    <span style="font-size: 0.85rem; color: var(--text-sub);">/ 100 Poin</span>
                                @endif
                            </td>
                            <td>
                                @if($isPending)
                                    <span class="badge badge-pending">
                                        <i class="fa-solid fa-clock"></i> Menunggu Persetujuan
                                    </span>
                                @elseif($isDitolak)
                                    <span class="badge badge-ditolak">
                                        <i class="fa-solid fa-circle-xmark"></i> Pendaftaran Ditolak
                                    </span>
                                @elseif($isTinjau)
                                    <span class="badge badge-tinjau">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Tinjau Akun
                                    </span>
                                @elseif($isPeringatan)
                                    <span class="badge badge-peringatan">
                                        <i class="fa-solid fa-bell"></i> Peringatan
                                    </span>
                                @else
                                    <span class="badge badge-aman">
                                        <i class="fa-solid fa-circle-check"></i> Aktif & Aman
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($isPending)
                                    <a href="{{ route('admin.members.approval') }}" class="btn-proses-approval" title="Buka Halaman Persetujuan Member">
                                        <i class="fa-solid fa-user-check"></i> Proses Akun
                                    </a>
                                @elseif($isDitolak)
                                    <a href="{{ route('admin.members.approval') }}" class="btn btn-outline" style="font-size: 0.82rem; padding: 6px 14px;">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                                    </a>
                                @else
                                    <a href="{{ route('admin.poin.show', $u->UserID) }}" class="btn {{ $isTinjau ? 'btn-warning' : 'btn-primary' }}" style="font-size: 0.82rem; padding: 6px 14px;">
                                        <i class="fa-solid fa-user-check"></i> Tinjau Akun
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-sub); padding: 35px;">
                                Belum ada anggota yang terdaftar pada kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($users->hasPages())
                <div style="padding: 15px 20px;">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
    </div> <!-- Close staff-main-content -->
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>
