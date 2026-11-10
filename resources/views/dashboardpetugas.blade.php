<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - {{ config('app.name', 'SeaBook') }}</title>
    
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
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
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

        /* Alerts */
        .alert {
            padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Action Toolbar */
        .toolbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px;
        }

        .filter-group { position: relative; }
        .btn-filter {
            background: white; border: 1px solid var(--border);
            padding: 10px 20px; border-radius: 8px; font-weight: 600; color: var(--text-sub);
            cursor: pointer; display: flex; align-items: center; gap: 8px;
            transition: 0.2s;
        }
        .btn-filter:hover { border-color: var(--primary); color: var(--primary); }

        .btn-group { display: flex; gap: 10px; }
        .btn-primary {
            background: var(--primary); color: white; border: none;
            padding: 10px 20px; border-radius: 8px; font-weight: 600;
            cursor: pointer; display: flex; align-items: center; gap: 8px;
            transition: 0.2s;
        }
        .btn-primary:hover { background: var(--primary-dark); }

        /* Filter Modal Dropdown */
        .filter-dropdown {
            position: absolute; top: 110%; left: 0;
            width: 280px; background: white; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid var(--border);
            padding: 15px; display: none; z-index: 100;
        }
        .filter-dropdown.show { display: block; animation: fadeIn 0.2s; }
        
        .search-mini {
            width: 100%; padding: 8px 30px 8px 10px; border: 1px solid var(--border);
            border-radius: 6px; margin-bottom: 10px; font-size: 0.9rem;
        }
        .checkbox-list { max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
        .checkbox-item {
            display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem; color: var(--text-main);
        }
        .checkbox-item input { accent-color: var(--primary); width: 16px; height: 16px; }

        /* Grid Buku */
        .section-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; color: var(--text-main); }

        .book-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;
        }

        .book-card {
            background: white; border-radius: 16px; padding: 12px;
            border: 1px solid var(--border); transition: all 0.3s ease;
            position: relative; display: flex; flex-direction: column;
        }
        .book-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: #bfdbfe; }
        
        .card-img {
            width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 10px;
            margin-bottom: 12px; background: #f1f5f9;
        }
        .card-title {
            font-size: 1rem; font-weight: 700; color: var(--text-main);
            margin-bottom: 5px; line-height: 1.4;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .card-meta { font-size: 0.85rem; color: var(--text-sub); margin-bottom: 15px; }

        /* --- MODAL STYLES --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content {
            background: white; width: 500px; max-width: 90%;
            border-radius: 16px; padding: 30px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            position: relative; max-height: 90vh; overflow-y: auto;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .modal-header h3 { font-size: 1.25rem; font-weight: 700; }
        .close-btn { font-size: 1.5rem; cursor: pointer; color: var(--text-sub); background: none; border: none; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; outline: none; font-family: inherit;
        }
        .form-group input:focus { border-color: var(--primary); }
        
        .file-upload-wrapper {
            border: 2px dashed var(--border); padding: 20px; text-align: center;
            border-radius: 8px; cursor: pointer; color: var(--text-sub); position: relative;
        }
        .file-upload-wrapper:hover { border-color: var(--primary); color: var(--primary); }
        .file-upload-wrapper input {
            position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer;
        }

        .btn-submit {
            width: 100%; padding: 12px; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 10px;
        }
        .btn-submit:hover { background: var(--primary-dark); }

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

    @include('partials.staff-sidebar', ['active' => 'buku'])

    <main class="main-content">
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 14px;">
                <form method="GET" action="{{ route('dashboardpetugas') }}" class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Cari judul buku, penulis, atau ISBN..." value="{{ request('search') }}">
                </form>
            </div>
            <div style="font-weight: 600; color: var(--text-sub);">
                {{ \Carbon\Carbon::now()->format('d M Y') }}
            </div>
        </header>

        <div class="content-body">
            @if(session('success'))
                <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <ul style="margin-left: 20px;">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="toolbar">
                <div class="filter-group">
                    <button class="btn-filter" id="categoryBtn">
                        <i class="fa-solid fa-filter"></i> Kategori <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
                    </button>
                    
                    <div class="filter-dropdown" id="filterModal">
                        <input type="text" class="search-mini kategori-search" placeholder="Cari kategori...">
                        <div class="checkbox-list modal-list-scroll">
                            <label class="checkbox-item">
                                <input type="checkbox" value="" class="kategori-checkbox"> Semua
                            </label>
                            @foreach($kategoris ?? [] as $kategori)
                            <label class="checkbox-item">
                                <input type="checkbox" value="{{ $kategori->KategoriID }}" class="kategori-checkbox"> 
                                {{ $kategori->nama_kategori }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="btn-group">
                    <button id="btnTambahBuku" class="btn-primary"><i class="fa-solid fa-plus"></i> Tambah Buku</button>
                    <button id="btnTambahKategori" class="btn-primary" style="background: white; color: var(--primary); border: 1px solid var(--border);"><i class="fa-solid fa-tags"></i> Kategori</button>
                </div>
            </div>

            <h2 class="section-title">Daftar Buku ({{ $buku->count() ?? 0 }})</h2>
            
            <div class="book-grid">
                @foreach($buku ?? [] as $book)
                <div class="book-card">
                    <img src="{{ $book->cover ? asset('storage/' . $book->cover) : 'https://placehold.co/200x300/e2e8f0/64748b?text=' . urlencode($book->judul) }}" alt="{{ $book->judul }}" class="card-img">
                    <h3 class="card-title">{{ $book->judul }}</h3>
                    <p class="card-meta">{{ $book->penulis }} • {{ $book->tahun_terbit }}</p>
                    
                    <div style="font-size: 0.78rem; color: var(--text-sub); margin: 6px 0 10px; display: flex; gap: 6px; flex-wrap: wrap;">
                        <span>Total: <strong>{{ $book->total }}</strong></span> |
                        <span style="color: {{ $book->tersedia > 0 ? '#16a34a' : '#dc2626' }};">Tersedia: <strong>{{ $book->tersedia }}</strong></span> |
                        <span>Dipinjam: <strong>{{ $book->dipinjam }}</strong></span>
                    </div>

                    <div style="margin-top: auto; display: flex; gap: 8px;">
                        <a href="{{ route('books.edit', $book->BukuID) }}" style="flex: 1; padding: 6px; border: 1px solid var(--border); border-radius: 6px; background: white; cursor: pointer; color: var(--text-sub); font-size: 0.8rem; display:flex;align-items:center;justify-content:center;gap:6px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                        <form action="{{ route('books.destroy', $book->BukuID) }}" method="POST" onsubmit="return confirm('Hapus buku ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="padding: 6px 10px; border: 1px solid #fee2e2; border-radius: 6px; background: #fef2f2; cursor: pointer; color: #dc2626; font-size: 0.8rem; display:flex;align-items:center;gap:6px;"><i class="fa-solid fa-trash"></i> Hapus</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </main>

    <div id="modalTambahBuku" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tambah Buku Baru</h3>
                <button class="close-btn" onclick="toggleModal('modalTambahBuku', false)">&times;</button>
            </div>
            <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Judul Buku</label>
                    <input type="text" name="judul" required placeholder="Contoh: Laskar Pelangi">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Penulis</label>
                        <input type="text" name="penulis" required>
                    </div>
                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="penerbit" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun_terbit" required>
                    </div>
                    <div class="form-group">
                        <label>ISBN</label>
                        <input type="text" name="isbn" required placeholder="Contoh: 978-602-xxx atau 'dalam proses'">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategoris" required>
                            <option value="">Pilih...</option>
                            @foreach(\App\Models\KategoriBuku::all() as $kategori)
                                <option value="{{ $kategori->KategoriID }}">{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Cover</label>
                    <div class="file-upload-wrapper">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Klik untuk upload cover
                        <input type="file" name="cover" id="cover-input" accept="image/*">
                    </div>
                    <div id="cover-preview" style="margin-top: 10px; text-align: center;"></div>
                </div>
                <button type="submit" class="btn-submit">Simpan Buku</button>
            </form>
        </div>
    </div>

    <div id="modalTambahKategori" class="modal-overlay">
        <div class="modal-content" style="width: 400px;">
            <div class="modal-header">
                <h3>Tambah Kategori</h3>
                <button class="close-btn" onclick="toggleModal('modalTambahKategori', false)">&times;</button>
            </div>
            <form action="{{ route('kategoribuku.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="nama_kategori" required placeholder="Contoh: Fiksi Ilmiah">
                </div>
                <button type="submit" class="btn-submit">Simpan Kategori</button>
            </form>
        </div>
    </div>

    <script>
        // --- MODAL LOGIC ---
        function toggleModal(id, show) {
            document.getElementById(id).style.display = show ? 'flex' : 'none';
        }

        document.getElementById('btnTambahBuku').addEventListener('click', () => toggleModal('modalTambahBuku', true));
        document.getElementById('btnTambahKategori').addEventListener('click', () => toggleModal('modalTambahKategori', true));

        // Close on outside click
        window.onclick = function(e) {
            if (e.target.classList.contains('modal-overlay')) e.target.style.display = 'none';
        }

        // --- FILTER LOGIC ---
        const categoryBtn = document.getElementById('categoryBtn');
        const filterModal = document.getElementById('filterModal');

        categoryBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            filterModal.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!filterModal.contains(e.target) && !categoryBtn.contains(e.target)) {
                filterModal.classList.remove('show');
            }
        });

        // Kategori Checkbox Logic
        const kategoriCheckboxes = document.querySelectorAll('.kategori-checkbox');
        kategoriCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.value === '' && this.checked) {
                    kategoriCheckboxes.forEach(cb => { if(cb.value !== '') cb.checked = false; });
                } else if (this.value !== '') {
                    kategoriCheckboxes.forEach(cb => { if(cb.value === '') cb.checked = false; });
                }

                const selected = Array.from(kategoriCheckboxes).find(cb => cb.checked && cb.value !== '');
                let url = "{{ route('dashboardpetugas') }}";
                if(selected) url += '?kategori=' + selected.value;
                window.location.href = url;
            });
        });

        // Set active checkbox from URL
        const urlParams = new URLSearchParams(window.location.search);
        const activeCat = urlParams.get('kategori');
        if(activeCat) {
            kategoriCheckboxes.forEach(cb => { if(cb.value === activeCat) cb.checked = true; });
        } else {
            kategoriCheckboxes.forEach(cb => { if(cb.value === '') cb.checked = true; });
        }

        // Kategori Search
        const kategoriSearch = document.querySelector('.kategori-search');
        if(kategoriSearch){
            kategoriSearch.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const items = document.querySelectorAll('.checkbox-item');
                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(term) ? 'flex' : 'none';
                });
            });
        }

        // Image Preview
        document.getElementById('cover-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('cover-preview');
            if(file) {
                if(file.size > 2 * 1024 * 1024) {
                    alert('File terlalu besar (Max 2MB)');
                    this.value = '';
                    preview.innerHTML = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => preview.innerHTML = `<img src="${e.target.result}" style="height: 100px; border-radius: 6px;">`;
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });
    </script>
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>
