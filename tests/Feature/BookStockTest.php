<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookStockTest extends TestCase
{
    use RefreshDatabase;

    protected KategoriBuku $kategori;
    protected User $admin;
    protected User $petugas;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori = KategoriBuku::create(['nama_kategori' => 'Teknologi Informasi']);

        $this->admin = User::create([
            'nama' => 'Admin Stock Test',
            'email' => 'adminstock@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $this->petugas = User::create([
            'nama' => 'Petugas Stock Test',
            'email' => 'petugasstock@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_PETUGAS,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $this->user = User::create([
            'nama' => 'User Stock Test',
            'email' => 'userstock@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
        ]);
    }

    /**
     * Test Migrasi 000003:
     * Menguji formula migrasi jumlah_total = stok (tersedia) + peminjaman lama ('sedang dipinjam', 'menunggu konfirmasi pengembalian')
     */
    public function test_migrasi_jumlah_total_menghitung_stok_lama_dan_peminjaman_lama_dengan_benar(): void
    {
        // 1. Simulasikan data lama di tabel buku dengan nilai stok lama = 5 dan jumlah_total awal 0
        $bukuId = DB::table('buku')->insertGetId([
            'judul' => 'Legacy Book Stock Test',
            'penulis' => 'Legacy Author',
            'penerbit' => 'Legacy Publisher',
            'tahun_terbit' => 2021,
            'isbn' => '978-999-000-01',
            'deskripsi' => 'Legacy book description',
            'cover' => 'covers/legacy.png',
            'stok' => 5,
            'jumlah_total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Buat berbagai peminjaman dengan status lama
        // 2 pinjaman 'sedang dipinjam' -> fisik di tangan peminjam
        for ($i = 0; $i < 2; $i++) {
            Peminjaman::create([
                'UserID' => $this->user->UserID,
                'BukuID' => $bukuId,
                'tanggal_pinjam' => Carbon::now()->subDays(2),
                'tanggal_kembali' => Carbon::now()->addDays(5),
                'statuspeminjaman' => 'sedang dipinjam',
            ]);
        }

        // 1 pinjaman 'menunggu konfirmasi pengembalian' -> buku belum diverifikasi kembali
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $bukuId,
            'tanggal_pinjam' => Carbon::now()->subDays(5),
            'tanggal_kembali' => Carbon::now()->subDays(1),
            'statuspeminjaman' => 'menunggu konfirmasi pengembalian',
        ]);

        // 1 pinjaman 'menunggu konfirmasi' (masih antrean permohonan, belum dipinjam fisik)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $bukuId,
            'tanggal_pinjam' => Carbon::now(),
            'tanggal_kembali' => Carbon::now()->addDays(7),
            'statuspeminjaman' => 'menunggu konfirmasi',
        ]);

        // 1 pinjaman 'dikembalikan' (sudah kembali ke rak)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $bukuId,
            'tanggal_pinjam' => Carbon::now()->subDays(10),
            'tanggal_kembali' => Carbon::now()->subDays(3),
            'statuspeminjaman' => 'dikembalikan',
        ]);

        // 1 pinjaman 'ditolak'
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $bukuId,
            'tanggal_pinjam' => Carbon::now()->subDays(8),
            'tanggal_kembali' => Carbon::now()->subDays(2),
            'statuspeminjaman' => 'ditolak',
        ]);

        // 3. Jalankan logika migrasi 000003
        $migration = require database_path('migrations/2026_11_04_000003_add_jumlah_total_to_buku_table.php');
        $migration->up();

        // 4. Periksa hasil: stok tersedia (5) + aktif (2 'sedang dipinjam' + 1 'menunggu konfirmasi pengembalian') = 8
        $bukuUpdated = DB::table('buku')->where('BukuID', $bukuId)->first();
        $this->assertEquals(8, $bukuUpdated->jumlah_total);
    }

    /**
     * Test Accessor total, dipinjam, dan tersedia pada model Buku
     */
    public function test_accessor_total_dipinjam_dan_tersedia_dengan_berbagai_status_peminjaman(): void
    {
        $buku = Buku::create([
            'judul' => 'Algoritma dan Struktur Data Modern',
            'penulis' => 'Prof. Donald',
            'penerbit' => 'Sains Komputer Press',
            'tahun_terbit' => 2023,
            'isbn' => '978-623-111-002',
            'deskripsi' => 'Buku teks komprehensif',
            'jumlah_total' => 10,
        ]);

        // Saat belum ada peminjaman:
        $this->assertEquals(10, $buku->total);
        $this->assertEquals(0, $buku->dipinjam);
        $this->assertEquals(10, $buku->tersedia);

        // Tambah peminjaman dengan berbagai status
        // Status yang mengunci eksemplar: 'disetujui' (1), 'sedang dipinjam' (2), 'menunggu konfirmasi pengembalian' (1)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku->BukuID,
            'tanggal_pinjam' => Carbon::now(),
            'tanggal_kembali' => Carbon::now()->addDays(7),
            'statuspeminjaman' => 'disetujui',
        ]);

        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku->BukuID,
            'tanggal_pinjam' => Carbon::now(),
            'tanggal_kembali' => Carbon::now()->addDays(7),
            'statuspeminjaman' => 'sedang dipinjam',
        ]);

        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku->BukuID,
            'tanggal_pinjam' => Carbon::now(),
            'tanggal_kembali' => Carbon::now()->addDays(7),
            'statuspeminjaman' => 'sedang dipinjam',
        ]);

        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku->BukuID,
            'tanggal_pinjam' => Carbon::now(),
            'tanggal_kembali' => Carbon::now()->addDays(7),
            'statuspeminjaman' => 'menunggu konfirmasi pengembalian',
        ]);

        // Status yang TIDAK mengunci eksemplar: 'dikembalikan' (1), 'ditolak' (1)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku->BukuID,
            'tanggal_pinjam' => Carbon::now()->subDays(10),
            'tanggal_kembali' => Carbon::now()->subDays(3),
            'statuspeminjaman' => 'dikembalikan',
        ]);

        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku->BukuID,
            'tanggal_pinjam' => Carbon::now()->subDays(5),
            'tanggal_kembali' => Carbon::now()->subDays(1),
            'statuspeminjaman' => 'ditolak',
        ]);

        $bukuFresh = $buku->fresh();

        $this->assertEquals(10, $bukuFresh->total);
        $this->assertEquals(4, $bukuFresh->dipinjam); // 1 disetujui + 2 sedang dipinjam + 1 menunggu konfirmasi pengembalian
        $this->assertEquals(6, $bukuFresh->tersedia); // 10 - 4
    }

    /**
     * Test Validasi: Form tambah buku memerlukan jumlah_total >= 0
     */
    public function test_form_tambah_buku_memvalidasi_jumlah_total(): void
    {
        // 1. Tanpa jumlah_total
        $response = $this->actingAs($this->admin)->post('/books', [
            'judul' => 'Buku Baru Tanpa Stok Total',
            'penulis' => 'Penulis X',
            'penerbit' => 'Penerbit Y',
            'tahun_terbit' => 2024,
            'isbn' => '978-123-456-7890',
            'deskripsi' => 'Deskripsi testing',
            'kategoris' => $this->kategori->KategoriID,
        ]);
        $response->assertSessionHasErrors('jumlah_total');

        // 2. jumlah_total negatif
        $responseNeg = $this->actingAs($this->admin)->post('/books', [
            'judul' => 'Buku Stok Negatif',
            'penulis' => 'Penulis X',
            'penerbit' => 'Penerbit Y',
            'tahun_terbit' => 2024,
            'isbn' => '978-123-456-7891',
            'deskripsi' => 'Deskripsi testing',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => -5,
        ]);
        $responseNeg->assertSessionHasErrors('jumlah_total');

        // 3. Sukses membuat buku dengan jumlah_total valid
        $responseSuccess = $this->actingAs($this->admin)->post('/books', [
            'judul' => 'Buku Valid Sukses',
            'penulis' => 'Penulis Hebat',
            'penerbit' => 'Penerbit Sukses',
            'tahun_terbit' => 2024,
            'isbn' => '978-123-456-7892',
            'deskripsi' => 'Deskripsi sukses',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 15,
        ]);
        $responseSuccess->assertRedirect(route('dashboardadmin'));
        $this->assertDatabaseHas('buku', [
            'isbn' => '978-123-456-7892',
            'jumlah_total' => 15,
        ]);
    }

    /**
     * Test Validasi: Form edit buku menolak nilai jumlah_total di bawah eksemplar yang sedang dipinjam
     */
    public function test_form_edit_buku_menolak_jumlah_total_di_bawah_jumlah_eksemplar_dipinjam(): void
    {
        $buku = Buku::create([
            'judul' => 'Buku Uji Edit Validasi',
            'penulis' => 'Penulis Testing',
            'penerbit' => 'Penerbit Testing',
            'tahun_terbit' => 2023,
            'isbn' => '978-777-888-999',
            'deskripsi' => 'Testing validation edit',
            'jumlah_total' => 5,
        ]);
        $buku->kategoris()->attach($this->kategori->KategoriID);

        // Buat 3 peminjaman aktif ('sedang dipinjam')
        for ($i = 0; $i < 3; $i++) {
            Peminjaman::create([
                'UserID' => $this->user->UserID,
                'BukuID' => $buku->BukuID,
                'tanggal_pinjam' => Carbon::now(),
                'tanggal_kembali' => Carbon::now()->addDays(7),
                'statuspeminjaman' => 'sedang dipinjam',
            ]);
        }

        $this->assertEquals(3, $buku->fresh()->dipinjam);

        // 1. Coba turunkan jumlah_total menjadi 2 (< 3 dipinjam) -> harus ditolak
        $responseReject = $this->actingAs($this->admin)->put("/books/{$buku->BukuID}", [
            'judul' => 'Buku Uji Edit Validasi',
            'penulis' => 'Penulis Testing',
            'penerbit' => 'Penerbit Testing',
            'tahun_terbit' => 2023,
            'isbn' => '978-777-888-999',
            'deskripsi' => 'Testing validation edit',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 2,
        ]);

        $responseReject->assertSessionHasErrors('jumlah_total');
        $this->assertEquals(5, $buku->fresh()->jumlah_total);

        // 2. Turunkan jumlah_total menjadi 3 (sama dengan jumlah dipinjam) -> harus berhasil
        $responseSuccess = $this->actingAs($this->admin)->put("/books/{$buku->BukuID}", [
            'judul' => 'Buku Uji Edit Validasi',
            'penulis' => 'Penulis Testing',
            'penerbit' => 'Penerbit Testing',
            'tahun_terbit' => 2023,
            'isbn' => '978-777-888-999',
            'deskripsi' => 'Testing validation edit',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 3,
        ]);

        $responseSuccess->assertRedirect(route('dashboardadmin'));
        $this->assertEquals(3, $buku->fresh()->jumlah_total);
    }

    /**
     * Test Halaman Daftar Buku dan Detail Buku menampilkan Total, Tersedia, dan Dipinjam
     */
    public function test_halaman_daftar_dan_detail_buku_menampilkan_total_tersedia_dipinjam(): void
    {
        $buku = Buku::create([
            'judul' => 'Katalog Visual Testing Eksemplar',
            'penulis' => 'Penulis UI',
            'penerbit' => 'Penerbit Grafis',
            'tahun_terbit' => 2024,
            'isbn' => '978-444-555-666',
            'deskripsi' => 'Buku contoh tampilan',
            'jumlah_total' => 8,
        ]);
        $buku->kategoris()->attach($this->kategori->KategoriID);

        // 2 eksemplar dipinjam
        for ($i = 0; $i < 2; $i++) {
            Peminjaman::create([
                'UserID' => $this->user->UserID,
                'BukuID' => $buku->BukuID,
                'tanggal_pinjam' => Carbon::now(),
                'tanggal_kembali' => Carbon::now()->addDays(7),
                'statuspeminjaman' => 'sedang dipinjam',
            ]);
        }

        // Halaman Daftar Buku (/books)
        $resBooks = $this->get('/books');
        $resBooks->assertOk();
        $resBooks->assertSee('Katalog Visual Testing Eksemplar');
        $resBooks->assertSeeText('Total: 8');
        $resBooks->assertSeeText('Tersedia: 6');
        $resBooks->assertSeeText('Dipinjam: 2');

        // Halaman Detail Buku (/books/{id})
        $resDetail = $this->get("/books/{$buku->BukuID}");
        $resDetail->assertOk();
        $resDetail->assertSee('Katalog Visual Testing Eksemplar');
        $resDetail->assertSee('Total Eksemplar');
        $resDetail->assertSee('Tersedia');
        $resDetail->assertSee('Dipinjam');
        $resDetail->assertSeeText('8');
        $resDetail->assertSeeText('6');
        $resDetail->assertSeeText('2');
    }

    /**
     * Test Halaman Dashboard Admin dan Petugas menampilkan info Total, Tersedia, dan Dipinjam
     */
    public function test_dashboard_admin_dan_petugas_menampilkan_eksemplar_lengkap(): void
    {
        $buku = Buku::create([
            'judul' => 'Dashboard Book Inventory Test',
            'penulis' => 'Author Dashboard',
            'penerbit' => 'Publisher Dashboard',
            'tahun_terbit' => 2023,
            'isbn' => '978-333-222-111',
            'deskripsi' => 'Dashboard testing',
            'jumlah_total' => 6,
        ]);
        $buku->kategoris()->attach($this->kategori->KategoriID);

        // 1 dipinjam
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku->BukuID,
            'tanggal_pinjam' => Carbon::now(),
            'tanggal_kembali' => Carbon::now()->addDays(7),
            'statuspeminjaman' => 'sedang dipinjam',
        ]);

        // Dashboard Admin
        $resAdmin = $this->actingAs($this->admin)->get('/dashboardadmin');
        $resAdmin->assertOk();
        $resAdmin->assertSee('Dashboard Book Inventory Test');
        $resAdmin->assertSeeText('Total: 6');
        $resAdmin->assertSeeText('Tersedia: 5');
        $resAdmin->assertSeeText('Dipinjam: 1');

        // Dashboard Petugas
        $resPetugas = $this->actingAs($this->petugas)->get('/dashboardpetugas');
        $resPetugas->assertOk();
        $resPetugas->assertSee('Dashboard Book Inventory Test');
        $resPetugas->assertSeeText('Total: 6');
        $resPetugas->assertSeeText('Tersedia: 5');
        $resPetugas->assertSeeText('Dipinjam: 1');
    }

    /**
     * Test Upload Cover Buku saat create dan update
     */
    public function test_upload_dan_update_cover_buku_berhasil(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->image('my_cover.jpg');

        $response = $this->actingAs($this->admin)->post('/books', [
            'judul' => 'Buku Dengan Cover File',
            'penulis' => 'Penulis Cover',
            'penerbit' => 'Penerbit Cover',
            'tahun_terbit' => 2024,
            'isbn' => '978-999-888-777',
            'deskripsi' => 'Testing upload cover',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 5,
            'cover' => $file,
        ]);

        $response->assertRedirect(route('dashboardadmin'));

        $buku = Buku::where('isbn', '978-999-888-777')->first();
        $this->assertNotNull($buku);
        $this->assertNotNull($buku->cover);
        $this->assertStringStartsWith('covers/', $buku->cover);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($buku->cover);

        // Update cover
        $newFile = \Illuminate\Http\UploadedFile::fake()->image('updated_cover.png');
        $oldCover = $buku->cover;

        $updateResponse = $this->actingAs($this->admin)->put("/books/{$buku->BukuID}", [
            'judul' => 'Buku Dengan Cover Baru',
            'penulis' => 'Penulis Cover',
            'penerbit' => 'Penerbit Cover',
            'tahun_terbit' => 2024,
            'isbn' => '978-999-888-777',
            'deskripsi' => 'Testing update cover',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 5,
            'cover' => $newFile,
        ]);

        $updateResponse->assertRedirect(route('dashboardadmin'));
        $buku->refresh();
        $this->assertNotEquals($oldCover, $buku->cover);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($buku->cover);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($oldCover);
    }

    /**
     * Test: ISBN placeholder 'dalam proses' boleh sama untuk lebih dari 1 buku,
     * tetapi ISBN riil wajib unik antar buku.
     */
    public function test_isbn_placeholder_dalam_proses_diizinkan_sama_sedangkan_isbn_riil_wajib_unik(): void
    {
        // 1. Buku pertama dengan ISBN 'dalam proses'
        $res1 = $this->actingAs($this->admin)->post('/books', [
            'judul' => 'Buku Proses Pertama',
            'penulis' => 'Penulis A',
            'penerbit' => 'Penerbit A',
            'tahun_terbit' => 2024,
            'isbn' => 'dalam proses',
            'deskripsi' => 'Buku menunggu penerbitan ISBN',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 3,
        ]);
        $res1->assertRedirect(route('dashboardadmin'));
        $this->assertDatabaseHas('buku', [
            'judul' => 'Buku Proses Pertama',
            'isbn' => 'dalam proses',
        ]);

        // 2. Buku kedua dengan ISBN 'Dalam Proses' (variasi huruf kapital) - HARUS BERHASIL
        $res2 = $this->actingAs($this->admin)->post('/books', [
            'judul' => 'Buku Proses Kedua',
            'penulis' => 'Penulis B',
            'penerbit' => 'Penerbit B',
            'tahun_terbit' => 2024,
            'isbn' => 'Dalam Proses',
            'deskripsi' => 'Buku lain yang juga dalam proses',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 4,
        ]);
        $res2->assertRedirect(route('dashboardadmin'));
        $this->assertDatabaseHas('buku', [
            'judul' => 'Buku Proses Kedua',
            'isbn' => 'Dalam Proses',
        ]);

        // 3. Buku ketiga dengan ISBN riil (misal: 978-602-001) - BERHASIL
        $res3 = $this->actingAs($this->admin)->post('/books', [
            'judul' => 'Buku Resmi Pertama',
            'penulis' => 'Penulis C',
            'penerbit' => 'Penerbit C',
            'tahun_terbit' => 2024,
            'isbn' => '978-602-001',
            'deskripsi' => 'Buku ber-ISBN resmi',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 2,
        ]);
        $res3->assertRedirect(route('dashboardadmin'));

        // 4. Buku keempat mencoba menggunakan ISBN riil yang SAMA (978-602-001) - HARUS DITOLAK
        $res4 = $this->actingAs($this->admin)->post('/books', [
            'judul' => 'Buku Resmi Duplikat',
            'penulis' => 'Penulis D',
            'penerbit' => 'Penerbit D',
            'tahun_terbit' => 2024,
            'isbn' => '978-602-001',
            'deskripsi' => 'Duplikat isbn',
            'kategoris' => $this->kategori->KategoriID,
            'jumlah_total' => 2,
        ]);
        $res4->assertSessionHasErrors('isbn');
    }
}
