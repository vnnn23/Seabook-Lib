<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SeaBook') }} - Perpustakaan Digital</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- 1. RESET & VARIABLES --- */
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --accent: #3b82f6;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-sub: #475569;
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        body { background-color: var(--bg-body); color: var(--text-main); overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* --- 2. ANIMATION UTILS (SCROLL REVEAL) --- */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        .reveal-left { opacity: 0; transform: translateX(-50px); transition: all 0.8s ease-out; }
        .reveal-right { opacity: 0; transform: translateX(50px); transition: all 0.8s ease-out; }
        .reveal-left.active, .reveal-right.active { opacity: 1; transform: translateX(0); }

        .stagger-1 { transition-delay: 0.1s; }
        .stagger-2 { transition-delay: 0.2s; }
        .stagger-3 { transition-delay: 0.3s; }

        /* --- 3. NAVBAR --- */
        .navbar {
            position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            z-index: 1000 !important; padding: 15px 0;
            transition: var(--transition);
        }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; }
        .nav-wrapper { display: flex; justify-content: space-between; align-items: center; }

        .logo { font-size: 1.5rem; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 8px; }
        .logo-sub { color: var(--text-main); font-size: 0.9rem; font-weight: 600; opacity: 0.8; }

        .nav-right { display: flex; align-items: center; gap: 30px; }
        .nav-link { font-weight: 600; color: var(--text-sub); font-size: 0.95rem; transition: var(--transition); position: relative; }
        .nav-link:hover { color: var(--primary); }
        .nav-link::after {
            content: ''; position: absolute; width: 0; height: 2px; bottom: -4px; left: 0;
            background-color: var(--primary); transition: var(--transition);
        }
        .nav-link:hover::after { width: 100%; }

        /* Profile & Buttons */
        .btn-primary {
            background: var(--primary); color: white; padding: 10px 24px;
            border-radius: 50px; font-weight: 600; border: none; cursor: pointer;
            transition: var(--transition); box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.3); }
        
        .profile-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            border: 2px solid var(--primary); box-shadow: var(--shadow-sm);
            cursor: pointer; transition: var(--transition);
            display: flex; align-items: center; justify-content: center;
            background: var(--primary) no-repeat center / cover;
            color: white; font-weight: 700; font-size: 1rem;
            overflow: hidden; flex-shrink: 0; user-select: none;
        }
        .profile-avatar:hover { transform: scale(1.08); }

        /* Profile Dropdown */
        .profile-wrapper { position: relative; }
        .profile-dropdown {
            position: absolute; top: 55px; right: 0; width: 260px;
            background: white; border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            opacity: 0; visibility: hidden; transform: translateY(-10px);
            transition: all 0.2s ease; overflow: hidden; z-index: 1000;
        }
        .profile-dropdown.show { opacity: 1; visibility: visible; transform: translateY(0); }
        
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


        /* --- 4. HERO SECTION --- */
        .hero-section {
            padding: 160px 0 100px;
            background: radial-gradient(circle at top right, #eff6ff 0%, #ffffff 40%, #f1f5f9 100%);
            overflow: hidden; position: relative;
        }
        .blob {
            position: absolute; width: 500px; height: 500px; background: linear-gradient(180deg, rgba(37, 99, 235, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-radius: 50%; filter: blur(80px); z-index: 0;
        }
        .blob-1 { top: -100px; right: -100px; }
        .blob-2 { bottom: -100px; left: -100px; background: linear-gradient(180deg, rgba(236, 72, 153, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%); }

        .hero-wrapper { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 1; }
        .hero-text { max-width: 600px; }
        .hero-text h1 { font-size: 3.2rem; line-height: 1.2; font-weight: 800; color: var(--text-main); margin-bottom: 20px; }
        .hero-text span { background: linear-gradient(135deg, #2563eb, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-text p { font-size: 1.1rem; color: var(--text-sub); line-height: 1.6; margin-bottom: 30px; }
        
        .hero-image img { width: 100%; max-width: 450px; animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

        /* --- 5. SECTIONS COMMON --- */
        .section-common { padding: 100px 0; }
        .section-header { text-align: center; margin-bottom: 60px; max-width: 700px; margin-left: auto; margin-right: auto; }
        .section-header h2 { font-size: 2.2rem; font-weight: 800; margin-bottom: 15px; }
        .section-header p { color: var(--text-sub); font-size: 1.1rem; }

        /* Kenali Kami */
        .kenali-wrapper { display: flex; align-items: center; gap: 50px; }
        .kenali-text { flex: 1; }
        .kenali-video { flex: 1; position: relative; }
        .video-placeholder {
            width: 100%; height: 350px; border-radius: 20px; overflow: hidden;
            position: relative; box-shadow: var(--shadow-lg); cursor: pointer; transition: var(--transition);
        }
        .video-placeholder:hover { transform: scale(1.02); }
        .video-placeholder img { width: 100%; height: 100%; object-fit: cover;  }
        .play-button {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 70px; height: 70px; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--primary);
            box-shadow: 0 0 20px rgba(255,255,255,0.4); transition: var(--transition);
        }
        .video-placeholder:hover .play-button { transform: translate(-50%, -50%) scale(1.1); background: var(--primary); color: white; }

        /* --- 6. BOOKS GRID (SMALL & NICER) --- */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Default Mobile */
            gap: 25px; /* Jarak antar kartu */
            margin-bottom: 50px;
            justify-content: center;
        }

        /* Responsive Breakpoints */
        @media (min-width: 640px) { .books-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 768px) { .books-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (min-width: 1024px) { 
            .books-grid { 
                grid-template-columns: repeat(5, 1fr); /* 5 Kolom di desktop */
                max-width: 1050px; /* Batasi lebar agar tidak terlalu renggang */
                margin-left: auto;
                margin-right: auto;
            } 
        }

        /* Card Style */
        .book-card {
            background: transparent;
            border-radius: 12px;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .book-card:hover { transform: translateY(-8px); }

        /* Cover Art Container */
        .book-cover-frame {
            width: 100%;
            aspect-ratio: 2/3;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1); /* Shadow lembut */
            margin-bottom: 12px;
            position: relative;
            background: #e2e8f0;
        }

        .book-cover-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        /* Zoom effect on hover */
        .book-card:hover .book-cover-frame img {
            transform: scale(1.08);
        }

        /* Typography */
        .book-card h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
            line-height: 1.35;
            padding: 0 5px;
            
            /* Limit to 2 lines */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-card p {
            font-size: 0.8rem;
            color: var(--text-sub);
            font-weight: 500;
        }

        /* --- 7. FAQ ACCORDION --- */
        .faq-wrapper { max-width: 800px; margin: 0 auto; }
        .faq-item { margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; }
        .accordion {
            background: white; color: var(--text-main); cursor: pointer; padding: 20px; width: 100%;
            text-align: left; border: none; outline: none; transition: 0.4s; font-size: 1.1rem; font-weight: 600;
            display: flex; justify-content: space-between; align-items: center; border-radius: 12px;
        }
        .accordion:hover { background-color: #f1f5f9; }
        .accordion::after { content: '+'; font-size: 1.5rem; color: var(--primary); }
        .accordion.active::after { content: '-'; }
        .panel {
            padding: 0 20px; background-color: white; max-height: 0; overflow: hidden;
            transition: max-height 0.3s ease-out; color: var(--text-sub); line-height: 1.6;
        }

        /* --- 8. FOOTER --- */
        .main-footer { background: #1e40af; color: white; padding: 80px 0 30px; }
        .footer-content { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 40px; margin-bottom: 50px; }
        .footer-logo { font-size: 1.8rem; font-weight: 800; margin-bottom: 20px; color: white; }
        .footer-links h5, .address-box h5 { color: #94a3b8; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; }
        .footer-links ul li { margin-bottom: 10px; }
        .footer-links a { color: #e2e8f0; transition: 0.2s; }
        .footer-links a:hover { color: var(--accent); }
        .address-box p { color: #cbd5e1; margin-bottom: 10px; line-height: 1.6; font-size: 0.95rem; }
        
        .footer-bottom { border-top: 1px solid #ced3db; padding-top: 30px; text-align: center; color: #babfc5; font-size: 0.9rem; }

        /* --- 9. MODAL --- */
        .modal {
            display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(5px);
            align-items: center; justify-content: center;
        }
        .modal-content {
            background-color: white; padding: 40px; border-radius: 20px; width: 90%; max-width: 450px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); animation: zoomIn 0.3s ease; position: relative;
        }
        @keyframes zoomIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        .close { position: absolute; top: 20px; right: 25px; font-size: 28px; font-weight: bold; color: #94a3b8; cursor: pointer; transition: 0.2s; }
        .close:hover { color: var(--text-main); }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.4s; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: 0.2s; }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .password-wrapper input {
            width: 100%;
            padding-right: 44px !important;
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 2;
        }
        .password-toggle-btn:hover {
            color: var(--primary);
        }
        .password-toggle-btn:focus {
            outline: none;
            color: var(--primary);
        }

        @keyframes modalShake {
            0%, 100% { transform: scale(1) translateX(0); }
            20%, 60% { transform: scale(1.01) translateX(-6px); }
            40%, 80% { transform: scale(1.01) translateX(6px); }
        }
        .modal-content.modal-shake {
            animation: modalShake 0.35s ease;
        }
        
        .method-buttons { display: flex; flex-direction: column; gap: 15px; margin-top: 20px; }
        .register-link, .back-link { margin-top: 20px; text-align: center; font-size: 0.9rem; }
        .register-link a, .back-link a { color: var(--primary); font-weight: 600; }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-text h1 { font-size: 2.5rem; }
            .hero-wrapper { flex-direction: column-reverse; text-align: center; }
            .hero-text { margin-top: 40px; }
            .kenali-wrapper { flex-direction: column; }
            .nav-right { display: none; } /* Add JS hamburger toggle logic if needed */
            .hamburger { display: block; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        }
        @media (min-width: 769px) { .hamburger { display: none; } }
    </style>
    @include('partials.motion')
</head>
<body>

    <nav class="navbar">
        <div class="container nav-wrapper">
            <a href="/" class="logo">
                <i class="fa-solid fa-book-open-reader"></i>
                SeaBook
            </a>
            
            <button class="hamburger" id="hamburger"><i class="fa-solid fa-bars"></i></button>
            
            <div class="nav-right" id="nav-menu">
                <a href="#" class="nav-link">Beranda</a>
                <a href="#
                kenali-kami" class="nav-link">Tentang</a>
                <a href="#kelola-perpustakaan" class="nav-link">Koleksi</a>
                <a href="#layanan" class="nav-link">FAQ</a>
                
                @auth
                    <div class="profile-wrapper" id="profileBtn" style="cursor: pointer;">
                        @php
                            $fotoPath = Auth::user()->foto_profil ? asset('storage/' . Auth::user()->foto_profil) : null;
                            $initials = strtoupper(substr(Auth::user()->nama ?? 'U', 0, 2));
                        @endphp
                        <div class="profile-avatar" style="@if($fotoPath) background-image: url('{{ $fotoPath }}'); @endif">
                            <span class="avatar-initials" style="{{ $fotoPath ? 'display: none;' : '' }}">
                                {{ $initials }}
                            </span>
                            @if($fotoPath)
                                <img src="{{ $fotoPath }}" alt="" style="display: none;" onerror="this.parentElement.style.backgroundImage='none'; const s = this.previousElementSibling; if(s) s.style.display='block';">
                            @endif
                        </div>
                        
                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="dropdown-header">
                                <h4>{{ Auth::user()->nama }}</h4>
                                <p>{{ Auth::user()->email }}</p>
                            </div>
                            <div class="dropdown-item">
                                <a href="{{ route('profile.edit') }}" style="display: flex; align-items: center; gap: 10px; color: inherit; text-decoration: none; width: 100%;">
                                    <i class="fa-solid fa-user-circle"></i> Profil Saya
                                </a>
                            </div>
                            <div class="dropdown-item danger">
                                <button type="button" class="btn-logout" data-action="logout" style="background:none; border:none; width:100%; text-align:left; color:inherit; font:inherit; cursor:pointer; display:flex; align-items:center; gap:10px;">
                                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <button class="btn-primary" onclick="openModal()">Masuk / Daftar</button>
                @endauth
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        
        <div class="container hero-wrapper">
            <div class="hero-text reveal-left">
                <h1>Perpustakaan <span>Modern</span><br>untuk <span>Generasi Digital</span></h1>
                <p>{{ config('app.name', 'SeaBook') }} adalah platform literasi masa depan. Akses ribuan buku kapan saja, di mana saja.</p>
                <div style="display: flex; gap: 15px; @media(min-width:768px){justify-content: flex-start;}">
                    <a href="#kelola-perpustakaan" class="btn-primary">Mulai Membaca</a>
                    <a href="#kenali-kami" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid #e2e8f0;">Pelajari Lebih</a>
                </div>
            </div>
            <div class="hero-image reveal-right">
                <img src="{{ asset('asset/banner1.png') }}" alt="{{ config('app.name', 'SeaBook') }} App Preview" onerror="this.src='https://placehold.co/400x500/png?text=Mobile+App+Mockup'">
            </div>
        </div>
    </header>

    <section class="section-common" id="kenali-kami">
        <div class="container kenali-wrapper">
            <div class="kenali-text reveal-left">
                <div class="section-header" style="text-align: left; margin-bottom: 30px; margin-left: 0;">
                    <h2>Kenali {{ config('app.name', 'SeaBook') }}</h2>
                    <p style="margin-bottom: 20px;">Platform perpustakaan digital tanpa batas untuk semua kalangan.</p>
                </div>
                <p style="line-height: 1.8; color: var(--text-sub); margin-bottom: 20px;">
                    Kami hadir sebagai wadah bagi para pecinta buku untuk menjelajahi, meminjam, dan membaca berbagai koleksi buku digital secara praktis. 
                    Dengan antarmuka yang modern, {{ config('app.name', 'SeaBook') }} siap menemani perjalanan membaca Anda.
                </p>
                <ul style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px;">
                    <li><i class="fa-solid fa-check-circle" style="color: var(--primary);"></i> Akses 24/7</li>
                    <li><i class="fa-solid fa-check-circle" style="color: var(--primary);"></i> Koleksi Lengkap</li>
                    <li><i class="fa-solid fa-check-circle" style="color: var(--primary);"></i> Mudah Digunakan</li>
                    <li><i class="fa-solid fa-check-circle" style="color: var(--primary);"></i> Gratis Peminjaman</li>
                </ul>
            </div>
            <div class="kenali-video reveal-right">
                <div class="video-placeholder">
                    <img src="{{ asset('asset/banner2.jpg') }}" alt="Banner 2">
                </div>
            </div>
        </div>
    </section>

    <section class="section-common" id="kelola-perpustakaan" style="background: white;">
        <div class="container">
            <div class="section-header reveal">
                <h2>Koleksi Pilihan</h2>
                <p>Jelajahi buku-buku terbaru yang sedang tren saat ini.</p>
            </div>
            
            <div class="books-grid">
                @foreach(\App\Models\Buku::with('kategoris')->latest()->take(5)->get() as $index => $book)
                <div class="book-card reveal stagger-{{ ($index % 3) + 1 }}" title="{{ $book->judul }}">
                    <div class="book-cover-frame">
                        <img src="{{ $book->cover ? asset('storage/' . $book->cover) : 'https://placehold.co/150x230/e2e8f0/64748b?text=' . urlencode($book->judul) }}" alt="{{ $book->judul }}">
                    </div>
                    <h3>{{ $book->judul }}</h3>
                    <p>{{ $book->penulis }}</p>
                </div>
                @endforeach
            </div>
            
            <div style="text-align: center;" class="reveal">
                <a href="/books" class="btn-primary" style="padding: 12px 40px;">Lihat Semua Koleksi</a>
            </div>
        </div>
    </section>

    <section class="section-common" id="layanan">
        <div class="container">
            <div class="section-header reveal">
                <h2>Pusat Bantuan</h2>
                <p>Jawaban untuk pertanyaan yang sering diajukan pengguna.</p>
            </div>
            <div class="faq-wrapper reveal">
                <div class="faq-item">
                    <button class="accordion">Bagaimana cara meminjam buku?</button>
                    <div class="panel">
                        <p style="padding: 15px 0;">Cukup login ke akun Anda, pilih buku yang diinginkan, dan klik tombol "Pinjam". Buku akan masuk ke rak digital Anda.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="accordion">Berapa lama durasi peminjaman?</button>
                    <div class="panel">
                        <p style="padding: 15px 0;">Durasi standar peminjaman adalah 7 hari. Anda dapat memperpanjangnya jika belum ada antrian peminjam lain.</p>
                    </div>
                </div>
                 <div class="faq-item">
                    <button class="accordion">Apakah layanan ini gratis?</button>
                    <div class="panel">
                        <p style="padding: 15px 0;">Ya, {{ config('app.name', 'SeaBook') }} berkomitmen menyediakan akses literasi gratis untuk seluruh anggota terdaftar.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container footer-content">
            <div class="footer-left">
                <div class="footer-logo">{{ config('app.name', 'SeaBook') }}</div>
                <p style="color: #94a3b8; max-width: 300px; line-height: 1.6;">Membuka jendela dunia melalui satu sentuhan jari. Platform perpustakaan digital terdepan di Indonesia.</p>
            </div>
            <div class="footer-links">
                <h5>Navigasi</h5>
                <ul>
                    <li><a href="#">Beranda</a></li>
                    <li><a href="#kelola-perpustakaan">Koleksi Buku</a></li>
                    <li><a href="#kenali-kami">Tentang Kami</a></li>
                    <li><a href="#layanan">Bantuan</a></li>
                </ul>
            </div>
            <div class="address-box">
                <h5>Hubungi Kami</h5>
                <p><i class="fa-solid fa-location-dot"></i> Jl. Palmerah Barat No.29-37, Jakarta Pusat</p>
                <p><i class="fa-solid fa-phone"></i> 0895-1787-3784</p>
                <p><i class="fa-solid fa-envelope"></i> help@seabook.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; 2026 {{ config('app.name', 'SeaBook') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <div id="authModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            
            @if(session('success'))
                <div style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; text-align: center; border: 1px solid #c8e6c9;">
                    <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; text-align: center; border: 1px solid #fecaca;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> {{ session('error') }}
                </div>
            @endif

            <div id="method-selection" class="tab-content active">
                <h2 style="text-align: center; margin-bottom: 20px;">Selamat Datang</h2>
                <p style="text-align: center; color: var(--text-sub); margin-bottom: 30px;">Silakan pilih metode masuk akun Anda</p>
                <div class="method-buttons">
                    <button class="btn-primary" onclick="showEmailLogin()" style="width: 100%; display: flex; justify-content: center; align-items: center; padding: 12px;">
                        <i class="fa-regular fa-envelope" style="margin-right: 10px; font-size: 1.1rem;"></i> Masuk dengan Email
                    </button>
                    <button class="btn-primary" onclick="showRegister()" style="width: 100%; display: flex; justify-content: center; align-items: center; padding: 12px; background: white; color: var(--primary); border: 1.5px solid var(--primary);">
                        <i class="fa-solid fa-user-plus" style="margin-right: 10px; font-size: 1.1rem;"></i> Buat Akun Baru
                    </button>
                </div>
            </div>

            <div id="email-login" class="tab-content">
                <h2 style="text-align: center; margin-bottom: 20px;">Login Email</h2>
                @if ($errors->any() && !$errors->has('nama'))
                    <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9rem;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="loginPassword" name="password" required placeholder="••••••••">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('loginPassword', this)" title="Lihat password" aria-label="Lihat atau sembunyikan password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Login</button>
                </form>
                <div class="register-link" style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
                    Belum punya akun? <a href="#" onclick="showRegister()">Daftar sekarang</a>
                </div>
                <div class="back-link" style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
                    <a href="#" onclick="showMethodSelection()">Kembali</a>
                </div>
            </div>

            <div id="register-tab" class="tab-content">
                <h2 style="text-align: center; margin-bottom: 20px;">Buat Akun Baru</h2>
                @if ($errors->any() && ($errors->has('nama') || session('open_register_modal')))
                     <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9rem;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <form action="{{ route('register.post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Nama lengkap Anda">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="registerPassword" name="password" required placeholder="Minimal 6 karakter">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('registerPassword', this)" title="Lihat password" aria-label="Lihat atau sembunyikan password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="registerPasswordConfirm" name="password_confirmation" required placeholder="Ulangi password">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('registerPasswordConfirm', this)" title="Lihat password" aria-label="Lihat atau sembunyikan konfirmasi password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Foto Profil (Opsional)</label>
                        <input type="file" name="foto_profil" accept="image/*" style="padding: 8px;">
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Daftar</button>
                </form>
                <div class="register-link" style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
                    Sudah punya akun? <a href="#" onclick="showEmailLogin()">Masuk sekarang</a>
                </div>
                <div class="back-link" style="margin-top: 10px; text-align: center; font-size: 0.9rem;">
                    <a href="#" onclick="showMethodSelection()">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. SCROLL REVEAL LOGIC
        document.addEventListener('DOMContentLoaded', () => {
            const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');

            const revealOnScroll = () => {
                const windowHeight = window.innerHeight;
                const elementVisible = 100;

                reveals.forEach((reveal) => {
                    const elementTop = reveal.getBoundingClientRect().top;
                    if (elementTop < windowHeight - elementVisible) {
                        reveal.classList.add('active');
                    }
                });
            };

            window.addEventListener('scroll', revealOnScroll);
            revealOnScroll(); // Trigger once on load
        });

        // 2. MODAL LOGIC (HANYA BISA DITUTUP VIA TOMBOL 'X')
        const modal = document.getElementById('authModal');
        
        function openModal(type) {
            modal.style.display = 'flex';
            if (type === 'register') {
                showRegister();
            } else if (type === 'email-login') {
                showEmailLogin();
            } else {
                showMethodSelection();
            }
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        // Khusus pop-up login/register: Dilarang menutup modal saat klik backdrop luar
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.remove('modal-shake');
                    void modalContent.offsetWidth;
                    modalContent.classList.add('modal-shake');
                }
            }
        });

        // Toggle Lihat / Sembunyikan Password
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
                btn.setAttribute('aria-label', 'Sembunyikan password');
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
                btn.setAttribute('title', 'Lihat password');
                btn.setAttribute('aria-label', 'Lihat password');
            }
        }

        @if(session('open_login_modal') || session('error') || session('success') || $errors->any())
            window.addEventListener('DOMContentLoaded', function() {
                @if(session('open_register_modal') || $errors->has('nama'))
                    openModal('register');
                    showRegister();
                @else
                    openModal('email-login');
                    showEmailLogin();
                @endif
            });
        @endif

        // 3. TAB SWITCHING
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        }

        function showEmailLogin() { switchTab('email-login'); }
        function showRegister() { switchTab('register-tab'); }
        function showMethodSelection() { switchTab('method-selection'); }

        // 4. ACCORDION LOGIC
        const acc = document.getElementsByClassName("accordion");
        for (let i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                const panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + "px";
                }
            });
        }
        
        // 5. PROFILE DROPDOWN LOGIC
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        
        if(profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileDropdown.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            window.addEventListener('click', (event) => {
                if(!event.target.closest('#profileBtn')) {
                    profileDropdown.classList.remove('show');
                }
            });
        }

        // 6. NAVBAR MOBILE TOGGLE (Optional if using hamburger)
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('nav-menu');
        
        hamburger.addEventListener('click', () => {
            // Simple toggle logic for mobile (css handles display mostly)
            if(navMenu.style.display === 'flex') {
                navMenu.style.display = 'none';
            } else {
                navMenu.style.display = 'flex';
                navMenu.style.flexDirection = 'column';
                navMenu.style.position = 'absolute';
                navMenu.style.top = '70px';
                navMenu.style.left = '0';
                navMenu.style.width = '100%';
                navMenu.style.background = 'white';
                navMenu.style.padding = '20px';
                navMenu.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            }
        });
    </script>

    @include('partials.chat-widget')
    @include('partials.logout-modal')
</body>
</html>