{{-- Modal Konfirmasi Logout Universal SyBook --}}
<link rel="stylesheet" href="{{ asset('css/logout-modal.css') }}">

<div id="sybookLogoutModal" class="sybook-logout-overlay" aria-hidden="true" role="dialog" aria-labelledby="sybookLogoutTitle">
    <div class="sybook-logout-card" role="document">
        <button type="button" class="sybook-logout-close" id="sybookLogoutClose" aria-label="Tutup dialog">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="sybook-logout-icon-wrap">
            <div class="sybook-logout-icon">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </div>
        </div>

        <h3 class="sybook-logout-title" id="sybookLogoutTitle">Konfirmasi Logout</h3>
        <p class="sybook-logout-desc">
            Apakah Anda yakin ingin keluar dari akun SeaBook? Sesi aktif Anda akan berakhir dan Anda perlu masuk kembali untuk mengakses layanan perpustakaan.
        </p>

        @auth
        @php
            $authUser = Auth::user();
            $authFoto = $authUser->foto_profil ? asset('storage/' . $authUser->foto_profil) : null;
        @endphp
        <div class="sybook-logout-user-box">
            <div class="sybook-logout-avatar">
                @if($authFoto)
                    <img src="{{ $authFoto }}" alt="{{ $authUser->nama }}">
                @else
                    {{ substr($authUser->nama, 0, 1) }}
                @endif
            </div>
            <div class="sybook-logout-user-details">
                <span class="sybook-logout-user-name">{{ $authUser->nama }}</span>
                <span class="sybook-logout-user-email">{{ $authUser->email }}</span>
            </div>
        </div>
        @endauth

        <div class="sybook-logout-actions">
            <button type="button" class="btn-logout-cancel" id="sybookLogoutCancelBtn">
                Batal
            </button>
            <button type="button" class="btn-logout-confirm" id="sybookLogoutConfirmBtn">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Ya, Logout
            </button>
        </div>
    </div>
</div>

<form id="sybookGlobalLogoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<script src="{{ asset('js/logout-confirm.js') }}" defer></script>

