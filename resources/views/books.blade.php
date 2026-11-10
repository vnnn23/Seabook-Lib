<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - {{ config('app.name', 'SeaBook') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">
    
    <style>
        /* --- 1. VARIABLES & RESET --- */
        :root {
            --primary: #2563eb;       /* Biru Utama */
            --primary-dark: #1d4ed8;
            --bg-body: #f1f5f9;       /* Abu-abu muda untuk background */
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body.user-layout {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        .container {
            width: 94%; max-width: 1280px; margin: 0 auto;
        }

        .nav-content {
            display: flex; justify-content: space-between; align-items: center;
        }

        .logo {
            font-size: 1.5rem; font-weight: 800; color: var(--primary);
            display: flex; align-items: center; gap: 8px;
            letter-spacing: -0.5px;
        }
        .logo span { color: var(--text-main); font-weight: 600; font-size: 0.9rem; margin-top: 4px; }

        .search-wrapper {
            flex: 1; max-width: 500px; margin: 0 40px; position: relative;
        }
        
        .search-bar {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border-radius: 50px;
            border: 1px solid var(--border);
            background: var(--bg-body);
            font-size: 0.95rem;
            color: var(--text-main);
            transition: all 0.3s;
            outline: none;
        }
        .search-bar:focus {
            background: white; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .search-icon {
            position: absolute; left: 18px; top: 50%; transform: translateY(-50%);
            color: var(--text-sub); pointer-events: none;
        }

        .nav-links {
            display: flex; align-items: center; gap: 30px;
        }
        .nav-link {
            font-weight: 600; color: var(--text-sub); font-size: 0.95rem;
            transition: color 0.2s; position: relative;
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

        /* --- 3. HERO SECTION --- */
        .hero {
            background: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
        }
        .hero-content h1 { font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 10px; }
        .hero-content p { color: var(--text-sub); max-width: 600px; line-height: 1.6; }

        /* --- 4. FILTERS --- */
        .filter-bar {
            background: white; padding: 15px; border-radius: 16px;
            border: 1px solid var(--border); display: flex; gap: 15px;
            align-items: center; margin-bottom: 40px; box-shadow: var(--shadow);
            flex-wrap: wrap;
        }
        .filter-label { font-weight: 700; color: var(--text-main); margin-right: 10px; }
        
        .filter-btn {
            padding: 8px 16px; background: var(--bg-body); border-radius: 50px;
            border: 1px solid transparent; color: var(--text-sub); font-weight: 600; font-size: 0.9rem;
            cursor: pointer; display: flex; align-items: center; gap: 8px;
            transition: all 0.2s;
        }
        .filter-btn:hover, .filter-btn.active {
            background: #eff6ff; color: var(--primary); border-color: #bfdbfe;
        }

        /* Filter Modal (Mega Menu style) */
        .filter-modal {
            position: absolute; left: 0; right: 0; top: 100%; margin-top: 10px;
            background: white; border-radius: 16px; border: 1px solid var(--border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1); padding: 25px; z-index: 900;
            display: none; grid-template-columns: repeat(3, 1fr); gap: 30px;
        }
        .filter-modal.show { display: grid; }
        
        .filter-column h4 { font-size: 0.9rem; text-transform: uppercase; color: var(--text-sub); margin-bottom: 15px; letter-spacing: 0.5px; }
        .filter-list { max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
        .checkbox-label {
            display: flex; align-items: center; gap: 10px; font-size: 0.95rem; color: var(--text-main); cursor: pointer;
        }
        .checkbox-label input { accent-color: var(--primary); width: 16px; height: 16px; }

        .filter-actions {
            grid-column: span 3; display: flex; justify-content: flex-end; gap: 15px;
            padding-top: 20px; border-top: 1px solid var(--border); margin-top: 10px;
        }
        .btn-reset { color: var(--text-sub); background: none; border: none; font-weight: 600; cursor: pointer; }
        .btn-apply { background: var(--primary); color: white; border: none; padding: 10px 30px; border-radius: 8px; font-weight: 600; cursor: pointer; }

        /* --- 5. BOOK GRID (CARD DESIGN) --- */
        .section-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 25px; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ''; width: 6px; height: 24px; background: var(--primary); border-radius: 4px; display: block; }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Responsif otomatis */
            gap: 25px;
            margin-bottom: 60px;
        }

        .book-card {
            background: white; border-radius: 16px; padding: 12px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            display: flex; flex-direction: column;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: #bfdbfe;
        }

        .book-cover-wrapper {
            position: relative;
            width: 100%; aspect-ratio: 2 / 3;
            border-radius: 10px; overflow: hidden;
            margin-bottom: 15px;
            background: #f1f5f9;
        }

        .book-cover {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.5s;
        }
        .book-card:hover .book-cover { transform: scale(1.05); }

        .book-info { flex: 1; display: flex; flex-direction: column; }
        
        .book-category {
            font-size: 0.75rem; color: var(--primary); font-weight: 700;
            text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px;
        }
        .book-title {
            font-size: 1rem; font-weight: 700; color: var(--text-main);
            margin-bottom: 5px; line-height: 1.4;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .book-author { font-size: 0.85rem; color: var(--text-sub); margin-bottom: 15px; }

        .book-footer {
            margin-top: auto; display: flex; align-items: center; justify-content: space-between;
        }
        
        .btn-action {
            width: 100%; text-align: center; padding: 10px;
            background: var(--bg-body); color: var(--text-main);
            border-radius: 8px; font-weight: 600; font-size: 0.9rem;
            transition: 0.2s; border: 1px solid transparent;
        }
        .btn-action:hover {
            background: var(--primary); color: white;
        }
        .btn-action.disabled {
            background: #e2e8f0; color: #94a3b8; cursor: not-allowed;
        }

        /* --- 6. UTILITIES --- */
        .empty-state { text-align: center; padding: 50px; color: var(--text-sub); grid-column: 1 / -1; }
        
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 1.5rem; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .filter-modal { grid-template-columns: 1fr; }
            .nav-links { display: none; /* Tambahkan toggle menu untuk mobile jika perlu */ }
        }
    </style>
    @include('partials.motion')
</head>
<body class="user-layout">
    @include('partials.user-sidebar', ['active' => 'katalog'])

    <div class="user-main-content">
        <!-- Topbar User -->
        <header class="user-topbar">
            <div class="user-topbar-left" style="flex: 1; max-width: 650px;">
                <button type="button" class="btn-topbar-mobile-toggle" id="sidebarMobileToggleBtn" aria-label="Buka Menu Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="search-wrapper" style="margin: 0; width: 100%; max-width: 480px; position: relative;">
                    <i class="fa-solid fa-magnifying-glass search-icon" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                    <input type="text" class="search-bar" placeholder="Cari judul, penulis, atau ISBN..." style="width: 100%; padding: 10px 18px 10px 42px; border-radius: 50px; border: 1px solid #e2e8f0; background: #f8fafc; outline: none; font-size: 0.92rem;">
                </div>
            </div>
            <div class="user-topbar-right">
                @auth
                    <span class="role-pill role-user"><i class="fa-solid fa-user"></i> {{ Auth::user()->nama }}</span>
                @else
                    <a href="{{ route('login') }}" class="role-pill" style="background:#2563eb; color:white; border-color:#2563eb; text-decoration:none;"><i class="fa-solid fa-right-to-bracket"></i> Masuk</a>
                @endauth
            </div>
        </header>

        <section class="hero">
        <div class="container hero-content">
            <h1>Jelajahi Dunia Pengetahuan</h1>
            <p>Temukan ribuan koleksi buku digital dari berbagai kategori. Baca kapan saja, di mana saja dengan SeaBook.</p>
        </div>
    </section>

    <main class="container">
        
        <div class="filter-bar-wrapper" style="position: relative;">
            <div class="filter-bar">
                <span class="filter-label"><i class="fa-solid fa-filter"></i> Filter:</span>
                
                <button class="filter-btn" id="toggleFilterBtn">
                    Kategori & Penulis <i class="fa-solid fa-chevron-down"></i>
                </button>
                
                <button class="filter-btn reset-filter" style="margin-left: auto; color: #ef4444; background: #fef2f2;">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>

            <div class="filter-modal" id="filterModal">
                <div class="filter-column">
                    <h4>Kategori</h4>
                    <div class="filter-list">
                        @foreach($kategoris as $kategori)
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-cb category-cb" value="{{ $kategori->nama_kategori }}"> 
                            {{ $kategori->nama_kategori }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-column">
                    <h4>Penulis Populer</h4>
                    <div class="filter-list">
                        @php $authors = $buku->pluck('penulis')->unique()->sort(); @endphp
                        @foreach($authors as $author)
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-cb author-cb" value="{{ $author }}"> 
                            {{ $author }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-column">
                    <h4>Tahun Terbit</h4>
                    <div class="filter-list">
                        @php $years = $buku->pluck('tahun_terbit')->unique()->sort()->reverse(); @endphp
                        @foreach($years as $year)
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-cb year-cb" value="{{ $year }}"> 
                            {{ $year }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-actions">
                    <button class="btn-reset reset-filter">Bersihkan</button>
                    <button class="btn-apply" id="applyFilterBtn">Terapkan Filter</button>
                </div>
            </div>
        </div>

        @auth
            @if(Auth::user()->isBlockedFromBorrowing())
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; color: #991b1b;">
                    <strong><i class="fa-solid fa-triangle-exclamation"></i> Peminjaman Dibatasi:</strong>
                    Akun Anda sedang dibatasi dari peminjaman buku (ada buku pinjaman yang belum dikembalikan atau akun belum aktif).
                </div>
            @endif
        @endauth

        <h2 class="section-title">Koleksi Terpopuler</h2>
        
        <!-- Skeleton Loading Grid (Ditampilkan saat awal memuat halaman) -->
        <div class="skeleton-grid" id="bookSkeletonGrid" aria-hidden="true">
            @for($i = 0; $i < 8; $i++)
            <div class="skeleton-book-card">
                <div class="skeleton skeleton-cover"></div>
                <div class="skeleton-info">
                    <div class="skeleton skeleton-text" style="width: 35%; height: 12px; margin-bottom: 8px;"></div>
                    <div class="skeleton skeleton-text heading" style="width: 85%; height: 18px; margin-bottom: 8px;"></div>
                    <div class="skeleton skeleton-text" style="width: 55%; height: 14px; margin-bottom: 12px;"></div>
                    <div class="skeleton skeleton-text" style="width: 70%; height: 12px; margin-bottom: 16px;"></div>
                    <div class="skeleton skeleton-btn"></div>
                </div>
            </div>
            @endfor
        </div>

        <div class="book-grid" id="bookGrid" style="display: none;">
            @forelse($buku as $book)
            <div class="book-card" 
                 data-category="{{ $book->kategoris->pluck('nama_kategori')->implode(' ') }}" 
                 data-author="{{ $book->penulis }}" 
                 data-year="{{ $book->tahun_terbit }}">
                
                <div class="book-cover-wrapper skeleton skeleton-card">
                    @if($book->cover)
                        <img src="{{ asset('storage/' . $book->cover) }}" alt="{{ $book->judul }}" class="book-cover" loading="lazy" onload="this.parentElement.classList.remove('skeleton')" onerror="this.onerror=null;this.parentElement.classList.remove('skeleton');this.parentElement.innerHTML='<div class=\'no-cover-fallback\' style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#e2e8f0;color:#94a3b8;flex-direction:column;\'><i class=\'fa-solid fa-image\' style=\'font-size:2rem;margin-bottom:10px;\'></i><span style=\'font-size:0.8rem;\'>No Cover</span></div>';">
                    @else
                        <div class="no-cover-fallback" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#e2e8f0; color:#94a3b8; flex-direction:column;">
                            <i class="fa-solid fa-image" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <span style="font-size: 0.8rem;">No Cover</span>
                        </div>
                    @endif
                </div>

                <div class="book-info">
                    <div class="book-category">{{ $book->kategoris->first()->nama_kategori ?? 'Umum' }}</div>
                    <h3 class="book-title">{{ $book->judul }}</h3>
                    <p class="book-author">Oleh: {{ $book->penulis }}</p>

                    <div style="display: flex; gap: 8px; font-size: 0.8rem; margin: 10px 0; color: var(--text-sub);">
                        <span>Total: <strong>{{ $book->total }}</strong></span> &bull;
                        <span style="color: {{ $book->tersedia > 0 ? '#16a34a' : '#dc2626' }};">Tersedia: <strong>{{ $book->tersedia }}</strong></span> &bull;
                        <span>Dipinjam: <strong>{{ $book->dipinjam }}</strong></span>
                    </div>

                    <div class="book-footer">
                        @auth
                            @if(Auth::user()->isBlockedFromBorrowing())
                                <button class="btn-action disabled" disabled title="Akun Anda sedang dibatasi dari peminjaman buku (ada buku aktif yang belum dikembalikan atau akun belum disetujui)." style="background: #fee2e2; color: #991b1b; cursor: not-allowed; font-size: 0.8rem;">Peminjaman Dibatasi</button>
                            @elseif($book->tersedia > 0)
                                <a href="{{ route('detailbooks', $book->BukuID) }}" class="btn-action">
                                    Pinjam Buku
                                </a>
                            @else
                                <button class="btn-action disabled" disabled style="background: #cbd5e1; cursor: not-allowed;">Tidak Tersedia</button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn-action">Login</a>
                        @endauth
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <i class="fa-solid fa-book-open" style="font-size: 3.5rem; color: #cbd5e1; margin-bottom: 16px;"></i>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Belum Ada Koleksi Buku</h3>
                <p style="color: #64748b; font-size: 0.9rem;">Saat ini belum ada buku yang ditambahkan ke sistem perpustakaan.</p>
            </div>
            @endforelse
            
            <div class="empty-state" id="emptyState" style="display: none; grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <i class="fa-solid fa-magnifying-glass" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                <h3>Buku tidak ditemukan</h3>
                <p>Coba gunakan kata kunci atau filter lain.</p>
            </div>
        </div>

    </main>
    </div> <!-- /.user-main-content -->

    <script src="{{ asset('js/sidebar-collapsible.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- DROPDOWN PROFILE ---
            const profileBtn = document.getElementById('profileBtn');
            const profileDropdown = document.getElementById('profileDropdown');
            
            if(profileBtn){
                profileBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('show');
                    filterModal.classList.remove('show'); // Tutup filter jika buka profil
                });
            }

            // --- FILTER MODAL TOGGLE ---
            const toggleFilterBtn = document.getElementById('toggleFilterBtn');
            const filterModal = document.getElementById('filterModal');
            const applyFilterBtn = document.getElementById('applyFilterBtn');
            
            toggleFilterBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                filterModal.classList.toggle('show');
                if(profileDropdown) profileDropdown.classList.remove('show');
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if(profileDropdown && !profileBtn.contains(e.target)) profileDropdown.classList.remove('show');
                if(!filterModal.contains(e.target) && !toggleFilterBtn.contains(e.target)) filterModal.classList.remove('show');
            });

            // --- FILTER LOGIC ---
            const bookCards = document.querySelectorAll('.book-card');
            const checkboxes = document.querySelectorAll('.filter-cb');
            const resetBtns = document.querySelectorAll('.reset-filter');
            const searchInput = document.querySelector('.search-bar');
            const emptyState = document.getElementById('emptyState');

            function filterBooks() {
                const searchVal = searchInput.value.toLowerCase();
                
                // Get checked values
                const checkedCats = Array.from(document.querySelectorAll('.category-cb:checked')).map(cb => cb.value);
                const checkedAuthors = Array.from(document.querySelectorAll('.author-cb:checked')).map(cb => cb.value);
                const checkedYears = Array.from(document.querySelectorAll('.year-cb:checked')).map(cb => cb.value);

                let visibleCount = 0;

                bookCards.forEach(card => {
                    const title = card.querySelector('.book-title').textContent.toLowerCase();
                    const author = card.dataset.author.toLowerCase();
                    const category = card.dataset.category;
                    const year = card.dataset.year;

                    // Logic Check
                    const matchSearch = title.includes(searchVal) || author.includes(searchVal);
                    const matchCat = checkedCats.length === 0 || checkedCats.some(c => category.includes(c));
                    const matchAuth = checkedAuthors.length === 0 || checkedAuthors.includes(card.dataset.author);
                    const matchYear = checkedYears.length === 0 || checkedYears.includes(year);

                    if(matchSearch && matchCat && matchAuth && matchYear) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show/Hide Empty State
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            // Event Listeners
            searchInput.addEventListener('input', filterBooks);
            applyFilterBtn.addEventListener('click', () => {
                filterBooks();
                filterModal.classList.remove('show');
            });
            
            resetBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    checkboxes.forEach(cb => cb.checked = false);
                    searchInput.value = '';
                    filterBooks();
                });
            });

        });
    </script>
    @include('partials.chat-widget')
    @include('partials.logout-modal')
</body>
</html>