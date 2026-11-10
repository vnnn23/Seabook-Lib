<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Petugas - {{ config('app.name', 'SeaBook') }} Admin</title>
    
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

        /* Header Flex for Title & Button */
        .page-header-flex {
            display: flex; justify-content: space-between; align-items: end; margin-bottom: 25px;
        }

        .section-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 5px; color: var(--text-main); }
        .section-subtitle { font-size: 0.9rem; color: var(--text-sub); }

        .btn-primary {
            background: var(--primary); color: white; border: none;
            padding: 10px 20px; border-radius: 8px; font-weight: 600;
            cursor: pointer; display: flex; align-items: center; gap: 8px;
            transition: 0.2s;
        }
        .btn-primary:hover { background: var(--primary-dark); }

        /* --- 4. TABLE STYLES --- */
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
            width: 32px; height: 32px; background: #e0f2fe; color: #0369a1;
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

        .empty-state { text-align: center; padding: 40px; color: var(--text-sub); }

        /* --- 5. MODAL STYLES --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content {
            background: white; width: 400px; max-width: 90%;
            border-radius: 16px; padding: 30px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            position: relative; animation: slideUp 0.3s ease;
        }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .modal-header h3 { font-size: 1.25rem; font-weight: 700; }
        .close-btn { font-size: 1.5rem; cursor: pointer; color: var(--text-sub); background: none; border: none; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; }
        .form-group input {
            width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; outline: none; font-family: inherit;
        }
        .form-group input:focus { border-color: var(--primary); }

        .password-wrapper { position: relative; display: flex; align-items: center; width: 100%; }
        .password-wrapper input { width: 100%; padding-right: 42px !important; }
        .password-toggle-btn { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 6px; font-size: 1rem; display: inline-flex; align-items: center; justify-content: center; transition: color 0.2s ease; z-index: 2; }
        .password-toggle-btn:hover { color: var(--primary); }

        .btn-submit {
            width: 100%; padding: 12px; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 10px;
        }
        .btn-submit:hover { background: var(--primary-dark); }

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

    @include('partials.staff-sidebar', ['active' => 'petugas'])

    <main class="main-content">
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 14px;">
                <form method="GET" action="{{ route('dashboardadmin3') }}" class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama petugas atau email...">
                </form>
            </div>
            <div style="font-weight: 600; color: var(--text-sub);">
                {{ \Carbon\Carbon::now()->format('d M Y') }}
            </div>
        </header>

        <div class="content-body">
            @php $petugas = $petugas ?? collect(); @endphp
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <div class="page-header-flex">
                <div>
                    <h2 class="section-title">Daftar Petugas</h2>
                    <p class="section-subtitle">Kelola akses petugas perpustakaan.</p>
                </div>
                <button class="btn-primary" id="btnTambahPetugas">
                    <i class="fa-solid fa-plus"></i> Petugas Baru
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Petugas</th>
                            <th>Email</th>
                            <th>Nomor Telepon</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($petugas as $index => $user)
                        <tr>
                            <td style="text-align: center; color: var(--text-sub);">{{ $index + 1 }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm">
                                        {{ substr($user->nama, 0, 1) }}
                                    </div>
                                    <div class="user-name">{{ $user->nama }}</div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->nomor_telepon ?? '-' }}</td>
                            <td style="text-align: right;">
                                <form action="{{ route('petugas.destroy', $user->UserID) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus petugas ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Hapus Petugas">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="fa-solid fa-user-shield" style="font-size: 2rem; margin-bottom: 10px;"></i>
                                <p>Belum ada data petugas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalRegisterPetugas" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tambah Petugas Baru</h3>
                <button class="close-btn" id="closeRegisterPetugas">&times;</button>
            </div>

            <form action="{{ route('petugas.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" required placeholder="Nama Petugas">
                    @error('nama') <span style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="petugas@seabook.com">
                    @error('email') <span style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="tel" name="nomor_telepon" placeholder="08xxxxxxxxxx">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="petugasPassword" name="password" required placeholder="••••••••" minlength="8">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('petugasPassword', this)" title="Lihat password" aria-label="Lihat atau sembunyikan password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('password') <span style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="petugasPasswordConfirm" name="password_confirmation" required placeholder="••••••••" minlength="8">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('petugasPasswordConfirm', this)" title="Lihat password" aria-label="Lihat atau sembunyikan konfirmasi password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Simpan Data</button>
            </form>
        </div>
    </div>

    <script>
        // Modal Logic
        const modal = document.getElementById('modalRegisterPetugas');
        const btn = document.getElementById('btnTambahPetugas');
        const closeBtn = document.getElementById('closeRegisterPetugas');

        btn.onclick = function() {
            modal.style.display = "flex";
        }

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
                btn.setAttribute('title', 'Sembunyikan password');
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
                btn.setAttribute('title', 'Lihat password');
            }
        }
    </script>
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>