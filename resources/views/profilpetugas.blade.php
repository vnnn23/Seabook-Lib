<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SeaBook') }} - Profil Petugas</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- 1. RESET & VARIABLES --- */
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --bg-body: #f1f5f9;
            --surface: #ffffff;
            --text-main: #1e293b;
            --text-sub: #64748b;
            --border: #e2e8f0;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --sidebar-width: 260px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05);
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); display: flex; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* --- 2. SIDEBAR (TIDAK DIUBAH) --- */
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
        .avatar-mini {
            width: 40px; height: 40px; background: var(--primary); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; flex-shrink: 0;
        }
        .user-info h4 { font-size: 0.9rem; margin-bottom: 2px; }
        .user-info p { font-size: 0.75rem; color: var(--text-sub); }


        /* --- 3. MAIN CONTENT --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            width: calc(100% - var(--sidebar-width));
        }

        .main-content {
            flex: 1;
            padding: 30px;
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        /* Profile Header Card */
        .profile-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            position: relative;
        }

        .profile-cover {
            height: 140px;
            background: linear-gradient(120deg, var(--primary), #60a5fa);
            position: relative;
        }

        .profile-header-content {
            padding: 0 30px 24px 30px;
            position: relative;
            display: flex;
            justify-content: space-between; /* Memisahkan info profil dan tombol logout */
            align-items: flex-end; /* Align bottom */
        }

        .profile-left-section {
            position: relative;
        }

        .profile-avatar-container {
            position: relative;
            margin-top: -60px;
            margin-bottom: 16px;
            width: 120px;
            height: 120px;
        }

        .big-avatar {
            width: 100%; height: 100%;
            border-radius: 50%;
            border: 4px solid var(--surface);
            background: var(--primary-light);
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: 800;
            object-fit: cover;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .camera-btn {
            position: absolute;
            bottom: 5px; right: 5px;
            background: var(--text-main);
            color: white;
            border: 2px solid var(--surface);
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .camera-btn:hover { transform: scale(1.1); background: var(--primary); }

        .profile-name-section h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }
        .profile-name-section p { color: var(--text-sub); font-size: 0.95rem; margin-top: 4px; }

        /* Tombol Logout Baru */
        .btn-logout-trigger {
            background: transparent;
            color: var(--danger);
            border: 1px solid var(--danger);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            margin-bottom: 5px; /* Alignment fix */
        }
        
        .btn-logout-trigger:hover {
            background: var(--danger);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
        }

        /* Tabs */
        .custom-tabs {
            display: flex;
            gap: 24px;
            border-bottom: 1px solid var(--border);
            padding: 0 30px;
            margin-top: 10px;
        }

        .tab-btn {
            background: none; border: none;
            padding: 16px 4px;
            font-size: 0.95rem; font-weight: 600;
            color: var(--text-sub);
            cursor: pointer;
            position: relative;
            transition: color 0.2s;
        }

        .tab-btn:hover { color: var(--primary); }
        
        .tab-btn.active { color: var(--primary); }
        .tab-btn.active::after {
            content: ''; position: absolute; bottom: -1px; left: 0; width: 100%;
            height: 3px; background: var(--primary);
            border-radius: 3px 3px 0 0;
        }

        /* Content Area */
        .content-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: 30px;
            border: 1px solid var(--border);
            min-height: 400px;
        }

        .section-title {
            font-size: 1.1rem; font-weight: 700; margin-bottom: 24px;
            color: var(--text-main); display: flex; align-items: center; gap: 10px;
        }
        .section-title::before {
            content: ''; width: 4px; height: 18px; background: var(--primary); border-radius: 2px;
        }

        /* Data Grid */
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        .data-item { display: flex; flex-direction: column; gap: 6px; }
        .data-label { font-size: 0.85rem; font-weight: 600; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.5px; }
        .data-value { font-size: 1rem; color: var(--text-main); font-weight: 500; padding: 12px; background: var(--bg-body); border-radius: 8px; border: 1px solid transparent; }

        /* Forms */
        .edit-form { max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main); }
        
        .form-control {
            width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px;
            font-size: 0.95rem; color: var(--text-main); background: var(--surface);
            transition: all 0.2s; outline: none;
        }
        
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        textarea.form-control { resize: vertical; min-height: 100px; }

        .btn-primary {
            display: inline-flex; justify-content: center; align-items: center;
            padding: 12px 24px; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-weight: 600; cursor: pointer;
            transition: background 0.2s; font-size: 0.95rem;
        }
        .btn-primary:hover { background: var(--primary-hover); }

        /* Modals */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);
            display: none; justify-content: center; align-items: center; z-index: 1000;
            opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-overlay.show { display: flex; opacity: 1; }
        
        .modal-box {
            background: white; width: 450px; max-width: 90%;
            border-radius: 16px; padding: 0;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            transform: scale(0.95); transition: transform 0.3s ease;
            overflow: hidden;
        }
        .modal-overlay.show .modal-box { transform: scale(1); }

        .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 1.1rem; font-weight: 700; }
        .close-modal { cursor: pointer; color: var(--text-sub); font-size: 1.2rem; transition: color 0.2s; }
        .close-modal:hover { color: var(--text-main); }
        
        .modal-body { padding: 24px; }
        
        .modal-actions { display: flex; gap: 12px; margin-top: 20px; }
        .btn-secondary { background: #e2e8f0; color: var(--text-main); border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; }
        .btn-secondary:hover { background: #cbd5e1; }
        .w-100 { width: 100%; }

        /* Animation for tabs */
        .tab-content { display: none; animation: fadeIn 0.4s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

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

    @include('partials.staff-sidebar', ['active' => ''])

    <div class="main-wrapper">
        <header class="top-header" style="background: var(--surface); padding: 16px 30px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800;">Profil Petugas Perpustakaan</h3>
            </div>
            <div style="font-weight: 600; color: var(--text-sub);">{{ \Carbon\Carbon::now()->format('d M Y') }}</div>
        </header>
        <main class="main-content">
            
            <div class="profile-card">
                <div class="profile-cover"></div>
                
                <div class="profile-header-content">
                    <div class="profile-left-section">
                        <div class="profile-avatar-container">
                            @if(Auth::user()->foto_profil)
                                <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" alt="Foto Profil" class="big-avatar">
                            @else
                                <div class="big-avatar">
                                    {{ strtoupper(substr(Auth::user()->nama ?? 'A', 0, 2)) }}
                                </div>
                            @endif
                            
                            <button class="camera-btn" id="btnUploadFoto" title="Ganti Foto">
                                <i class="fa-solid fa-camera" style="font-size: 12px;"></i>
                            </button>
                        </div>

                        <div class="profile-name-section">
                            <h2>{{ Auth::user()->nama ?? 'Pengguna' }}</h2>
                            <p><i class="fa-solid fa-id-badge" style="margin-right: 6px;"></i> {{ Auth::user()->role ?? 'Petugas Perpustakaan' }}</p>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button id="btnLogoutTrigger" class="btn-logout-trigger">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                        </button>
                    </div>
                </div>

                <div class="custom-tabs">
                    <button class="tab-btn active" onclick="openTab(event, 'data-identitas')">
                        <i class="fa-regular fa-user" style="margin-right: 6px;"></i> Data Identitas
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'ubah-profil')">
                        <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Edit Profil
                    </button>
                </div>
            </div>

            <div class="content-card">
                
                <div id="data-identitas" class="tab-content active">
                    <h3 class="section-title">Informasi Pribadi</h3>
                    <div class="data-grid">
                        <div class="data-item">
                            <span class="data-label">Nama Lengkap</span>
                            <div class="data-value">{{ Auth::user()->nama ?? '-' }}</div>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Jenis Kelamin</span>
                            <div class="data-value">{{ Auth::user()->jenis_kelamin ?? '-' }}</div>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Nomor Ponsel</span>
                            <div class="data-value">{{ Auth::user()->nomor_telepon ?? '-' }}</div>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Kecamatan</span>
                            <div class="data-value">{{ Auth::user()->kecamatan ?? '-' }}</div>
                        </div>
                        <div class="data-item" style="grid-column: 1 / -1;">
                            <span class="data-label">Alamat Lengkap</span>
                            <div class="data-value">{{ Auth::user()->alamat ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div id="ubah-profil" class="tab-content">
                    <h3 class="section-title">Edit Data Pengguna</h3>
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="edit-form">
                        @csrf
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="{{ Auth::user()->nama }}" required>
                        </div>
                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ Auth::user()->jenis_kelamin == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ Auth::user()->jenis_kelamin == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nomor Ponsel</label>
                            <input type="text" name="nomor_telepon" class="form-control" value="{{ Auth::user()->nomor_telepon }}">
                        </div>
                        <div class="form-group">
                            <label>Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control" value="{{ Auth::user()->kecamatan }}">
                        </div>
                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control">{{ Auth::user()->alamat }}</textarea>
                        </div>
                        <div style="margin-top: 30px;">
                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-save" style="margin-right: 8px;"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <div id="modalUploadFoto" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Ubah Foto Profil</h3>
                <span class="close-modal" id="closeUploadFoto">&times;</span>
            </div>
            <div class="modal-body">
                <form action="{{ route('profile.uploadFoto') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Pilih Foto Baru</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" required>
                        <small style="color: var(--text-sub); display: block; margin-top: 5px;">Format: JPG, PNG, JPEG. Max 2MB.</small>
                    </div>
                    <button type="submit" class="btn-primary w-100">Upload Foto</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- Tab Logic ---
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            setTimeout(() => { document.getElementById(tabName).classList.add("active"); }, 10);
            evt.currentTarget.className += " active";
        }

        // --- Helper Modal Function ---
        function setupModal(triggerId, modalId, closeId) {
            const trigger = document.getElementById(triggerId);
            const modal = document.getElementById(modalId);
            const close = document.getElementById(closeId);

            if(trigger) {
                trigger.onclick = (e) => {
                    e.preventDefault();
                    modal.classList.add('show');
                }
            }
            
            if(close) {
                close.onclick = () => { modal.classList.remove('show'); }
            }
            
            // Close on outside click
            window.addEventListener('click', (e) => {
                if (e.target == modal) {
                    modal.classList.remove('show');
                }
            });
        }

        // --- Init Modals ---
        setupModal('btnUploadFoto', 'modalUploadFoto', 'closeUploadFoto');
        
        // Setup Logout Modal
        const btnLogoutTrigger = document.getElementById('btnLogoutTrigger');
        if (btnLogoutTrigger) {
            btnLogoutTrigger.onclick = function() {
                if (window.SyBookLogout) {
                    window.SyBookLogout.open();
                }
            };
        }

    </script>
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>