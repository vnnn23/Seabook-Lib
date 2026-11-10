<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Buku - {{ config('app.name', 'SeaBook') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/sybook-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <h1 class="user-page-title">Baca Buku Digital</h1>
            </div>
            <div class="user-topbar-right">
                @auth
                    <span class="role-pill role-user"><i class="fa-solid fa-user"></i> {{ Auth::user()->nama }}</span>
                @endauth
            </div>
        </header>

        <section class="section-common">
            <div class="container">
                <div class="section-header center">
                    <h2>Baca Buku</h2>
                    <p>Nikmati membaca buku langsung di browser</p>
                </div>

                <div class="pdf-viewer">
                    <embed src="{{ asset('pdfs/' . $filename) }}" width="100%" height="800px" type="application/pdf">
                    <p>Jika PDF tidak muncul, <a href="{{ asset('pdfs/' . $filename) }}" target="_blank">klik di sini untuk membuka</a></p>
                </div>
            </div>
        </section>
    </div> <!-- /.user-main-content -->

    <script src="{{ asset('js/sidebar-collapsible.js') }}"></script>
    <script src="{{ asset('js/sybook-script.js') }}"></script>
    @include('partials.chat-widget')
    @include('partials.logout-modal')
</body>
</html>