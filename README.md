# 📚 SeaBook - Sistem Perpustakaan Digital & Manajemen Sirkulasi

Aplikasi web perpustakaan digital modern berbasis **Laravel 10**, dilengkapi sistem sirkulasi peminjaman berbasis *state machine*, verifikasi pengambilan fisik via Kode QR, sistem poin sanksi pelanggaran (menggantikan denda tunai), dashboard metrik interaktif Chart.js, serta widget layanan interaktif (*Asisten AI* bertenaga LLM dan *Live Chat* petugas/admin dengan proteksi konkurensi).

---

## 🚀 Fitur Utama Sistem

### 1. Sirkulasi Peminjaman Terstruktur (*State Machine*)
- **Siklus Status:** `menunggu persetujuan` $\rightarrow$ `disetujui` $\rightarrow$ `sedang dipinjam` $\rightarrow$ `menunggu konfirmasi pengembalian` $\rightarrow$ `dikembalikan` (atau `ditolak`/`dibatalkan`).
- **Verifikasi Pengambilan:** Dilengkapi kode QR unik yang kedaluwarsa otomatis dalam 24 jam bila buku fisik tidak diambil di meja sirkulasi.
- **Dua Jalur Pengembalian:**
  - Pemustaka mengajukan pengembalian mandiri secara daring (status: *menunggu konfirmasi pengembalian*).
  - Atau langsung datang menyerahkan buku ke petugas untuk diproses seketika.
- **Stok Eksemplar Tunggal:** Menggunakan `jumlah_total` sebagai satu-satunya sumber kebenaran, ketersediaan dihitung real-time (`jumlah_total` dikurangi peminjaman aktif). Jika buku hilang atau rusak berat, `jumlah_total` dikurangi secara aman via transaksi database.

### 2. Sistem Poin Pelanggaran & Anti-Blokir
- Menggantikan denda uang tunai dengan akumulasi poin pelanggaran:
  - Keterlambatan: **+2 poin / hari**
  - Rusak ringan: **+5 poin**
  - Rusak berat: **+15 poin**
  - Buku hilang: **+25 poin**
- **Ambang Blokir Otomatis:** Akun otomatis diblokir dari meminjam jika mencapai **25 poin** atau jika masih memiliki pinjaman aktif yang melewati tanggal jatuh tempo.
- **Konsol Admin:** Admin dapat memutihkan, menyesuaikan, atau mereset poin pelanggaran dengan pencatatan audit log alasan yang wajib diisi.

### 3. Persetujuan Akun Pemustaka (*Member Approval*)
- Registrasi anggota baru (termasuk via Google OAuth) berstatus `pending` dan memerlukan verifikasi serta persetujuan oleh Administrator sebelum dapat meminjam buku.
- Dilengkapi middleware `EnsureAccountActive` yang secara paksa mengeluarkan sesi akun berstatus *pending* atau *ditolak*.

### 4. Dashboard Statistik & Grafik Real-Time (Chart.js 4.4.1)
- Garis tren peminjaman bulanan selama 12 bulan terakhir.
- Batang 10 buku terpopuler dan donat stok (Tersedia vs Dipinjam).
- Distribusi status peminjaman dan sebaran kategori buku.
- Kartu ringkasan metrik (Total Judul, Total Eksemplar, Tersedia, Dipinjam, Menunggu Persetujuan, Terlambat, Akun Pending khusus admin).
- Filter periode rentang tanggal (*Date Range*).

### 5. Chatbot Asisten AI & Live Chat Petugas/Admin
- **Widget Mengambang Pemustaka:** Hadir di seluruh halaman user aktif dengan dua mode yang dapat dialihkan:
  - **Mode Asisten AI:** Menjawab pertanyaan aturan perpustakaan, pencarian katalog, stok buku, dan status pinjaman pengguna saat ini. Terproteksi dari kebocoran data user lain, prompt injection, dan dilengkapi batas harian kuota (default 50 pesan/hari).
  - **Mode Hubungi Petugas:** Antrean live chat langsung dengan staf perpustakaan.
- **Konsol Inbox Petugas/Admin:**
  - Antrean percakapan aman konkurensi (hanya 1 petugas yang dapat mengambil tiket percakapan via transaksi `lockForUpdate`).
  - Polling otomatis (5 detik) yang otomatis berhenti saat tab browser diminimalkan / tidak aktif untuk menghemat bandwidth.

---

## 🛠️ Panduan Instalasi & Menjalankan Proyek

