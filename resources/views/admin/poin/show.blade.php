<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinjau Akun & Log Poin - {{ $user->nama }} - {{ config('app.name', 'SeaBook') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sybook-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">

    <script>
        if (localStorage.getItem('sybook_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>

    <style>
        :root {
            --primary: #2563eb;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-sub: #64748b;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-body); color: var(--text-main); margin: 0; }
        .container-admin { padding: 28px 32px; max-width: 1400px; width: 100%; margin: 0 auto; }
        .table-card { background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .card-header { padding: 14px 20px; border-bottom: 1px solid var(--border); background: #f8fafc; font-weight: 700; font-size: 0.95rem; color: var(--text-main); display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 18px; border-bottom: 1px solid var(--border); }
        th { background: #f8fafc; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-sub); }
        td { font-size: 0.88rem; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .badge-aman { background: #dcfce7; color: #166534; }
        .badge-peringatan { background: #fef3c7; color: #92400e; }
        .badge-tinjau { background: #fee2e2; color: #991b1b; }
        .btn { padding: 7px 15px; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; border: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-outline { background: white; border: 1px solid var(--border); color: var(--text-main); }
        .alert { padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-success { background: #e8f5e9; color: #1e7e34; border: 1px solid #c3e6cb; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 0.92rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--primary); outline: none; }
    </style>
    @include('partials.motion')
</head>
<body class="staff-layout">

    <!-- Collapsible Staff Sidebar -->
    @include('partials.staff-sidebar', ['active' => 'poin'])

    <!-- Main Content Area -->
    <div class="staff-main-content">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <h2 class="staff-page-title">Tinjau Akun Anggota: {{ $user->nama }}</h2>
            </div>
            <div class="staff-topbar-right">
                <a href="{{ route('admin.poin.index') }}" class="btn btn-outline" style="padding: 6px 14px; font-size: 0.84rem;">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Poin
                </a>
                <span class="role-pill role-admin">
                    <i class="fa-solid fa-user-shield"></i> Administrator
                </span>
            </div>
        </header>

        <div class="container-admin">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $isPending = ($user->status_akun === \App\Models\User::STATUS_PENDING);
            $isDitolak = ($user->status_akun === \App\Models\User::STATUS_DITOLAK);
            $userPoin = (int) ($user->poin ?? 0);
            $ambangTinjau = (int) config('perpustakaan.ambang_tinjau', 60);
            $ambangPeringatan = (int) config('perpustakaan.ambang_peringatan', 75);
            $isTinjau = (!$isPending && !$isDitolak) && (($userPoin <= $ambangTinjau) || ($user->status_akun === \App\Models\User::STATUS_TINJAU));
            $isPeringatan = (!$isPending && !$isDitolak && !$isTinjau) && ($userPoin <= $ambangPeringatan);
        @endphp

        @if($isPending)
            <div class="alert" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-hourglass-half" style="font-size: 1.3rem; color: #d97706;"></i>
                    <div>
                        <strong style="font-size: 0.95rem;">Akun Anggota Menunggu Persetujuan Admin</strong>
                        <p style="margin: 3px 0 0; font-size: 0.85rem; color: #b45309;">Akun ini belum disetujui, belum memiliki poin aktif, dan belum dapat meminjam buku. Poin perdana (100 poin) akan diberikan otomatis setelah akun disetujui.</p>
                    </div>
                </div>
                <a href="{{ route('admin.members.approval') }}" class="btn" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 7px 16px; border-radius: 8px; font-weight: 600; text-decoration: none; box-shadow: 0 2px 4px rgba(37,99,235,0.2);">
                    <i class="fa-solid fa-user-check"></i> Proses Persetujuan
                </a>
            </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <!-- Kartu Info Anggota & Poin -->
            <div class="table-card" style="margin-bottom: 0;">
                <div class="card-header">
                    <span>Profil & Skor Poin Anggota</span>
                    <span style="font-size: 0.82rem; color: var(--text-sub);">ID Member: #{{ $user->UserID }}</span>
                </div>
                <div style="padding: 22px;">
                    <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 18px;">
                        @if($user->foto_profil)
                            <img src="{{ asset('storage/' . $user->foto_profil) }}" style="width: 55px; height: 55px; border-radius: 50%; object-fit: cover;">
                        @else
                            <div style="width: 55px; height: 55px; border-radius: 50%; background: #eff6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem;">
                                {{ substr($user->nama, 0, 2) }}
                            </div>
                        @endif
                        <div>
                            <h2 style="margin: 0 0 4px; font-size: 1.15rem; font-weight: 700;">{{ $user->nama }}</h2>
                            <p style="color: var(--text-sub); margin: 0; font-size: 0.88rem;">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; font-size: 0.88rem; color: var(--text-sub);">
                        <div><strong>No. Telp:</strong> {{ $user->nomor_telepon ?? '-' }}</div>
                        <div><strong>Jenis Kelamin:</strong> {{ $user->jenis_kelamin ?? '-' }}</div>
                        <div><strong>Alamat:</strong> {{ $user->alamat ?? '-' }}</div>
                        <div><strong>Kecamatan:</strong> {{ $user->kecamatan ?? '-' }}</div>
                    </div>
                    
                    <div style="display: flex; gap: 25px; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border);">
                        <div>
                            <span style="font-size: 0.82rem; color: var(--text-sub); display: block; margin-bottom: 4px;">Skor Poin Saat Ini:</span>
                            @if($isPending)
                                <div style="font-size: 1.5rem; font-weight: 800; color: #d97706; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-hourglass-half" style="font-size: 1.1rem;"></i> -
                                </div>
                                <span style="font-size: 0.82rem; color: #b45309; font-weight: 600;">Belum Ada Poin</span>
                            @elseif($isDitolak)
                                <span style="font-size: 1.75rem; font-weight: 800; color: #dc2626;">
                                    0
                                </span>
                                <span style="font-size: 0.85rem; color: #dc2626; font-weight: 600;">(Ditolak)</span>
                            @else
                                <span style="font-size: 1.75rem; font-weight: 800; color: {{ $isTinjau ? '#dc2626' : ($isPeringatan ? '#d97706' : '#16a34a') }};">
                                    {{ $userPoin }}
                                </span>
                                <span style="font-size: 0.88rem; color: var(--text-sub); font-weight: 600;">/ 100</span>
                            @endif
                        </div>
                        <div>
                            <span style="font-size: 0.82rem; color: var(--text-sub); display: block; margin-bottom: 6px;">Status Akses Peminjaman:</span>
                            @if($isPending)
                                <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.88rem; padding: 6px 14px;">
                                    <i class="fa-solid fa-clock"></i> Menunggu Persetujuan
                                </span>
                            @elseif($isDitolak)
                                <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.88rem; padding: 6px 14px;">
                                    <i class="fa-solid fa-circle-xmark"></i> Pendaftaran Ditolak
                                </span>
                            @elseif($isTinjau)
                                <span class="badge badge-tinjau" style="font-size: 0.88rem; padding: 6px 14px;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Tinjau Akun (Diblokir)
                                </span>
                            @elseif($isPeringatan)
                                <span class="badge badge-peringatan" style="font-size: 0.88rem; padding: 6px 14px;">
                                    <i class="fa-solid fa-bell"></i> Peringatan
                                </span>
                            @else
                                <span class="badge badge-aman" style="font-size: 0.88rem; padding: 6px 14px;">
                                    <i class="fa-solid fa-circle-check"></i> Aktif & Aman
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Form Peninjauan Akun Oleh Admin -->
            <div class="table-card" style="margin-bottom: 0;">
                <div class="card-header">
                    <span><i class="fa-solid fa-user-check" style="color: var(--primary);"></i> Form Peninjauan Akun Member</span>
                </div>
                <div style="padding: 22px;">
                    <form action="{{ route('admin.poin.adjust', $user->UserID) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Keputusan / Tindakan Tinjauan Admin</label>
                            <select name="aksi" id="aksiSelect" required onchange="handleAksiChange(this.value)">
                                <option value="pulihkan_reset" {{ $isTinjau ? 'selected' : '' }}>Pulihkan Akun & Reset ke 100 Poin</option>
                                <option value="tambah">Tambah Poin Anggota</option>
                                <option value="kurangi">Kurangi Poin Anggota</option>
                                <option value="set_status">Ubah Status Akun Langsung</option>
                            </select>
                        </div>

                        <div class="form-group" id="groupJumlahPoin" style="display: none;">
                            <label id="labelJumlahPoin">Jumlah Poin</label>
                            <input type="number" name="jumlah_poin" id="inputJumlahPoin" min="1" max="100" value="10" placeholder="Contoh: 15">
                        </div>

                        <div class="form-group" id="groupStatusAkun" style="display: none;">
                            <label>Status Akun Baru</label>
                            <select name="status_tujuan">
                                <option value="aktif">Aktif (Bisa Meminjam)</option>
                                <option value="tinjau">Tinjau Akun (Tangguhkan Peminjaman)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Alasan / Catatan Peninjauan (Wajib Diisi)</label>
                            <textarea name="alasan" rows="3" required placeholder="Contoh: Pengguna telah mengganti buku yang hilang / dispensasi perpustakaan..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                            <i class="fa-solid fa-check-double"></i> Terapkan Hasil Peninjauan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Log Pelanggaran & Peninjauan -->
        <div class="table-card">
            <div class="card-header">
                <span>Riwayat Log Mutasi Poin & Peninjauan Akun</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 150px;">Waktu</th>
                        <th style="width: 160px;">Jenis Kejadian</th>
                        <th style="width: 130px;">Perubahan Poin</th>
                        <th>Keterangan / Buku Terkait</th>
                        <th style="width: 150px;">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->poinLogs as $log)
                        <tr>
                            <td style="color: var(--text-sub); font-size: 0.85rem;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong style="text-transform: capitalize;">{{ $log->jenis }}</strong>
                            </td>
                            <td>
                                <span style="font-weight: 800; color: {{ $log->poin < 0 ? '#dc2626' : '#16a34a' }};">
                                    {{ $log->poin > 0 ? '+' . $log->poin : $log->poin }} Poin
                                </span>
                            </td>
                            <td>
                                {{ $log->keterangan }}
                                @if($log->peminjaman && $log->peminjaman->buku)
                                    <div style="font-size: 0.8rem; color: var(--text-sub); margin-top: 3px;">
                                        Buku: {{ $log->peminjaman->buku->judul }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size: 0.85rem;">
                                {{ $log->creator->nama ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-sub); padding: 30px;">
                                Belum ada riwayat pengurangan atau peninjauan poin untuk anggota ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function handleAksiChange(val) {
            const groupJumlah = document.getElementById('groupJumlahPoin');
            const groupStatus = document.getElementById('groupStatusAkun');
            const labelJumlah = document.getElementById('labelJumlahPoin');

            if (val === 'tambah') {
                groupJumlah.style.display = 'block';
                groupStatus.style.display = 'none';
                labelJumlah.textContent = 'Jumlah Poin yang Ditambahkan';
            } else if (val === 'kurangi') {
                groupJumlah.style.display = 'block';
                groupStatus.style.display = 'none';
                labelJumlah.textContent = 'Jumlah Poin yang Dikurangi';
            } else if (val === 'set_status') {
                groupJumlah.style.display = 'none';
                groupStatus.style.display = 'block';
            } else {
                // pulihkan_reset
                groupJumlah.style.display = 'none';
                groupStatus.style.display = 'none';
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            handleAksiChange(document.getElementById('aksiSelect').value);
        });
    </script>
    </div> <!-- Close staff-main-content -->
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>
