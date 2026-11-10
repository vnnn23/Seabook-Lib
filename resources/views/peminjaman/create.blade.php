<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku - {{ $buku->judul ?? 'Buku' }} - {{ config('app.name', 'SeaBook') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sybook-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">
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
                <h1 class="user-page-title">Form Pinjam Buku</h1>
            </div>
            <div class="user-topbar-right">
                @auth
                    <span class="role-pill role-user"><i class="fa-solid fa-user"></i> {{ Auth::user()->nama }}</span>
                @endauth
            </div>
        </header>

    <section class="section-common">
        <div class="container">
            <h2>Pinjam Buku: {{ $buku->judul ?? 'Buku tidak ditemukan' }}</h2>
            @if(isset($buku) && $buku)
            <form action="{{ route('peminjaman.store') }}" method="POST" id="peminjamanForm">
                @csrf
                <input type="hidden" name="buku_id" value="{{ $buku->BukuID }}">
            @else
                <div class="alert">Buku tidak tersedia atau telah dihapus.</div>
            @endif
                <div>
                    <label for="tanggal_pinjam">Tanggal Pinjam:</label>
                    <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" required>
                </div>
                
                <div>
                    <label for="tanggal_kembali">Tanggal Pengembalian:</label>
                    <input type="date" id="tanggal_kembali" name="tanggal_kembali" required>
                </div>
                
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin: 20px 0; font-size: 0.9em; color: #475569;">
                    <strong style="color: #1e293b;"><i class="fa-solid fa-circle-info"></i> Ringkasan Aturan Peminjaman:</strong> 
                    <ul style="margin-top: 8px; padding-left: 20px; line-height: 1.6;">
                        <li>Jam operasional pelayanan: <strong>{{ sprintf('%02d:00', config('perpustakaan.jam_buka', 8)) }} - {{ sprintf('%02d:00', config('perpustakaan.jam_tutup', 17)) }} WIB</strong></li>
                        <li>Durasi maksimal peminjaman: <strong>{{ config('perpustakaan.durasi_maksimal_hari', 30) }} hari</strong></li>
                        <li>Batas pinjaman aktif bersamaan: <strong>{{ config('perpustakaan.max_active_loans', 3) }} buku</strong></li>
                        <li>Batas pengambilan buku setelah disetujui: <strong>{{ config('perpustakaan.pickup_expiry_hours', 24) }} jam</strong></li>
                        <li>Dilarang meminjam buku yang sama sebelum buku sebelumnya dikembalikan</li>
                        <li>Harap mengembalikan buku tepat waktu sebelum tanggal jatuh tempo dalam kondisi terawat.</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn-primary">Ajukan Peminjaman</button>
            </form>
        </div>
    </section>
    </div> <!-- /.user-main-content -->

    <script src="{{ asset('js/sidebar-collapsible.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const pinjamValue = `${year}-${month}-${day}`;
            
            const sevenDaysLater = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
            const sYear = sevenDaysLater.getFullYear();
            const sMonth = String(sevenDaysLater.getMonth() + 1).padStart(2, '0');
            const sDay = String(sevenDaysLater.getDate()).padStart(2, '0');
            const kembaliValue = `${sYear}-${sMonth}-${sDay}`;
            
            const pinjamInput = document.getElementById('tanggal_pinjam');
            const kembaliInput = document.getElementById('tanggal_kembali');
            
            if (pinjamInput) {
                pinjamInput.value = pinjamValue;
                pinjamInput.min = pinjamValue;
            }
            if (kembaliInput) {
                kembaliInput.value = kembaliValue;
                kembaliInput.min = pinjamValue;
            }
            
            if (pinjamInput && kembaliInput) {
                pinjamInput.addEventListener('change', function() {
                    kembaliInput.min = this.value;
                    if (kembaliInput.value <= this.value) {
                        const pinjamTime = new Date(this.value);
                        const defaultKembali = new Date(pinjamTime.getTime() + 7 * 24 * 60 * 60 * 1000);
                        const nYear = defaultKembali.getFullYear();
                        const nMonth = String(defaultKembali.getMonth() + 1).padStart(2, '0');
                        const nDay = String(defaultKembali.getDate()).padStart(2, '0');
                        kembaliInput.value = `${nYear}-${nMonth}-${nDay}`;
                    }
                });
            }
        });
    </script>
    @include('partials.chat-widget')
    @include('partials.logout-modal')
</body>
</html>