### 1. Kebutuhan Sistem
- PHP >= 8.1 atau 8.2 (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd`, `bcmath`)
- Composer >= 2.x
- MySQL / MariaDB (XAMPP / Laragon / Docker)
- Node.js & NPM (opsional jika ingin me-rebuild aset Vite)

### 2. Langkah Instalasi

1. **Clone atau Buka Direktori Proyek:**
   ```bash
   cd "d:/coding/Perpustakaan digital"
   ```

2. **Instal Dependensi Composer:**
   ```bash
   composer install
   ```

3. **Salin File Konfigurasi Environment:**
   ```bash
   copy .env.example .env
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Konfigurasi Database pada `.env`:**
   Sesuaikan koneksi database MySQL Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=perpustakaan
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Jalankan Migrasi Database & Seeder:**
   ```bash
   php artisan migrate --seed
   ```

7. **Buat Symlink Storage Publik:**
   ```bash
   php artisan storage:link
   ```

8. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui browser di `http://localhost:8000`.

---

## ⚙️ Variabel Environment Penting (`.env`)

| Variabel | Keterangan & Contoh Nilai |
| :--- | :--- |
| `APP_NAME` | Nama perpustakaan digital (contoh: `SeaBook`) |
| `APP_ENV` | Lingkungan aplikasi: `local`, `testing`, atau `production` |
| `APP_KEY` | Kunci enkripsi aplikasi (dihasilkan oleh `php artisan key:generate`) |
| `APP_DEBUG` | Mode debug (`true` untuk lokal, **wajib `false` pada production**) |
| `APP_URL` | URL basis aplikasi (contoh: `http://localhost:8000`) |
| `DB_*` | Kredensial database MySQL |
| `MAIL_*` | Konfigurasi server SMTP untuk notifikasi persetujuan akun |
| `GOOGLE_CLIENT_ID` | Client ID dari Google Cloud Console OAuth 2.0 Web Client |
| `GOOGLE_CLIENT_SECRET`| Client Secret dari Google Cloud Console OAuth 2.0 |
| `GOOGLE_REDIRECT_URI` | `${APP_URL}/auth/google/callback` |
| `CHATBOT_PROVIDER` | Driver AI: `fake` (testing/offline) atau `gemini` (Google Gemini) |
| `CHATBOT_API_KEY` | Kunci API Google AI Studio / Gemini |
| `CHATBOT_MODEL` | Model resmi Gemini aktif (contoh: `gemini-2.5-flash`, `gemini-1.5-flash`) |
| `CHATBOT_DAILY_LIMIT` | Batas maksimal pesan AI per pemustaka per hari (default: `50`) |
| `SEEDER_DEFAULT_PASSWORD`| Password wajib untuk seeder pada environment non-lokal |

---

## ⏱️ Menjalankan Scheduled Tasks (Otomasi Sistem)

Aplikasi SeaBook memiliki tugas terjadwal harian untuk membatalkan antrean pengambilan QR code yang kedaluwarsa:
```bash
php artisan peminjaman:check-status
```

### Di Lingkungan Pengembangan (Lokal):
Jalankan worker penjadwalan pada jendela terminal terpisah:
```bash
php artisan schedule:work
```

### Di Lingkungan Server Produksi / Windows Task Scheduler:
Jalankan perintah berikut setiap menit:
```bash
* * * * * cd /path-ke-proyek && php artisan schedule:run >> /dev/null 2>&1
```
*(Pada Windows, buat Basic Task di Windows Task Scheduler yang mengeksekusi `php.exe artisan schedule:run` setiap 1 menit).*

---

## 🔑 Akun Bawaan Seeder (Lokal / Pengujian)

Setelah menjalankan `php artisan migrate --seed`:

| Role | Email | Password Default | Status Akun |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@example.com` | `password` | `aktif` |
| **Petugas** | `petugas@example.com` | `password` | `aktif` |

> [!CAUTION]
> **Peringatan Keamanan Produksi:**
> 1. Jangan pernah menyalakan `APP_DEBUG=true` di lingkungan produksi publik, karena akan mengekspos variabel rahasia dan database trace saat terjadi error.
> 2. Segera ubah password default `admin@example.com` dan `petugas@example.com` setelah proses instalasi pertama selesai.
> 3. Pada lingkungan `APP_ENV=production`, `DatabaseSeeder` menolak password default `'password'` dan mewajibkan variabel `SEEDER_DEFAULT_PASSWORD` diisi pada `.env`.

---

## 🧪 Pengujian Otomatis (PHPUnit Test Suite)

Untuk menjalankan seluruh unit test dan feature test (menggunakan basis data in-memory SQLite tanpa mengganggu database utama):
```bash
php artisan test
```

Seluruh 70+ pengujian mencakup:
- Persetujuan & otorisasi akun pengguna
- Integritas stok eksemplar tunggal
- Siklus hidup state machine peminjaman & idempotensi
- Akurasi kalkulasi poin keterlambatan & sanksi
- Endpoint statistik & kompatibilitas query cross-database (MySQL & SQLite)
- Keamanan chat, pencegahan XSS, rate limiting, dan proteksi race condition konkurensi petugas.
