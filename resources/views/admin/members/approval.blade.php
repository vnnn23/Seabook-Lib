<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Member - {{ config('app.name', 'SeaBook') }}</title>
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
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .page-title { font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.2px; }
        .page-subtitle { font-size: 0.88rem; color: var(--text-sub); margin: 0; line-height: 1.5; }
        .table-card { background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .card-header { padding: 14px 20px; border-bottom: 1px solid var(--border); background: #f8fafc; font-weight: 700; font-size: 0.95rem; color: var(--text-main); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 18px; border-bottom: 1px solid var(--border); }
        th { background: #f8fafc; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-sub); }
        td { font-size: 0.88rem; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; display: inline-block; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-aktif { background: #dcfce7; color: #166534; }
        .badge-ditolak { background: #fee2e2; color: #991b1b; }
        .btn { padding: 7px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; border: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 6px; }
        .btn-success { background: #16a34a; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-outline { background: white; border: 1px solid var(--border); color: var(--text-main); }
        .alert { padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-success { background: #e8f5e9; color: #1e7e34; border: 1px solid #c3e6cb; }
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 24px; border-radius: 12px; width: 90%; max-width: 480px; }
    </style>
    @include('partials.motion')
</head>
<body class="staff-layout">

    <!-- Collapsible Staff Sidebar -->
    @include('partials.staff-sidebar', ['active' => 'approval'])

    <!-- Main Content Area -->
    <div class="staff-main-content">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <h2 class="staff-page-title">Persetujuan Akun Member</h2>
            </div>
            <div class="staff-topbar-right">
                <span class="role-pill role-admin">
                    <i class="fa-solid fa-user-shield"></i> Administrator
                </span>
            </div>
        </header>

        <div class="container-admin">
        <div class="header-section">
            <div>
                <h1 class="page-title">Persetujuan Akun Member</h1>
                <p class="page-subtitle">Kelola pendaftaran anggota baru yang memerlukan verifikasi admin.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        <!-- TABEL ANGGOTA MENUNGGU PERSETUJUAN -->
        <div class="table-card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Menunggu Persetujuan ({{ $pendingUsers->count() }})</span>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Member</th>
                            <th>Email</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingUsers as $u)
                            <tr>
                                <td><strong>{{ $u->nama }}</strong></td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->created_at ? $u->created_at->format('d M Y H:i') : '-' }}</td>
                                <td><span class="badge badge-pending">Pending</span></td>
                                <td style="text-align: right;">
                                    <form action="{{ route('admin.members.approve', $u->UserID) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Setujui pendaftaran member {{ $u->nama }}?')">
                                            <i class="fa-solid fa-check"></i> Setujui
                                        </button>
                                    </form>
                                    <button class="btn btn-danger" onclick="openRejectModal('{{ $u->UserID }}', '{{ addslashes($u->nama) }}')">
                                        <i class="fa-solid fa-xmark"></i> Tolak
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-sub); padding: 30px;">
                                    Tidak ada pendaftaran member yang menunggu persetujuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL RIWAYAT KEPUTUSAN -->
        <div class="table-card">
            <div class="card-header">
                Riwayat Keputusan Approval
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Member</th>
                            <th>Email</th>
                            <th>Status Akun</th>
                            <th>Diputuskan Oleh</th>
                            <th>Waktu Keputusan</th>
                            <th>Keterangan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historyUsers as $h)
                            <tr>
                                <td><strong>{{ $h->nama }}</strong></td>
                                <td>{{ $h->email }}</td>
                                <td>
                                    @if($h->status_akun === 'aktif')
                                        <span class="badge badge-aktif">Aktif (Disetujui)</span>
                                    @elseif($h->status_akun === 'ditolak')
                                        <span class="badge badge-ditolak">Ditolak</span>
                                    @else
                                        <span class="badge badge-pending">{{ $h->status_akun }}</span>
                                    @endif
                                </td>
                                <td>{{ $h->disetujuiOleh->nama ?? 'Admin' }}</td>
                                <td>{{ $h->disetujui_pada ? $h->disetujui_pada->format('d M Y H:i') : '-' }}</td>
                                <td>{{ $h->alasan_penolakan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-sub); padding: 30px;">
                                    Belum ada riwayat keputusan approval.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($historyUsers->hasPages())
                <div style="padding: 15px 20px;">
                    {{ $historyUsers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL PENOLAKAN -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top: 0;">Tolak Pendaftaran Member</h3>
            <p id="rejectModalText" style="color: var(--text-sub);"></p>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Alasan Penolakan:</label>
                    <textarea name="alasan" required rows="4" placeholder="Tuliskan alasan penolakan pendaftaran..." style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); box-sizing: border-box;"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Akun</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(userId, userName) {
            document.getElementById('rejectModalText').textContent = 'Anda akan menolak pendaftaran member ' + userName + '. Masukkan alasan penolakan:';
            document.getElementById('rejectForm').action = '/admin/members/' + userId + '/reject';
            document.getElementById('rejectModal').style.display = 'flex';
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }
    </script>
    </div> <!-- Close staff-main-content -->
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>

