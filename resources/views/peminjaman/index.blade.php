<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peminjaman - {{ config('app.name', 'SeaBook') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sybook-style.css') }}">
    @include('partials.motion')
</head>
<body>
    <nav class="navbar">
        <div class="container nav-wrapper">
            <div class="logo">
                <span class="logo-main">SeaBook</span>
                <span class="logo-sub">Library</span>
            </div>
            <button class="hamburger" id="hamburger">☰</button>
            <div class="nav-right" id="nav-menu">
                <a href="/" class="nav-link-sybook"><img src="https://via.placeholder.com/20" alt=""> SeaBook</a>
                <a href="/books" class="nav-link-buku">Buku</a>
                @auth
                    <div class="profile-nav">
                        <a href="@if(Auth::user()->role === 'admin') /dashboardadmin @elseif(Auth::user()->role === 'petugas') /dashboardpetugas @else /dashboard @endif" class="profile-link">
                            <img src="{{ Auth::user()->foto_profil ?? 'https://via.placeholder.com/32x32/667eea/ffffff?text=' . substr(Auth::user()->nama, 0, 1) }}"
                                 alt="Profile"
                                 class="profile-avatar"
                                 title="{{ Auth::user()->nama }}">
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <section class="section-common">
        <div class="container">
            <h2>Kelola Peminjaman</h2>
            @foreach($peminjaman as $p)
                <div class="book-card">
                    <h3>{{ $p->buku->judul ?? 'Buku terhapus' }}</h3>
                    <p>User: {{ $p->user->nama }}</p>
                    <p>Status: {{ $p->statuspeminjaman }}</p>
                    <p>Tanggal Pinjam: {{ $p->tanggal_pinjam->format('d/m/Y H:i') }}</p>
                    <p>Tanggal Kembali: {{ $p->tanggal_kembali->format('d/m/Y H:i') }}</p>
                    @if($p->statuspeminjaman === 'menunggu persetujuan')
                        <form action="{{ route('peminjaman.confirm', $p->PeminjamanID) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-primary">Konfirmasi Peminjaman</button>
                        </form>
                    @endif
                    @if($p->statuspeminjaman === 'menunggu konfirmasi pengembalian' || $p->statuspeminjaman === 'sedang dipinjam')
                        <form action="{{ route('peminjaman.confirmReturn', $p->PeminjamanID) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-primary">Konfirmasi Pengembalian</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</body>
</html>