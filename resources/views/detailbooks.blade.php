<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $buku->judul ?? 'Buku' }} - {{ config('app.name', 'SeaBook') }}</title>
    
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
            --star-color: #f59e0b;    
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body.user-layout {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        .container { width: 94%; max-width: 1200px; margin: 0 auto; }

        /* --- 2. NAVBAR --- */
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
        }
        .dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-item { padding: 12px 20px; display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-main); font-weight: 500; transition: background 0.2s; }
        .dropdown-item:hover { background: var(--bg-body); }
        .dropdown-item.danger { color: #ef4444; border-top: 1px solid var(--border); }


        /* --- 3. MAIN CONTENT --- */
        .breadcrumb {
            margin-bottom: 20px; font-size: 0.9rem; color: var(--secondary); font-weight: 500;
        }
        .breadcrumb a { color: var(--text-sub); text-decoration: none; transition: 0.2s; }
        .breadcrumb a:hover { color: var(--primary); }
        .breadcrumb i { margin-right: 6px; }

        /* Layout Grid Utama */
        .book-detail-grid {
            display: grid; grid-template-columns: 320px 1fr; gap: 40px;
            align-items: start;
        }

        /* Kolom Kiri: Cover & Quick Stats */
        .left-column { position: sticky; top: 100px; }
        
        .book-cover-wrapper {
            background: white; padding: 15px; border-radius: var(--radius-lg);
            box-shadow: var(--shadow); border: 1px solid var(--border);
            margin-bottom: 20px; 
            position: relative; /* Penting untuk tombol koleksi */
            overflow: hidden;
        }
        
        /* Tombol Koleksi (Floating Heart) */
        .btn-wishlist {
            width: 42px; height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            transition: all 0.2s ease;
            color: #94a3b8; /* Warna default (abu-abu) */
        }
        
        .btn-wishlist:hover {
            transform: scale(1.1);
            color: #ef4444; /* Merah saat hover */
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        .btn-wishlist.active {
            color: #ef4444; /* Merah jika sudah dikoleksi */
            background: #fff;
        }

        .book-cover {
            width: 100%; aspect-ratio: 2/3; object-fit: cover;
            border-radius: 12px; display: block;
        }
        .no-cover {
            width: 100%; aspect-ratio: 2/3; background: #e2e8f0; border-radius: 12px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #94a3b8;
        }

        .quick-stats {
            display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
        }
        .stat-card {
            background: white; padding: 15px; border-radius: var(--radius-md);
            border: 1px solid var(--border); text-align: center;
        }
        .stat-card h5 { font-size: 0.8rem; color: var(--secondary); margin-bottom: 5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card p { font-size: 1.1rem; font-weight: 800; color: var(--text-main); }


        /* Kolom Kanan: Info & Tabs */
        .right-column {
            background: white; border-radius: var(--radius-lg);
            border: 1px solid var(--border); padding: 40px;
            box-shadow: var(--shadow-sm);
        }

        .book-meta-header { margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 30px; }
        
        .book-category-badge {
            display: inline-block; background: #eff6ff; color: var(--primary);
            font-size: 0.8rem; font-weight: 700; padding: 6px 12px; border-radius: 30px;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px;
        }

        .book-title {
            font-size: 2.2rem; font-weight: 800; color: var(--text-main);
            line-height: 1.2; margin-bottom: 10px;
        }

        .book-author {
            font-size: 1.1rem; color: var(--text-sub); margin-bottom: 20px; font-weight: 500;
        }
        .book-author strong { color: var(--text-main); font-weight: 700; }

        /* Rating Section */
        .rating-wrapper {
            display: flex; align-items: center; gap: 15px; margin-bottom: 25px;
        }
        .stars { color: var(--star-color); font-size: 1.2rem; display: flex; gap: 3px; }
        .rating-num { font-size: 1.2rem; font-weight: 800; color: var(--text-main); }
        .rating-count { font-size: 0.9rem; color: var(--secondary); }

        /* Action Buttons */
        .action-area { display: flex; gap: 15px; align-items: center; margin-bottom: 10px; flex-wrap: wrap; }
        
        .btn-pinjam {
            background: var(--primary); color: white; border: none;
            padding: 14px 40px; border-radius: 12px; font-weight: 700; font-size: 1rem;
            cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-pinjam:hover { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2); }
        .btn-pinjam.disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; transform: none; }

        .stock-status {
            font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 6px;
        }
        .stock-status.available { color: #16a34a; }
        .stock-status.empty { color: #dc2626; }


        /* --- TABS SYSTEM --- */
        .tabs-container { margin-top: 20px; }
        
        .tabs-nav {
            display: flex; gap: 30px; border-bottom: 2px solid #f1f5f9; margin-bottom: 30px;
        }
        
        .tab-btn {
            background: none; border: none; padding: 15px 0;
            font-size: 1rem; font-weight: 600; color: var(--secondary);
            cursor: pointer; position: relative; transition: color 0.2s;
        }
        .tab-btn:hover { color: var(--text-main); }
        .tab-btn.active { color: var(--primary); }
        .tab-btn.active::after {
            content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: var(--primary);
        }

        .tab-content { display: none; animation: fadeIn 0.4s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Tab: Deskripsi & Detail */
        .book-desc { font-size: 1rem; line-height: 1.8; color: var(--text-sub); }
        
        .detail-list { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px; }
        .detail-item { padding: 15px; background: var(--bg-body); border-radius: 10px; }
        .detail-label { font-size: 0.85rem; color: var(--secondary); margin-bottom: 5px; }
        .detail-val { font-size: 1rem; font-weight: 600; color: var(--text-main); }


        /* Tab: Ulasan */
        .review-header-sec {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;
        }
        .btn-write-review {
            font-size: 0.85rem; color: var(--primary); font-weight: 700; background: #eff6ff; border: 1px solid #bfdbfe; padding: 6px 14px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;
        }
        .btn-write-review:hover { background: var(--primary); color: #ffffff; border-color: var(--primary); }

        .review-list { display: flex; flex-direction: column; gap: 20px; }
        
        .review-item {
            display: flex; gap: 15px; padding-bottom: 20px; border-bottom: 1px solid var(--border);
        }
        .review-item:last-child { border-bottom: none; }
        
        .reviewer-avatar {
            width: 45px; height: 45px; background: #e0f2fe no-repeat center / cover;
            color: #0369a1;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; flex-shrink: 0; font-size: 1.1rem;
            overflow: hidden;
        }
        
        .review-body h4 { font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 2px; }
        .review-date { font-size: 0.8rem; color: var(--secondary); display: block; margin-bottom: 8px; }
        .review-text { font-size: 0.95rem; line-height: 1.5; color: var(--text-sub); margin-top: 8px; }
        .review-stars { color: var(--star-color); font-size: 0.85rem; }

        /* Form Ulasan */
        .review-form-box {
            background: var(--bg-body); padding: 25px; border-radius: 12px; margin-bottom: 30px; border: 1px solid var(--border);
        }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-main); }
        
        /* Star Input */
        .star-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; }
        .star-input input { display: none; }
        .star-input label { font-size: 1.8rem; color: #cbd5e1; cursor: pointer; transition: color 0.2s; }
        .star-input input:checked ~ label, .star-input label:hover, .star-input label:hover ~ label { color: var(--star-color); }

        .form-control {
            width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.95rem; outline: none; transition: border 0.2s;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }


        /* --- RESPONSIVE --- */
        @media (max-width: 900px) {
            .book-detail-grid { grid-template-columns: 1fr; gap: 0; }
            .left-column { position: static; max-width: 300px; margin: 0 auto 30px; }
            .right-column { padding: 25px; }
            .book-title { font-size: 1.8rem; }
            .detail-list { grid-template-columns: 1fr; }
        }

        /* --- MODAL STYLES (Pinjam & Success) --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);
            display: none; justify-content: center; align-items: center; z-index: 2000;
        }
        .modal-box {
            background: white; width: 92%; max-width: 480px; padding: 24px;
            border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            position: relative; animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .modal-box.modal-box-lg {
            max-width: 640px;
            padding: 24px 28px;
            overflow: visible;
        }
        .loan-dates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 4px;
        }
        @media (max-width: 640px) {
            .modal-box.modal-box-lg {
                padding: 20px 16px;
                width: 95%;
            }
            .loan-dates-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .modal-header h3 { font-size: 1.3rem; font-weight: 700; margin-bottom: 14px; text-align: center; }
        
        .modal-book-summary {
            display: flex; gap: 12px; background: var(--bg-body); padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; align-items: center;
        }
        .modal-thumb { width: 44px; height: 60px; object-fit: cover; border-radius: 6px; }
        
        .checkbox-wrapper {
            display: flex; align-items: flex-start; gap: 10px; margin-bottom: 18px; font-size: 0.88rem; color: var(--text-sub);
        }
        .checkbox-wrapper input { width: 18px; height: 18px; margin-top: 2px; accent-color: var(--primary); cursor: pointer; }

        .btn-full { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 0.95rem; transition: all 0.2s ease; }
        .btn-full:hover:not(:disabled) { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); }
        .btn-full:disabled {
            background: #cbd5e1;
            color: #64748b;
            cursor: not-allowed;
            opacity: 0.75;
            box-shadow: none;
            transform: none;
        }

        .close-modal {
            position: absolute; top: 18px; right: 20px; background: none; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer;
        }
        .close-modal:hover { color: var(--text-main); }
        
        /* Success Modal Specifics */
        .success-icon { width: 80px; height: 80px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2.5rem; }
        .success-msg { text-align: center; margin-bottom: 25px; }
        .success-msg h4 { font-size: 1.5rem; font-weight: 800; margin-bottom: 5px; }
        .success-msg p { color: var(--text-sub); }

    </style>
    @include('partials.motion')
</head>
<body class="user-layout">
    @include('partials.user-sidebar', ['active' => 'katalog'])

    <div class="user-main-content">
        <!-- Topbar User -->
        <header class="user-topbar">
            <div class="user-topbar-left">
                <button type="button" class="btn-topbar-mobile-toggle" id="sidebarMobileToggleBtn" aria-label="Buka Menu Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="user-page-title">Detail Buku</h1>
            </div>
            <div class="user-topbar-right">
                @auth
                    <span class="role-pill role-user"><i class="fa-solid fa-user"></i> {{ Auth::user()->nama }}</span>
                @else
                    <a href="{{ route('login') }}" class="role-pill" style="background:#2563eb; color:white; border-color:#2563eb; text-decoration:none;"><i class="fa-solid fa-right-to-bracket"></i> Masuk</a>
                @endauth
            </div>
        </header>

        <main class="container" style="margin-top: 25px; margin-bottom: 50px;">
        
        <div class="breadcrumb">
            <a href="{{ route('books') }}"><i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Buku</a>
        </div>

        <!-- Skeleton Detail Placeholder (Ditampilkan saat awal muat) -->
        <div class="skeleton-detail-grid" id="detailBookSkeleton" aria-hidden="true">
            <div>
                <div class="skeleton skeleton-cover" style="width: 100%; aspect-ratio: 2/3; border-radius: 12px; margin-bottom: 20px;"></div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                    <div class="skeleton" style="height: 60px; border-radius: 8px;"></div>
                    <div class="skeleton" style="height: 60px; border-radius: 8px;"></div>
                    <div class="skeleton" style="height: 60px; border-radius: 8px;"></div>
                </div>
            </div>
            <div>
                <div class="skeleton skeleton-text" style="width: 25%; height: 16px; margin-bottom: 12px;"></div>
                <div class="skeleton skeleton-text heading" style="width: 80%; height: 32px; margin-bottom: 16px;"></div>
                <div class="skeleton skeleton-text" style="width: 45%; height: 18px; margin-bottom: 24px;"></div>
                <div class="skeleton skeleton-text" style="width: 100%; height: 16px; margin-bottom: 8px;"></div>
                <div class="skeleton skeleton-text" style="width: 95%; height: 16px; margin-bottom: 8px;"></div>
                <div class="skeleton skeleton-text" style="width: 70%; height: 16px; margin-bottom: 30px;"></div>
                <div class="skeleton" style="width: 200px; height: 46px; border-radius: 8px;"></div>
            </div>
        </div>

        <div class="book-detail-grid" id="detailBookReal" style="display: none;">
            
            <div class="left-column">
                <div class="book-cover-wrapper skeleton skeleton-card">
                    @auth
                        @if($inKoleksi)
                            <form action="{{ route('koleksi.destroyByBuku', $buku->BukuID) }}" method="POST" style="position: absolute; top: 15px; right: 15px; z-index: 20;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-wishlist active" title="Hapus dari Koleksi">
                                    <i class="fa-solid fa-heart"></i>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('koleksi.store') }}" method="POST" style="position: absolute; top: 15px; right: 15px; z-index: 20;">
                                @csrf
                                <input type="hidden" name="BukuID" value="{{ $buku->BukuID }}">
                                <button type="submit" class="btn-wishlist" title="Tambah ke Koleksi">
                                    <i class="fa-regular fa-heart"></i>
                                </button>
                            </form>
                        @endif
                    @endauth

                    @if($buku->cover)
                        <img src="{{ $buku->cover ? asset('storage/' . $buku->cover) : 'https://placehold.co/200x300/e2e8f0/64748b?text=' . urlencode($buku->judul ?? 'Buku') }}" alt="{{ $buku->judul ?? 'Buku' }}" class="book-cover" loading="lazy" onload="this.parentElement.classList.remove('skeleton')" onerror="this.onerror=null;this.parentElement.classList.remove('skeleton');this.parentElement.innerHTML='<div class=\'no-cover\'><i class=\'fa-solid fa-image\' style=\'font-size: 3rem; margin-bottom: 10px;\'></i><span>No Cover</span></div>';">
                    @else
                        <div class="no-cover">
                            <i class="fa-solid fa-image" style="font-size: 3rem; margin-bottom: 10px;"></i>
                            <span>No Cover</span>
                        </div>
                    @endif
                </div>

                <div class="quick-stats">
                    <div class="stat-card">
                        <h5>Total Eksemplar</h5>
                        <p>{{ $buku->total }}</p>
                    </div>
                    <div class="stat-card">
                        <h5>Tersedia</h5>
                        <p>{{ $buku->tersedia }}</p>
                    </div>
                    <div class="stat-card">
                        <h5>Pembaca</h5>
                        <p>{{ $pembacaCount ?? 0 }}</p>
                    </div>
                    <div class="stat-card">
                        <h5>Dipinjam</h5>
                        <p>{{ $buku->dipinjam }}</p>
                    </div>
                </div>
            </div>

            <div class="right-column">
                <div class="book-meta-header">
                    <div class="book-category-badge">
                        {{ $buku->kategoris->pluck('nama_kategori')->first() ?? 'Umum' }}
                    </div>
                    
                    <h1 class="book-title">{{ $buku->judul ?? 'Buku tidak ditemukan' }}</h1>
                    <div class="book-author">Penulis: <strong>{{ $buku->penulis }}</strong></div>

                    @php
                        $avgRating = $buku->ulasan ? $buku->ulasan->avg('rating') : 0;
                        $ratingValue = $avgRating ? number_format($avgRating, 1) : '0.0';
                        $ratingCount = $buku->ulasan ? $buku->ulasan->count() : 0;
                    @endphp

                    <div class="rating-wrapper">
                        <div class="rating-num">{{ $ratingValue }}</div>
                        <div class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $ratingValue)
                                    <i class="fa-solid fa-star"></i>
                                @elseif($i - 0.5 <= $ratingValue)
                                    <i class="fa-solid fa-star-half-stroke"></i>
                                @else
                                    <i class="fa-regular fa-star" style="color: #cbd5e1;"></i>
                                @endif
                            @endfor
                        </div>
                        <div class="rating-count">({{ $ratingCount }} Ulasan)</div>
                    </div>

                    <div class="action-area">
                        @auth
                            @if(Auth::user()->isBlockedFromBorrowing())
                                <button class="btn-pinjam disabled" disabled style="background: #fee2e2; color: #991b1b; cursor: not-allowed;">
                                    <i class="fa-solid fa-ban"></i> Peminjaman Dibatasi
                                </button>
                                <div style="width: 100%; margin-top: 8px; color: #dc2626; font-size: 0.9rem; font-weight: 500;">
                                    <i class="fa-solid fa-circle-exclamation"></i> Akun Anda sedang dibatasi dari peminjaman buku (ada buku aktif yang belum dikembalikan atau akun belum aktif).
                                </div>
                            @elseif($buku->tersedia > 0)
                                <button class="btn-pinjam" onclick="toggleModal(true)">
                                    <i class="fa-solid fa-book-bookmark"></i> Pinjam Buku Ini
                                </button>
                                <span class="stock-status available"><i class="fa-solid fa-check-circle"></i> Eksemplar Tersedia ({{ $buku->tersedia }})</span>
                            @else
                                <button class="btn-pinjam disabled" disabled style="background: #cbd5e1; cursor: not-allowed;">Tidak Tersedia</button>
                                <span class="stock-status empty"><i class="fa-solid fa-circle-xmark"></i> Sedang Dipinjam Semua</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn-pinjam">Login untuk Pinjam</a>
                        @endauth
                    </div>
                </div>

                <div class="tabs-container">
                    <div class="tabs-nav">
                        <button class="tab-btn active" onclick="openTab(event, 'deskripsi')">Sinopsis</button>
                        <button class="tab-btn" onclick="openTab(event, 'detail')">Detail Buku</button>
                        <button class="tab-btn" onclick="openTab(event, 'ulasan')">Ulasan ({{ $ratingCount }})</button>
                    </div>

                    <div id="deskripsi" class="tab-content active">
                        <p class="book-desc">
                            {{ $buku->deskripsi ?? 'Belum ada deskripsi untuk buku ini. Namun buku ini sangat direkomendasikan untuk dibaca.' }}
                        </p>
                    </div>

                    <div id="detail" class="tab-content">
                        <div class="detail-list">
                            <div class="detail-item">
                                <div class="detail-label">Penerbit</div>
                                <div class="detail-val">{{ $buku->penerbit }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Tahun Terbit</div>
                                <div class="detail-val">{{ $buku->tahun_terbit }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">ISBN</div>
                                <div class="detail-val">{{ $buku->isbn }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Kategori Lengkap</div>
                                <div class="detail-val">
                                    {{ $buku->kategoris->pluck('nama_kategori')->implode(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="ulasan" class="tab-content">
                        
                        @auth
                            @if($hasBorrowedAndReturned && !$sudahMemberiBulasan)
                            <div class="review-form-box">
                                <h4 style="margin-bottom: 15px;">Tulis Ulasan Anda</h4>
                                <form id="reviewForm">
                                    @csrf
                                    <input type="hidden" name="buku_id" value="{{ $buku->BukuID }}">
                                    
                                    <div class="form-group">
                                        <label class="form-label">Berikan Rating</label>
                                        <div class="star-input">
                                            <input type="radio" id="star5" name="rating" value="5" required><label for="star5" title="Sempurna"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="Bagus"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="Cukup"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="Kurang"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="Buruk"><i class="fa-solid fa-star"></i></label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Ulasan</label>
                                        <textarea name="isi_ulasan" class="form-control" rows="4" placeholder="Apa pendapat Anda tentang buku ini?" minlength="10" required></textarea>
                                    </div>
                                    <button type="submit" class="btn-pinjam" style="padding: 10px 25px; font-size: 0.9rem;">Kirim Ulasan</button>
                                </form>
                            </div>
                            @elseif($sudahMemberiBulasan)
                                <div style="background: #ecfdf5; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                                    <i class="fa-solid fa-check-circle"></i> Terima kasih! Anda sudah memberikan ulasan untuk buku ini.
                                </div>
                            @elseif(!$hasBorrowedAndReturned)
                                <div style="background: #fef2f2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                                    <i class="fa-solid fa-circle-info"></i> Anda hanya dapat memberikan ulasan setelah meminjam dan mengembalikan buku ini.
                                </div>
                            @endif
                        @else
                            <div style="background: #fef2f2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                                <i class="fa-solid fa-circle-info"></i> Silakan <a href="{{ route('login') }}" style="color: #991b1b; font-weight: 700; text-decoration: none; padding: 2px 8px; background: #fee2e2; border-radius: 4px; border: 1px solid #fecaca; margin: 0 4px;">Login</a> untuk memberikan ulasan.
                            </div>
                        @endauth

                        <div class="review-list">
                            @forelse($buku->ulasan ?? [] as $ulasan)
                                <div class="review-item">
                                    @php
                                        $reviewerFotoPath = $ulasan->user && $ulasan->user->foto_profil ? asset('storage/' . $ulasan->user->foto_profil) : null;
                                    @endphp
                                    <div class="reviewer-avatar" @if($reviewerFotoPath) style="background-image: url('{{ $reviewerFotoPath }}');" @else style="background-color: #e0f2fe;" @endif>
                                        @if(!$reviewerFotoPath)
                                            {{ substr($ulasan->user->nama ?? 'U', 0, 1) }}
                                        @endif
                                    </div>
                                    <div class="review-body">
                                        <h4>{{ $ulasan->user->nama ?? 'Pengguna' }}</h4>
                                        <span class="review-date">{{ $ulasan->created_at->format('d M Y') }}</span>
                                        <div class="review-stars">
                                            @for($i=0; $i<$ulasan->rating; $i++) <i class="fa-solid fa-star"></i> @endfor
                                        </div>
                                        <p class="review-text">{{ $ulasan->isi_ulasan }}</p>

                                        @if(Auth::id() == $ulasan->UserID)
                                            <button onclick="deleteReview({{ $ulasan->UlasanID }})" style="color: #ef4444; background: none; border: none; font-size: 0.8rem; cursor: pointer; margin-top: 5px; font-weight: 600;">Hapus</button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; color: var(--secondary); padding: 30px;">
                                    <i class="fa-regular fa-comment-dots" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                    <p>Belum ada ulasan. Jadilah yang pertama mereview!</p>
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>


    <div id="loanModal" class="modal-overlay">
        <div class="modal-box modal-box-lg">
            <button class="close-modal" onclick="toggleModal(false)"><i class="fa-solid fa-times"></i></button>
            <div class="modal-header"><h3>Konfirmasi Peminjaman</h3></div>
            
            <div class="modal-book-summary">
                @if($buku->cover)
                    <img src="{{ asset('storage/' . $buku->cover) }}" class="modal-thumb">
                @endif
                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 3px; font-weight: 700;">{{ $buku->judul ?? 'Buku' }}</h4>
                    <span style="font-size: 0.82rem; color: var(--secondary);">Maksimal Durasi: {{ config('perpustakaan.durasi_maksimal_hari', 30) }} Hari</span>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; margin-bottom: 14px; font-size: 0.82rem; color: #475569;">
                <div style="font-weight: 700; margin-bottom: 4px; color: #1e293b;"><i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Ringkasan Aturan Peminjaman:</div>
                <ul style="padding-left: 18px; margin: 0; line-height: 1.5;">
                    <li>Durasi maksimal peminjaman: <strong>{{ config('perpustakaan.durasi_maksimal_hari', 30) }} hari</strong> (maksimal <strong>{{ config('perpustakaan.max_active_loans', 3) }} buku</strong> aktif).</li>
                    <li>Batas pengambilan buku: <strong>{{ config('perpustakaan.pickup_expiry_hours', 24) }} jam</strong> sejak disetujui petugas.</li>
                    <li>Harap mengembalikan buku tepat waktu sebelum tanggal jatuh tempo dan menjaga kondisi buku.</li>
                </ul>
            </div>

            <form action="{{ route('peminjaman.store') }}" method="POST" id="loanForm">
                @csrf
                <input type="hidden" name="buku_id" value="{{ $buku->BukuID }}">
                
                <div class="loan-dates-grid">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.88rem; margin-bottom: 6px;">Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" class="form-control" style="padding: 10px 12px; font-size: 0.9rem;" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.88rem; margin-bottom: 6px;">Tanggal Pengembalian</label>
                        <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control" style="padding: 10px 12px; font-size: 0.9rem;" required>
                    </div>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" id="agreement" required>
                    <label for="agreement" style="cursor: pointer; user-select: none;">Saya setuju untuk mengembalikan buku tepat waktu dan menjaga kondisi buku.</label>
                </div>

                <button type="submit" class="btn-full" id="btnSubmitLoan" disabled>Konfirmasi Pinjam</button>
            </form>
        </div>
    </div>

    <div id="successModal" class="modal-overlay">
        <div class="modal-box" style="text-align: center;">
            <button class="close-modal" onclick="closeSuccess()"><i class="fa-solid fa-times"></i></button>
            
            <div class="success-icon"><i class="fa-solid fa-check"></i></div>
            <div class="success-msg">
                <h4>Peminjaman Berhasil!</h4>
                <p>Silakan tunggu konfirmasi dari petugas perpustakaan.</p>
            </div>
            
            <a href="{{ route('peminjaman.riwayat') }}" class="btn-full" style="text-decoration: none; display: inline-block;">Lihat Riwayat Saya</a>
        </div>
    </div>
    </div> <!-- /.user-main-content -->

    <script src="{{ asset('js/sidebar-collapsible.js') }}"></script>
    <script>
        // --- TABS LOGIC ---
        function openTab(evt, tabName) {
            let i, tabContent, tabBtns;
            
            tabContent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabContent.length; i++) {
                tabContent[i].classList.remove("active");
                tabContent[i].style.display = "none";
            }
            
            tabBtns = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tabBtns.length; i++) {
                tabBtns[i].classList.remove("active");
            }
            
            document.getElementById(tabName).style.display = "block";
            setTimeout(() => document.getElementById(tabName).classList.add("active"), 10);
            evt.currentTarget.classList.add("active");
            // If the Ulasan tab was opened, (re)attach review submit handler
            if (tabName === 'ulasan') attachReviewHandler();
        }

        // --- MODAL LOGIC ---
        function toggleModal(show) {
            const modal = document.getElementById('loanModal');
            modal.style.display = show ? 'flex' : 'none';
            
            // Inisialisasi tanggal (tanpa jam) & status checkbox saat modal dibuka
            if(show) {
                const agreement = document.getElementById('agreement');
                const btnSubmit = document.getElementById('btnSubmitLoan');
                if (agreement && btnSubmit) {
                    agreement.checked = false;
                    btnSubmit.disabled = true;
                    agreement.onchange = function() {
                        btnSubmit.disabled = !this.checked;
                    };
                }

                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const todayStr = `${year}-${month}-${day}`;
                
                const sevenDaysLater = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
                const sYear = sevenDaysLater.getFullYear();
                const sMonth = String(sevenDaysLater.getMonth() + 1).padStart(2, '0');
                const sDay = String(sevenDaysLater.getDate()).padStart(2, '0');
                const returnStr = `${sYear}-${sMonth}-${sDay}`;
                
                const pinjamInput = document.getElementById('tanggal_pinjam');
                const kembaliInput = document.getElementById('tanggal_kembali');
                
                if (pinjamInput) {
                    pinjamInput.value = todayStr;
                    pinjamInput.min = todayStr;
                }
                if (kembaliInput) {
                    kembaliInput.value = returnStr;
                    kembaliInput.min = todayStr;
                }
                
                // Update batas minimal tanggal_kembali saat tanggal_pinjam diubah
                if (pinjamInput && kembaliInput) {
                    pinjamInput.onchange = function() {
                        kembaliInput.min = this.value;
                        if (kembaliInput.value <= this.value) {
                            const newPinjam = new Date(this.value);
                            const newKembali = new Date(newPinjam.getTime() + 7 * 24 * 60 * 60 * 1000);
                            const nYear = newKembali.getFullYear();
                            const nMonth = String(newKembali.getMonth() + 1).padStart(2, '0');
                            const nDay = String(newKembali.getDate()).padStart(2, '0');
                            kembaliInput.value = `${nYear}-${nMonth}-${nDay}`;
                        }
                    };
                }
            }
        }
        function closeSuccess() {
            document.getElementById('successModal').style.display = 'none';
        }

        // Close on outside click
        window.onclick = function(e) {
            if (e.target == document.getElementById('loanModal')) toggleModal(false);
            if (e.target == document.getElementById('successModal')) closeSuccess();
            
            // Dropdown Profile Close
            if(!e.target.closest('#profileBtn')) {
                document.getElementById('profileDropdown')?.classList.remove('show');
            }
        }

        // --- PROFILE DROPDOWN ---
        const profileBtn = document.getElementById('profileBtn');
        if(profileBtn) {
            profileBtn.addEventListener('click', () => {
                document.getElementById('profileDropdown').classList.toggle('show');
            });
        }

        // --- AJAX SUBMIT LOAN ---
        function attachLoanHandler() {
            const loanForm = document.getElementById('loanForm');
            if (!loanForm) return;
            if (loanForm._handlerAttached) return;
            loanForm._handlerAttached = true;

            const agreement = document.getElementById('agreement');
            const btnSubmit = document.getElementById('btnSubmitLoan');
            if (agreement && btnSubmit) {
                btnSubmit.disabled = !agreement.checked;
                agreement.addEventListener('change', function() {
                    btnSubmit.disabled = !this.checked;
                });
            }

            loanForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Client-side validation
                const tanggalPinjamStr = document.getElementById('tanggal_pinjam').value;
                const tanggalKembaliStr = document.getElementById('tanggal_kembali').value;
                
                if (!tanggalPinjamStr || !tanggalKembaliStr) {
                    alert('Tanggal pinjam dan pengembalian harus diisi');
                    return;
                }
                
                if (agreement && !agreement.checked) {
                    alert('Harap setujui ketentuan peminjaman terlebih dahulu.');
                    return;
                }

                const dPinjam = new Date(tanggalPinjamStr + 'T00:00:00');
                const dKembali = new Date(tanggalKembaliStr + 'T00:00:00');
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                // Validasi 1: Tanggal pinjam tidak boleh di masa lalu
                if (dPinjam < today) {
                    alert('Tanggal pinjam tidak boleh di masa lalu. Pilih tanggal hari ini atau di kemudian hari.');
                    return;
                }

                // Validasi 2: Tanggal pengembalian harus lebih maju dari tanggal pinjam
                if (dKembali <= dPinjam) {
                    alert('Tanggal pengembalian harus lebih maju dari tanggal pinjam.');
                    return;
                }

                // Validasi 3: Maksimal peminjaman 30 hari
                const diffMs = dKembali - dPinjam;
                const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
                if (diffDays > 30) {
                    alert('Durasi peminjaman maksimal 30 hari. Durasi yang Anda pilih adalah ' + diffDays + ' hari.');
                    return;
                }

                const formData = new FormData(this);
                formData.set('tanggal_pinjam', tanggalPinjamStr);
                formData.set('tanggal_kembali', tanggalKembaliStr);
                
                fetch("{{ route('peminjaman.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        toggleModal(false);
                        document.getElementById('successModal').style.display = 'flex';
                        loanForm.reset();
                        if (btnSubmit) btnSubmit.disabled = true;
                    } else {
                        alert('Gagal: ' + (data.message || 'Peminjaman gagal'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
            });
        }

        document.addEventListener('DOMContentLoaded', attachLoanHandler);

        // --- AJAX SUBMIT REVIEW ---
        function attachReviewHandler() {
            const reviewForm = document.getElementById('reviewForm');
            if (!reviewForm) return;
            if (reviewForm._handlerAttached) return;
            reviewForm._handlerAttached = true;

            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch("{{ route('ulasan.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Gagal mengirim ulasan'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat mengirim ulasan. Silakan coba lagi.');
                });
            });
        }

        document.addEventListener('DOMContentLoaded', attachReviewHandler);

        // --- DELETE REVIEW ---
        function deleteReview(id) {
            if(!confirm("Hapus ulasan ini?")) return;
            
            fetch(`/ulasan/${id}`, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success || data.message) location.reload();
            });
        }
    </script>
    @include('partials.chat-widget')
    @include('partials.logout-modal')
</body>
</html>