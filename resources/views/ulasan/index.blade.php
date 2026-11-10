<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ulasan - {{ config('app.name', 'SeaBook') }} Admin</title>
    
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

        .content-body { padding: 30px; }

        .card{background: var(--surface); padding: 20px; border-radius: 12px; border: 1px solid var(--border);} 
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{padding:10px;border-bottom:1px solid #f1f5f9;text-align:left}
        th{background:#fbfeff}
        .badge{padding:6px 8px;border-radius:8px;background:#eef2ff;color:#1e40af;font-weight:700}
        .empty{color:var(--text-sub);text-align:center;padding:28px}
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

    @include('partials.staff-sidebar', ['active' => 'ulasan'])

    <main class="main-content">
        <header class="top-header" style="background: var(--surface); padding: 16px 30px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800;">Daftar Ulasan Buku</h3>
            </div>
            <div style="font-weight: 600; color: var(--text-sub);">{{ \Carbon\Carbon::now()->format('d M Y') }}</div>
        </header>

        <div class="content-body">
            <div class="card">
                <h2>Daftar Ulasan</h2>
                <p style="color:var(--text-sub)">Menampilkan seluruh ulasan dari pengguna.</p>

                <div style="overflow:auto; margin-top:12px">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:60px">No</th>
                                <th>Pengguna</th>
                                <th>Buku</th>
                                <th style="width:90px">Rating</th>
                                <th>Isi Ulasan</th>
                                <th style="width:140px">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ulasans as $i => $u)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $u->user->nama ?? 'Pengguna terhapus' }}</td>
                                <td>{{ $u->buku->judul ?? 'Buku terhapus' }}</td>
                                <td><span class="badge">{{ $u->rating }}/5</span></td>
                                <td>{{ \Illuminate\Support\Str::limit($u->isi_ulasan, 200) }}</td>
                                <td>{{ $u->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="empty">Belum ada ulasan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>
