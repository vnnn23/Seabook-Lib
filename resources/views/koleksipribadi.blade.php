<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Saya - {{ config('app.name', 'SeaBook') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">
    
    <style>
        /* --- 1. VARIABLES & RESET --- */
        :root {
            --primary: #2563eb;       
            --primary-hover: #1d4ed8;
            --secondary: #64748b;
            --bg-body: #f8fafc;       
            --bg-card: #ffffff;
            --text-main: #0f172a;     
            --text-sub: #475569;
            --border: #e2e8f0;
            --radius-lg: 20px;
            --radius-md: 12px;
            --shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
            --shadow-sm: 0 4px 6px -1px rgba(0,0,0,0.05);
            --danger: #ef4444;
            --danger-bg: #fef2f2;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body.user-layout {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        .container { width: 94%; max-width: 1200px; margin: 0 auto; }
        a { text-decoration: none; color: inherit; }

        /* --- 2. NAVBAR (Sama persis dengan Detail Books) --- */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 1000; padding: 16px 0;
        }
        .nav-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.4rem; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .logo span { color: var(--text-main); font-weight: 600; font-size: 0.9rem; margin-top: 4px; }
        .nav-links { display: flex; align-items: center; gap: 25px; }
        .nav-link { font-weight: 600; color: var(--secondary); text-decoration: none; font-size: 0.95rem; transition: 0.2s; }
        .nav-link:hover { color: var(--primary); }

        /* Profile Dropdown */
        .profile-wrapper { position: relative; }
        .avatar-btn {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--primary) no-repeat center / cover;
            color: white;
            font-weight: 700; display: flex; justify-content: center; align-items: center;
            cursor: pointer; transition: transform 0.2s;
            overflow: hidden;
            flex-shrink: 0;
        }
        .avatar-btn:hover { transform: scale(1.05); }

        .dropdown-menu {
            position: absolute; top: 55px; right: 0; width: 240px;
            background: white; border-radius: 16px; border: 1px solid var(--border);
            box-shadow: var(--shadow); opacity: 0; visibility: hidden;
            transform: translateY(-10px); transition: all 0.2s;
            z-index: 1100;
        }
        .dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-item { padding: 12px 20px; display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-main); font-weight: 500; transition: background 0.2s; }
        .dropdown-item:hover { background: var(--bg-body); }
        .dropdown-item.danger { color: #ef4444; border-top: 1px solid var(--border); }


        /* --- 3. PAGE HEADER --- */
        .page-header {
            background: white;
            border-radius: var(--radius-lg);
            padding: 30px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
        }
        
        .header-content h1 { font-size: 1.8rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        .header-content p { color: var(--secondary); margin-top: 5px; font-size: 0.95rem; }
        .count-badge {
            background: var(--bg-body); color: var(--primary); padding: 8px 16px;
            border-radius: 30px; font-weight: 700; font-size: 0.9rem;
            border: 1px solid var(--border);
        }

        /* --- 4. BOOK GRID --- */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        .card-koleksi {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            display: flex; flex-direction: column;
        }

        .card-koleksi:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            border-color: #bfdbfe;
        }

        .card-cover-area {
            position: relative;
            width: 100%;
            aspect-ratio: 2/3;
            overflow: hidden;
        }

        .book-img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.5s ease;
        }
        .card-koleksi:hover .book-img { transform: scale(1.05); }

        /* Delete Button Overlay */
        .delete-overlay {
            position: absolute; top: 10px; right: 10px;
            z-index: 10;
        }
        .btn-delete-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-delete-icon:hover {
            background: var(--danger); color: white; transform: scale(1.1);
        }

        .card-body { padding: 16px; flex: 1; display: flex; flex-direction: column; }
        
        .card-title {
            font-size: 1.05rem; font-weight: 700; color: var(--text-main);
            margin-bottom: 5px; line-height: 1.4;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        
        .card-author { font-size: 0.85rem; color: var(--secondary); margin-bottom: 15px; }

        .btn-detail {
            margin-top: auto;
            display: block; width: 100%; text-align: center;
            padding: 10px; border-radius: 8px;
            background: var(--bg-body); color: var(--text-main);
            font-weight: 600; font-size: 0.9rem;
            border: 1px solid transparent; transition: all 0.2s;
        }
        .btn-detail:hover {
            background: var(--primary); color: white;
        }

        /* --- 5. EMPTY STATE --- */
        .empty-state-box {
            background: white; border-radius: var(--radius-lg);
            border: 2px dashed var(--border);
            padding: 60px 20px; text-align: center;
        }
        .empty-icon {
            font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;
        }
        .empty-title { font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 10px; }
        .empty-desc { color: var(--text-sub); margin-bottom: 30px; }
        
        .btn-explore {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--primary); color: white; padding: 12px 30px;
            border-radius: 10px; font-weight: 700; transition: all 0.2s;
        }
        .btn-explore:hover { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }

        /* Alerts */
        .alert {
            padding: 16px; border-radius: 12px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 12px; font-weight: 500;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; text-align: center; gap: 15px; }
            .grid-container { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .grid-container { grid-template-columns: 1fr; }
        }
    </style>
    @include('partials.motion')
