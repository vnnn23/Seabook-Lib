<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - {{ config('app.name', 'SeaBook') }} {{ Auth::user()->role === 'admin' ? 'Admin' : 'Petugas' }}</title>
    
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
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --radius: 12px;
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

        .sidebar-footer { padding: 20px; border-top: 1px solid var(--border); }
        .user-profile { display: flex; align-items: center; gap: 10px; }
        .avatar {
            width: 40px; height: 40px; background: var(--primary); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;
        }
        .user-info h4 { font-size: 0.9rem; margin-bottom: 2px; }
        .user-info p { font-size: 0.75rem; color: var(--text-sub); }

        /* --- 3. MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1; display: flex; flex-direction: column;
            width: calc(100% - var(--sidebar-width));
            padding: 30px 40px;
        }

        /* Top Header Area */
        .page-header {
            display: flex; align-items: center; gap: 15px; margin-bottom: 30px;
        }
        .btn-back {
            background: white; border: 1px solid var(--border); padding: 8px 12px;
            border-radius: 8px; color: var(--text-sub); transition: 0.2s;
        }
        .btn-back:hover { border-color: var(--text-main); color: var(--text-main); }
        
        .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-main); }

        /* --- 4. EDIT FORM CARD --- */
        .edit-card {
            background: white; border-radius: 16px; border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 30px;
            max-width: 900px; margin: 0 auto; width: 100%;
        }

        .form-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
        }
        .col-full { grid-column: span 2; }

        .form-group { margin-bottom: 5px; }
        .form-group label {
            display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid var(--border);
            border-radius: 8px; font-family: inherit; outline: none; transition: 0.2s; font-size: 0.95rem;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }

        /* File Upload Styling */
        .file-upload-box {
            border: 2px dashed var(--border); padding: 20px; text-align: center;
            border-radius: 10px; cursor: pointer; transition: 0.2s; position: relative;
            background: var(--bg-body);
        }
        .file-upload-box:hover { border-color: var(--primary); background: var(--primary-light); }
        .file-upload-box input {
            position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer;
        }
        .upload-content { color: var(--text-sub); font-size: 0.9rem; }
        .upload-content i { font-size: 1.5rem; display: block; margin-bottom: 5px; color: var(--primary); }

        .current-cover {
            margin-top: 15px; display: flex; align-items: center; gap: 15px;
            background: var(--bg-body); padding: 10px; border-radius: 8px; border: 1px solid var(--border);
        }
        .cover-preview-img { height: 80px; border-radius: 6px; object-fit: cover; }

        /* Action Buttons */
        .form-actions {
            margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);
            display: flex; justify-content: flex-end; gap: 12px;
        }
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: white; border: 1px solid var(--border); color: var(--text-sub); }
        .btn-secondary:hover { background: var(--bg-body); color: var(--text-main); }

        /* Danger Zone */
        .danger-zone {
            margin-top: 40px; background: var(--danger-bg); border: 1px solid #fecaca;
            border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center;
        }
        .danger-text h4 { color: #991b1b; margin-bottom: 4px; font-size: 1rem; }
        .danger-text p { color: #b91c1c; font-size: 0.85rem; }
        .btn-danger { background: #ef4444; color: white; padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; }
        .btn-danger:hover { background: #dc2626; }

    </style>
    @include('partials.motion')
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-book-open-reader" style="font-size: 1.8rem; color: var(--primary);"></i>
            <span class="logo-text">SeaBook</span>
        </div>
        
        <nav class="nav-menu">
            <ul>
                @if(Auth::user()->role === 'admin')
                    <li class="nav-item">
                        <a href="{{ route('dashboardadmin') }}" class="nav-link active">
                            <i class="fa-solid fa-book"></i> Daftar Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dashboardadmin2') }}" class="nav-link">
                            <i class="fa-solid fa-users"></i> Pengguna
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dashboardadmin3') }}" class="nav-link">
                            <i class="fa-solid fa-user-shield"></i> Petugas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dashboardadmin4') }}" class="nav-link">
                            <i class="fa-solid fa-clipboard-list"></i> Peminjaman
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('dashboardpetugas') }}" class="nav-link active">
                            <i class="fa-solid fa-book"></i> Daftar Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dashboardpetugas2') }}" class="nav-link">
                            <i class="fa-solid fa-clipboard-list"></i> Peminjaman
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('peminjaman.exportPdf') }}" class="nav-link">
                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="{{ Auth::user()->role === 'admin' ? route('profile') : route('profile.petugas') }}" class="user-profile">
                <div class="avatar">
                    @if(Auth::user()->foto_profil)
                        <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" alt="Avatar" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                    @else
                        {{ substr(Auth::user()->nama ?? 'A', 0, 1) }}
                    @endif
                </div>
                <div class="user-info">
                    <h4>{{ Auth::user()->nama ?? 'User' }}</h4>
                    <p>{{ Auth::user()->role === 'admin' ? 'Administrator' : 'Petugas' }}</p>
                </div>
            </a>
        </div>
    </aside>

    <main class="main-content">
        
        <div class="page-header">
            <a href="{{ Auth::user()->role === 'admin' ? route('dashboardadmin') : route('dashboardpetugas') }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i></a>
            <h1 class="page-title">Edit Buku</h1>
        </div>

        <div class="edit-card">
            <form action="{{ route('books.update', $buku->BukuID) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="form-grid">
                    <div class="form-group col-full">
                        <label>Judul Buku</label>
                        <input type="text" name="judul" value="{{ old('judul', $buku->judul) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Penulis</label>
                        <input type="text" name="penulis" value="{{ old('penulis', $buku->penulis) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="penerbit" value="{{ old('penerbit', $buku->penerbit) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit) }}" required>
                    </div>
                    <div class="form-group">
                        <label>ISBN</label>
                        <input type="text" name="isbn" value="{{ old('isbn', $buku->isbn) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategoris" required>
                            <option value="">Pilih Kategori...</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->KategoriID }}" 
                                    {{ ($buku->kategoris->contains('KategoriID', $kategori->KategoriID)) ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Total Eksemplar</label>
                        <input type="number" name="jumlah_total" value="{{ old('jumlah_total', $buku->jumlah_total) }}" min="{{ $buku->dipinjam }}" required>
                        <small style="color: #64748b; display: block; margin-top: 4px;">
                            Sedang dipinjam: <strong>{{ $buku->dipinjam }}</strong> eksemplar. Tersedia: <strong>{{ $buku->tersedia }}</strong> eksemplar. (Minimal: {{ $buku->dipinjam }})
                        </small>
                    </div>

                    <div class="form-group col-full">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" rows="5">{{ old('deskripsi', $buku->deskripsi) }}</textarea>
                    </div>

                    <div class="form-group col-full">
                        <label>Cover Buku</label>
                        
                        <div class="file-upload-box">
                            <div class="upload-content">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Klik atau seret gambar ke sini untuk mengganti cover</span>
                            </div>
                            <input type="file" name="cover" id="coverInput" accept="image/*">
                        </div>

                        <div class="current-cover" id="previewArea" style="{{ $buku->cover ? '' : 'display:none;' }}">
                            <div style="flex:1;">
                                <span style="font-size:0.85rem; font-weight:600; display:block; margin-bottom:5px;">Preview Cover:</span>
                                <img src="{{ $buku->cover ? asset('storage/' . $buku->cover) : '' }}" id="coverPreview" class="cover-preview-img">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ Auth::user()->role === 'admin' ? route('dashboardadmin') : route('dashboardpetugas') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        <div class="edit-card danger-zone">
            <div class="danger-text">
                <h4>Hapus Buku</h4>
                <p>Tindakan ini tidak dapat dibatalkan. Buku akan dihapus permanen dari database.</p>
            </div>
            <form action="{{ route('books.destroy', $buku->BukuID) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini secara permanen?')">
                    <i class="fa-solid fa-trash-can"></i> Hapus Buku
                </button>
            </form>
        </div>

    </main>

    <script>
        // Simple Image Preview Script
        const coverInput = document.getElementById('coverInput');
        const previewArea = document.getElementById('previewArea');
        const coverPreview = document.getElementById('coverPreview');

        coverInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverPreview.src = e.target.result;
                    previewArea.style.display = 'flex';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

</body>
</html>