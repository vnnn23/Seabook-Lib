<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna - {{ config('app.name', 'SeaBook') }} Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- 1. RESET & VARIABLES --- */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border: #e2e8f0;
            --sidebar-width: 260px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --radius: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); display: flex; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* --- 2. SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed; height: 100vh; top: 0; left: 0;
            display: flex; flex-direction: column;
            z-index: 100;
        }

        .sidebar-header {
            padding: 24px;
            display: flex; align-items: center; gap: 10px;
            border-bottom: 1px solid var(--border);
        }
        .logo-text { font-size: 1.5rem; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; }

        .nav-menu { padding: 20px 15px; flex: 1; }
        .nav-item { margin-bottom: 5px; }
        
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 8px;
            color: var(--text-sub); font-weight: 600; font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .nav-link:hover { background-color: var(--bg-body); color: var(--primary); }
        
        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        .nav-link i { width: 20px; text-align: center; font-size: 1.1rem; }

        .sidebar-footer {
            padding: 20px; border-top: 1px solid var(--border);
        }
        .user-profile {
            display: flex; align-items: center; gap: 10px;
        }
        .avatar {
            width: 40px; height: 40px; background: var(--primary); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        .user-info h4 { font-size: 0.9rem; margin-bottom: 2px; }
        .user-info p { font-size: 0.75rem; color: var(--text-sub); }

        /* --- 3. MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1; display: flex; flex-direction: column;
            width: calc(100% - var(--sidebar-width));
        }

        /* Top Header */
        .top-header {
            background: var(--surface);
            padding: 16px 30px;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 90;
        }

        .search-wrapper {
            position: relative; width: 400px;
        }
        .search-wrapper input {
            width: 100%; padding: 10px 15px 10px 40px;
            border-radius: 8px; border: 1px solid var(--border);
            background: var(--bg-body); outline: none; transition: 0.2s;
        }
        .search-wrapper input:focus { border-color: var(--primary); background: white; }
        .search-wrapper i {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--text-sub);
        }

        /* Content Body */
        .content-body { padding: 30px; }

        /* Alerts */
        .alert {
            padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        /* --- 4. TABLE STYLES (NEW) --- */
        .section-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; color: var(--text-main); }
        .section-subtitle { font-size: 0.9rem; color: var(--text-sub); margin-bottom: 20px; }

        .table-container {
            background: white; border-radius: 16px; border: 1px solid var(--border);
            overflow: hidden; box-shadow: var(--shadow-sm);
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            background: #f8fafc; text-align: left; padding: 16px;
            font-weight: 700; color: var(--text-sub); font-size: 0.85rem;
            text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px; border-bottom: 1px solid var(--border);
            color: var(--text-main); font-size: 0.95rem; vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fcfcfc; }

        /* User Info in Table */
        .user-cell { display: flex; align-items: center; gap: 12px; }
        .user-avatar-sm {
            width: 32px; height: 32px; background: #e2e8f0; color: #64748b;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 700;
        }
        .user-name { font-weight: 600; color: var(--text-main); }
        .user-email { font-size: 0.85rem; color: var(--text-sub); }

        /* Action Buttons */
        .btn-delete {
            background: #fef2f2; border: 1px solid #fee2e2; color: #dc2626;
            padding: 8px 14px; border-radius: 8px; cursor: pointer;
            font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
            transition: 0.2s;
        }
        .btn-delete:hover { background: #fee2e2; border-color: #fca5a5; }

        /* Status Badges & Action Button */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.76rem;
            font-weight: 700;
        }
        .badge-status.badge-pending {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .badge-status.badge-ditolak {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .badge-status.badge-aktif {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .btn-proses-approval {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff !important;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
            transition: all 0.2s ease;
        }
        .btn-proses-approval:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
            color: #ffffff !important;
            text-decoration: none !important;
        }

        .empty-state { text-align: center; padding: 40px; color: var(--text-sub); }

    </style>
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">
    <script>
        if (localStorage.getItem('sybook_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    @include('partials.motion')
</head>
<body class="staff-layout">

    @include('partials.staff-sidebar', ['active' => 'pengguna'])

    <main class="main-content">
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 14px;">
                <form method="GET" action="{{ route('dashboardadmin2') }}" class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pengguna atau email...">
                </form>
            </div>
            <div style="font-weight: 600; color: var(--text-sub);">
                {{ \Carbon\Carbon::now()->format('d M Y') }}
            </div>
        </header>

        <div class="content-body">
            @php $users = $users ?? collect(); @endphp
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <div style="margin-bottom: 25px;">
                <h2 class="section-title" style="margin-bottom: 5px;">Daftar Pengguna</h2>
                <p class="section-subtitle">Kelola data pengguna yang terdaftar di sistem.</p>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Pengguna</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                        <tr>
                            <td style="text-align: center; color: var(--text-sub);">{{ $index + 1 }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm">
                                        {{ substr($user->nama, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $user->nama }}</div>
                                        <div class="user-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $user->nomor_telepon ?? '-' }}
                            </td>
                            <td>
                                @if(isset($user->status_akun) && $user->status_akun === 'pending')
                                    <div style="display: inline-flex; align-items: center; gap: 8px;">
                                        <span class="badge-status badge-pending">
                                            <i class="fa-solid fa-hourglass-half"></i> Menunggu
                                        </span>
                                        <a href="{{ route('admin.members.approval') }}" class="btn-proses-approval" title="Tinjau dan proses persetujuan akun">
                                            <i class="fa-solid fa-user-check"></i> Proses
                                        </a>
                                    </div>
                                @elseif(isset($user->status_akun) && $user->status_akun === 'ditolak')
                                    <span class="badge-status badge-ditolak">
                                        <i class="fa-solid fa-circle-xmark"></i> Ditolak
                                    </span>
                                @else
                                    <span class="badge-status badge-aktif">
                                        <i class="fa-solid fa-circle-check"></i> Aktif
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <form action="{{ route('user.destroy', $user->UserID) }}" method="POST" onsubmit="return confirm('Hapus pengguna \"{{ $user->nama }}\"?');" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-delete" title="Hapus Pengguna" type="submit">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="fa-solid fa-users-slash" style="font-size: 2rem; margin-bottom: 10px;"></i>
                                <p>Tidak ada data pengguna ditemukan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 20px; color: var(--text-sub); font-size: 0.9rem; text-align: right;">
                Menampilkan {{ $users->count() }} pengguna
            </div>

        </div>
    </main>

    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>