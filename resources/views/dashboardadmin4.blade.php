<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peminjaman - {{ config('app.name', 'SeaBook') }} Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- 1. RESET & VARIABLES --- */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border: #e2e8f0;
            --sidebar-width: 260px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --radius: 12px;
            
            /* Status Colors */
            --status-wait-bg: #fef9c3; --status-wait-text: #854d0e;
            --status-ready-bg: #dbeafe; --status-ready-text: #1e40af;
            --status-danger-bg: #fee2e2; --status-danger-text: #991b1b;
            --status-success-bg: #dcfce7; --status-success-text: #166534;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); display: flex; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* --- 2. SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed; height: 100vh; top: 0; left: 0;
            display: flex; flex-direction: column;
            z-index: 100;
        }

        .sidebar-header {
            padding: 24px;
            display: flex; align-items: center; gap: 10px;
            border-bottom: 1px solid var(--border);
        }
        .logo-text { font-size: 1.5rem; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; }

        .nav-menu { padding: 20px 15px; flex: 1; }
        .nav-item { margin-bottom: 5px; }
        
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 8px;
            color: var(--text-sub); font-weight: 600; font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .nav-link:hover { background-color: var(--bg-body); color: var(--primary); }
        
        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        .nav-link i { width: 20px; text-align: center; font-size: 1.1rem; }

        .sidebar-footer {
            padding: 20px; border-top: 1px solid var(--border);
        }
        .user-profile {
            display: flex; align-items: center; gap: 10px;
        }
        .avatar {
            width: 40px; height: 40px; background: var(--primary); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        .user-info h4 { font-size: 0.9rem; margin-bottom: 2px; }
        .user-info p { font-size: 0.75rem; color: var(--text-sub); }

        /* --- 3. MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1; display: flex; flex-direction: column;
            width: calc(100% - var(--sidebar-width));
        }

        /* Top Header */
        .top-header {
            background: var(--surface);
            padding: 16px 30px;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 90;
        }

        .search-wrapper {
            position: relative; width: 400px;
        }
        .search-wrapper input {
            width: 100%; padding: 10px 15px 10px 40px;
            border-radius: 8px; border: 1px solid var(--border);
            background: var(--bg-body); outline: none; transition: 0.2s;
        }
        .search-wrapper input:focus { border-color: var(--primary); background: white; }
        .search-wrapper i {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--text-sub);
        }

        /* Content Body */
        .content-body { padding: 30px; }

        /* Page Header Flex */
        .page-header-flex {
            display: flex; justify-content: space-between; align-items: end; margin-bottom: 25px;
        }
        .section-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 5px; color: var(--text-main); }
        .section-subtitle { font-size: 0.9rem; color: var(--text-sub); }

        .header-actions { display: flex; gap: 10px; }

        .btn-action {
            padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem;
            cursor: pointer; display: flex; align-items: center; gap: 8px; border: none; text-decoration: none;
            transition: 0.2s;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        
        .btn-outline { background: white; border: 1px solid var(--border); color: var(--text-main); }
        .btn-outline:hover { border-color: var(--text-main); }

        /* --- 4. TABLE STYLES --- */
        .table-container {
            background: white; border-radius: 16px; border: 1px solid var(--border);
            overflow: hidden; box-shadow: var(--shadow-sm);
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            background: #f8fafc; text-align: left; padding: 16px;
            font-weight: 700; color: var(--text-sub); font-size: 0.8rem;
            text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px; border-bottom: 1px solid var(--border);
            color: var(--text-main); font-size: 0.9rem; vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fcfcfc; }

        /* Status Badge */
        .badge {
            padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block;
        }
        .badge-wait { background: var(--status-wait-bg); color: var(--status-wait-text); }
        .badge-ready { background: var(--status-ready-bg); color: var(--status-ready-text); }
        .badge-danger { background: var(--status-danger-bg); color: var(--status-danger-text); }
        .badge-success { background: var(--status-success-bg); color: var(--status-success-text); }

        /* Table Action Buttons */
        .btn-sm {
            padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; border: none; cursor: pointer;
        }
        .btn-accept { background: #dcfce7; color: #166534; }
        .btn-accept:hover { background: #bbf7d0; }
        
        .btn-reject { background: #fee2e2; color: #991b1b; }
        .btn-reject:hover { background: #fecaca; }
        
        .btn-poin { background: #1e293b; color: white; width: 100%; }

        .empty-state { text-align: center; padding: 40px; color: var(--text-sub); }

        /* --- 5. MODAL STYLES --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content {
            background: white; width: 450px; max-width: 90%;
            border-radius: 16px; padding: 30px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            position: relative; animation: slideUp 0.3s ease;
        }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .modal-header h3 { font-size: 1.25rem; font-weight: 700; }
        .close-btn { font-size: 1.5rem; cursor: pointer; color: var(--text-sub); background: none; border: none; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; outline: none; }
        .form-group input:focus { border-color: var(--primary); }

        .info-box {
            background: var(--bg-body); padding: 15px; border-radius: 8px; margin-bottom: 20px;
        }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; }
        .info-label { color: var(--text-sub); }
        .info-val { font-weight: 600; color: var(--text-main); }

        .btn-confirm { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }
        .btn-confirm:hover { background: var(--primary-dark); }

    </style>
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">
    <script>
        if (localStorage.getItem('sybook_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    @include('partials.motion')
</head>
<body class="staff-layout">

    @include('partials.staff-sidebar', ['active' => 'peminjaman'])

    <main class="main-content">
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Cari peminjam, buku, atau kode...">
                </div>
            </div>
            <div style="font-weight: 600; color: var(--text-sub);">
                {{ \Carbon\Carbon::now()->format('d M Y') }}
            </div>
        </header>

        <div class="content-body">
            @php $peminjaman = $peminjaman ?? collect(); @endphp
            
            <div class="page-header-flex">
                <div>
                    <h2 class="section-title">Daftar Peminjaman</h2>
                    <p class="section-subtitle">Pantau dan kelola status peminjaman buku.</p>
                </div>
                <div class="header-actions">
                    <button class="btn-action btn-primary" id="scanBtn" onclick="openScanModal()">
                        <i class="fa-solid fa-qrcode"></i> Scan Kode
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Peminjam</th>
                            <th>Cover</th>
                            <th>Buku</th>
                            <th>Durasi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjaman as $index => $p)
                        <tr>
                            <td style="text-align: center; color: var(--text-sub);">{{ $index + 1 }}</td>
                            <td style="font-weight: 600;">{{ $p->user->nama }}</td>
                            <td style="width: 60px;">
                                @if(optional($p->buku)->cover)
                                    <img src="{{ asset('storage/' . $p->buku->cover) }}" alt="{{ $p->buku->judul ?? 'Cover' }}" style="width:50px;height:70px;object-fit:cover;border-radius:4px;" />
                                @else
                                    <div style="width:50px;height:70px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:var(--text-sub);border-radius:4px;">-</div>
                                @endif
                            </td>
                            <td>{{ $p->buku->judul ?? 'Buku terhapus' }}</td>
                            <td>{{ $p->durasi }} Hari</td>
                            <td style="font-size: 0.85rem; color: var(--text-sub);">
                                {{ $p->tanggal_pinjam->format('d/m/y H:i') }} - {{ $p->tanggal_kembali->format('d/m/y H:i') }}
                            </td>
                            <td>
                                @if($p->statuspeminjaman == 'menunggu persetujuan')
                                    <span class="badge badge-wait">Menunggu Persetujuan</span>
                                @elseif($p->statuspeminjaman == 'disetujui')
                                    <span class="badge badge-ready">Disetujui</span>
                                @elseif($p->statuspeminjaman == 'sedang dipinjam')
                                    <span class="badge badge-ready">Dipinjam</span>
                                @elseif($p->statuspeminjaman == 'menunggu konfirmasi pengembalian')
                                    <span class="badge badge-wait">Konfirmasi Kembali</span>
                                @elseif($p->statuspeminjaman == 'dikembalikan')
                                    <span class="badge badge-success">Dikembalikan</span>
                                @elseif($p->statuspeminjaman == 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @elseif($p->statuspeminjaman == 'dibatalkan')
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;">Dibatalkan</span>
                                @elseif($p->statuspeminjaman == 'kadaluarsa')
                                    <span class="badge badge-danger">Kadaluarsa</span>
                                @else
                                    <span class="badge badge-success">{{ ucfirst($p->statuspeminjaman) }}</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 5px;">
                                    @if($p->statuspeminjaman == 'menunggu persetujuan')
                                        <form action="{{ route('peminjaman.confirm', $p->PeminjamanID) }}" method="POST"> @csrf
                                            <button type="submit" class="btn-sm btn-accept" title="Setujui"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                        <form action="{{ route('peminjaman.reject', $p->PeminjamanID) }}" method="POST" onsubmit="const reason = prompt('Masukkan alasan penolakan:'); if(!reason) return false; this.alasan_penolakan.value = reason;">
                                            @csrf
                                            <input type="hidden" name="alasan_penolakan" value="">
                                            <button type="submit" class="btn-sm btn-reject" title="Tolak"><i class="fa-solid fa-xmark"></i></button>
                                        </form>
                                    @elseif($p->statuspeminjaman == 'menunggu konfirmasi pengembalian' || $p->statuspeminjaman == 'sedang dipinjam')
                                        @php
                                            $isLate = now()->gt($p->tanggal_kembali);
                                            $diffSec = $isLate ? now()->diffInSeconds($p->tanggal_kembali) : 0;
                                            $hariTelat = (int) ceil($diffSec / 86400);
                                            $poinTelatEst = $hariTelat * (int) config('perpustakaan.poin_terlambat_per_hari', 2);
                                            $telatText = $isLate ? "Terlambat {$hariTelat} hari (+{$poinTelatEst} poin pelanggaran)" : "Tepat Waktu";
                                        @endphp
                                        <button type="button" class="btn-sm btn-accept" onclick="openReturnModal({{ $p->PeminjamanID }}, '{{ addslashes($p->user->nama) }}', '{{ addslashes($p->buku->judul ?? 'Buku terhapus') }}', '{{ $p->tanggal_kembali->format('d/m/Y H:i') }}', {{ $isLate ? 'true' : 'false' }}, '{{ $telatText }}', {{ $p->statuspeminjaman === 'menunggu konfirmasi pengembalian' ? 'true' : 'false' }})">
                                            <i class="fa-solid fa-box-archive"></i> Verifikasi Kembali
                                        </button>
                                    @elseif($p->statuspeminjaman == 'disetujui')
                                        <form action="{{ route('peminjaman.cancel', $p->PeminjamanID) }}" method="POST" onsubmit="return confirm('Batalkan peminjaman ini?');"> @csrf
                                            <button type="submit" class="btn-sm btn-reject">Batal</button>
                                        </form>
                                    @else
                                        <span style="color: #cbd5e1;">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 10px;"></i>
                                <p>Belum ada data peminjaman.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="scanModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Scan / Input Kode</h3>
                <button class="close-btn" onclick="closeScanModal()">&times;</button>
            </div>

            <form action="{{ route('peminjaman.scan') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Kode Peminjaman</label>
                    <div style="position: relative;">
                        <input type="text" id="kode_peminjaman" name="kode_peminjaman" placeholder="Ketik kode atau scan..." required oninput="fetchPeminjamanData()" autocomplete="off" autofocus>
                        <i class="fa-solid fa-qrcode" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-sub);"></i>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Peminjam</span>
                        <span class="info-val" id="modalNama">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Buku</span>
                        <span class="info-val" id="modalBuku">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Durasi</span>
                        <span class="info-val" id="modalDurasi">-</span>
                    </div>
                </div>

                <button type="submit" class="btn-confirm">Konfirmasi Data</button>
            </form>
        </div>
    </div>

    <!-- Modal Verifikasi Pengembalian -->
    <div id="returnModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Verifikasi Pengembalian Buku</h3>
                <button class="close-btn" onclick="closeReturnModal()">&times;</button>
            </div>

            <form id="returnForm" action="" method="POST">
                @csrf
                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Peminjam</span>
                        <span class="info-val" id="returnNama">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Buku</span>
                        <span class="info-val" id="returnBuku">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tenggat Waktu</span>
                        <span class="info-val" id="returnTenggat">-</span>
                    </div>
                    <div class="info-row" id="rowTerlambat" style="display: none;">
                        <span class="info-label" style="color: #dc2626;">Keterlambatan</span>
                        <span class="info-val" id="returnTerlambat" style="color: #dc2626; font-weight: bold;">-</span>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Kondisi Buku Fisik</label>
                    <select name="kondisi_buku" id="kondisi_buku" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
                        <option value="baik">Baik (Sempurna)</option>
                        <option value="rusak ringan">Rusak Ringan (Halaman terlipat/coret sedikit)</option>
                        <option value="rusak berat">Rusak Berat (Robek/halaman hilang/tidak layak)</option>
                        <option value="hilang">Hilang (Buku tidak dapat ditemukan)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Catatan Petugas (Opsional)</label>
                    <textarea name="catatan_petugas" rows="2" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);" placeholder="Catatan kondisi buku atau pelanggaran..."></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-confirm" style="flex: 1; background: #16a34a;">Terima Pengembalian</button>
                    <button type="submit" name="belum_diterima" value="1" id="btnBelumDiterima" class="btn-confirm" style="flex: 1; background: #eab308; display: none;">Belum Diterima</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Functions
        function openScanModal() {
            const modal = document.getElementById('scanModal');
            modal.style.display = 'flex';
            setTimeout(() => document.getElementById('kode_peminjaman').focus(), 100);
        }

        function closeScanModal() {
            const modal = document.getElementById('scanModal');
            modal.style.display = 'none';
            // Reset Data
            document.getElementById('kode_peminjaman').value = '';
            document.getElementById('modalNama').textContent = '-';
            document.getElementById('modalBuku').textContent = '-';
            document.getElementById('modalDurasi').textContent = '-';
        }

        function openReturnModal(id, nama, buku, tenggat, isLate, terlambatText, isFromUserRequest) {
            const modal = document.getElementById('returnModal');
            document.getElementById('returnForm').action = "{{ url('/peminjaman/confirm-return') }}/" + id;
            document.getElementById('returnNama').textContent = nama;
            document.getElementById('returnBuku').textContent = buku;
            document.getElementById('returnTenggat').textContent = tenggat;
            
            const rowTerlambat = document.getElementById('rowTerlambat');
            if (isLate) {
                rowTerlambat.style.display = 'flex';
                document.getElementById('returnTerlambat').textContent = terlambatText;
            } else {
                rowTerlambat.style.display = 'none';
            }

            const btnBelum = document.getElementById('btnBelumDiterima');
            if (isFromUserRequest) {
                btnBelum.style.display = 'block';
            } else {
                btnBelum.style.display = 'none';
            }

            modal.style.display = 'flex';
        }

        function closeReturnModal() {
            document.getElementById('returnModal').style.display = 'none';
        }

        // Close on outside click
        window.onclick = function(event) {
            const scanModal = document.getElementById('scanModal');
            const returnModal = document.getElementById('returnModal');
            if (event.target == scanModal) {
                closeScanModal();
            }
            if (event.target == returnModal) {
                closeReturnModal();
            }
        }

        // Live Fetch Data Logic
        function fetchPeminjamanData() {
            const kode = document.getElementById('kode_peminjaman').value;
            
            // Minimal 3 karakter untuk trigger fetch agar tidak spam request
            if (kode.length >= 3) {
                fetch('{{ route("peminjaman.getByCode") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ kode_peminjaman: kode })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        document.getElementById('modalNama').textContent = data.nama;
                        document.getElementById('modalBuku').textContent = data.buku;
                        document.getElementById('modalDurasi').textContent = data.durasi + ' Hari';
                    } else {
                        // Reset jika tidak ketemu (opsional: bisa tambah alert kecil)
                        document.getElementById('modalNama').textContent = '-';
                        document.getElementById('modalBuku').textContent = '-';
                        document.getElementById('modalDurasi').textContent = '-';
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    </script>
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>