</head>
<body class="user-layout">
    @include('partials.user-sidebar', ['active' => 'koleksi'])

    <div class="user-main-content">
        <!-- Topbar User -->
        <header class="user-topbar">
            <div class="user-topbar-left">
                <button type="button" class="btn-topbar-mobile-toggle" id="sidebarMobileToggleBtn" aria-label="Buka Menu Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="user-page-title">Koleksi Saya</h1>
            </div>
            <div class="user-topbar-right">
                <span class="role-pill role-user"><i class="fa-solid fa-user"></i> {{ Auth::user()->nama }}</span>
            </div>
        </header>

        <main class="container" style="margin-top: 25px;">
        
        <div class="page-header">
            <div class="header-content">
                <h1><i class="fa-solid fa-heart" style="color: #ef4444;"></i> Koleksi Pribadi</h1>
                <p>Simpan dan kelola daftar buku favorit Anda di sini.</p>
            </div>
            @if($koleksis->count() > 0)
                <div class="count-badge">
                    {{ $koleksis->count() }} Buku Disimpan
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif


        @if($koleksis->count() > 0)
            <div class="grid-container">
                @foreach($koleksis as $koleksi)
                    <div class="card-koleksi">
                        <div class="card-cover-area">
                            <div class="delete-overlay">
                                <form action="{{ route('koleksi.destroyByBuku', $koleksi->buku->BukuID) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-icon" title="Hapus dari koleksi" onclick="return confirm('Yakin ingin menghapus buku ini dari koleksi?')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>

                            <img src="{{ $koleksi->buku->cover ? asset('storage/' . $koleksi->buku->cover) : 'https://placehold.co/200x300/e2e8f0/64748b?text=' . urlencode($koleksi->buku->judul) }}" 
                                 alt="{{ $koleksi->buku->judul }}" 
                                 class="book-img">
                        </div>

                        <div class="card-body">
                            <h3 class="card-title">{{ $koleksi->buku->judul }}</h3>
                            <p class="card-author">{{ $koleksi->buku->penulis }}</p>
                            
                            <a href="{{ route('detailbooks', $koleksi->buku->BukuID) }}" class="btn-detail">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-box">
                <div class="empty-icon">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <h2 class="empty-title">Koleksi Anda Masih Kosong</h2>
                <p class="empty-desc">Jelajahi perpustakaan kami dan simpan buku yang menarik minat Anda!</p>
                <a href="{{ route('books') }}" class="btn-explore">
                    <i class="fa-solid fa-magnifying-glass"></i> Jelajahi Buku
                </a>
            </div>
        @endif

    </main>
    </div> <!-- /.user-main-content -->

    <script src="{{ asset('js/sidebar-collapsible.js') }}"></script>
    @include('partials.chat-widget')
    @include('partials.logout-modal')
</body>
</html>