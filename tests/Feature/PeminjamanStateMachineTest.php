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

class PeminjamanStateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $user;
    protected User $otherUser;
    protected Buku $buku1;
    protected Buku $buku2;
    protected KategoriBuku $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori = KategoriBuku::create(['nama_kategori' => 'Sains Komputer']);

        $this->admin = User::create([
            'nama' => 'Admin Peminjaman',
            'email' => 'adminpem@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $this->petugas = User::create([
            'nama' => 'Petugas Peminjaman',
            'email' => 'petugaspem@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_PETUGAS,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $this->user = User::create([
            'nama' => 'User Peminjaman',
            'email' => 'userpem@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $this->otherUser = User::create([
            'nama' => 'User Lain',
            'email' => 'userlain@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $this->buku1 = Buku::create([
            'judul' => 'Arsitektur Komputer Modern',
            'penulis' => 'Andrew S.',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2022,
            'isbn' => '978-013-111-0001',
            'deskripsi' => 'Buku teks sistem komputer',
            'cover' => 'covers/arkom.png',
            'jumlah_total' => 5,
        ]);
        $this->buku1->kategoris()->attach($this->kategori->KategoriID);

        $this->buku2 = Buku::create([
            'judul' => 'Jaringan Komputer Lanjut',
            'penulis' => 'David W.',
            'penerbit' => 'Pearson',
            'tahun_terbit' => 2023,
            'isbn' => '978-013-111-0002',
            'deskripsi' => 'Buku teks jaringan',
            'cover' => 'covers/jarkom.png',
            'jumlah_total' => 3,
        ]);
        $this->buku2->kategoris()->attach($this->kategori->KategoriID);
    }

    /**
     * Test 1: Migrasi 000004 memetakan status lama ke status baru
     */
    public function test_migrasi_000004_memetakan_status_lama_ke_baru_dan_menghitung_ketersediaan(): void
    {
        $id1 = DB::table('peminjaman')->insertGetId([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now()->format('Y-m-d H:i:s'),
            'tanggal_kembali' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'statuspeminjaman' => 'menunggu konfirmasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id2 = DB::table('peminjaman')->insertGetId([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now()->format('Y-m-d H:i:s'),
            'tanggal_kembali' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'statuspeminjaman' => 'menunggu antrian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id3 = DB::table('peminjaman')->insertGetId([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now()->format('Y-m-d H:i:s'),
            'tanggal_kembali' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'statuspeminjaman' => 'siap diambil',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Jalankan logika migrasi 000004
        $migration = require database_path('migrations/2026_11_04_000004_update_peminjaman_schema_state_machine.php');
        $migration->up();

        // Buktikan status terpetakan
        $row1 = DB::table('peminjaman')->where('PeminjamanID', $id1)->first();
        $row2 = DB::table('peminjaman')->where('PeminjamanID', $id2)->first();
        $row3 = DB::table('peminjaman')->where('PeminjamanID', $id3)->first();

        $this->assertEquals(Peminjaman::STATUS_MENUNGGU, $row1->statuspeminjaman);
        $this->assertEquals(Peminjaman::STATUS_MENUNGGU, $row2->statuspeminjaman);
        $this->assertEquals(Peminjaman::STATUS_DISETUJUI, $row3->statuspeminjaman);

        // Buktikan nilai tersedia & dipinjam model Buku setelah pemetaan
        $bukuFresh = $this->buku1->fresh();
        // Hanya 'disetujui' (id3) yang memegang eksemplar fisik
        $this->assertEquals(1, $bukuFresh->dipinjam);
        $this->assertEquals(4, $bukuFresh->tersedia);
    }

    /**
     * Test 2: Scope Buku::withKetersediaan() mencegah N+1 Query
     */
    public function test_scope_buku_with_ketersediaan_mencegah_n_plus_1_query(): void
    {
        // Buat 5 buku tambahan
        for ($i = 3; $i <= 7; $i++) {
            $b = Buku::create([
                'judul' => "Buku Ke-{$i}",
                'penulis' => "Penulis {$i}",
                'penerbit' => "Penerbit {$i}",
                'tahun_terbit' => 2024,
                'isbn' => "978-000-000-000{$i}",
                'cover' => "covers/{$i}.png",
                'jumlah_total' => 10,
            ]);
            $b->kategoris()->attach($this->kategori->KategoriID);

            Peminjaman::create([
                'UserID' => $this->user->UserID,
                'BukuID' => $b->BukuID,
                'tanggal_pinjam' => now(),
                'tanggal_kembali' => now()->addDays(7),
                'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        // Ambil buku dengan scope withKetersediaan()
        $books = Buku::withKetersediaan()->with('kategoris')->get();

        // Akses properti dipinjam dan tersedia di setiap buku (simulasi loop di view Blade)
        foreach ($books as $book) {
            $this->assertIsInt($book->dipinjam);
            $this->assertIsInt($book->tersedia);
        }

        $queryCount = count(DB::getQueryLog());
        // Hanya ada 2 query: 1 query select buku withCount subquery, 1 query eager load kategoris
        $this->assertEquals(2, $queryCount, "Query count harus tepat 2 (tidak bertambah per buku) untuk mencegah N+1.");

        // Verifikasi accessor tunggal tetap bekerja jika dipanggil tanpa scope
        $singleBook = Buku::find($this->buku1->BukuID);
        $this->assertIsInt($singleBook->dipinjam);
        $this->assertIsInt($singleBook->tersedia);
    }

    /**
     * Test 3: Siklus lengkap peminjaman (store -> confirm -> scan -> kembalikan -> confirmReturn)
     */
    public function test_siklus_lengkap_peminjaman(): void
    {
        $jamPinjam = now()->hour >= 8 && now()->hour < 16 ? now()->format('Y-m-d H:i') : now()->addDay()->setTime(10, 0)->format('Y-m-d H:i');
        $jamKembali = Carbon::createFromFormat('Y-m-d H:i', $jamPinjam)->addDays(7)->setTime(14, 0)->format('Y-m-d H:i');

        // 1. User Mengajukan Peminjaman
        $resStore = $this->actingAs($this->user)->post('/peminjaman', [
            'buku_id' => $this->buku1->BukuID,
            'tanggal_pinjam' => $jamPinjam,
            'tanggal_kembali' => $jamKembali,
        ]);
        $resStore->assertRedirect(route('peminjaman.riwayat'));
        $peminjaman = Peminjaman::where('UserID', $this->user->UserID)->where('BukuID', $this->buku1->BukuID)->first();
        $this->assertNotNull($peminjaman);
        $this->assertEquals(Peminjaman::STATUS_MENUNGGU, $peminjaman->statuspeminjaman);

        // 2. Petugas Menyetujui Peminjaman (confirm)
        $resConfirm = $this->actingAs($this->petugas)->post("/peminjaman/confirm/{$peminjaman->PeminjamanID}");
        $resConfirm->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertEquals(Peminjaman::STATUS_DISETUJUI, $peminjaman->statuspeminjaman);
        $this->assertNotEmpty($peminjaman->qr_code);

        // 3. Petugas Melakukan Scan Pengambilan Buku
        $resScan = $this->actingAs($this->petugas)->post('/peminjaman/scan', [
            'kode_peminjaman' => $peminjaman->qr_code,
        ]);
        $resScan->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertEquals(Peminjaman::STATUS_DIPINJAM, $peminjaman->statuspeminjaman);

        // 4. User Mengajukan Pengembalian Buku
        $resKembalikan = $this->actingAs($this->user)->post("/peminjaman/kembalikan/{$peminjaman->PeminjamanID}");
        $resKembalikan->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertEquals(Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN, $peminjaman->statuspeminjaman);

        // 5. Petugas Memverifikasi Pengembalian Buku (confirmReturn)
        $resReturn = $this->actingAs($this->petugas)->post("/peminjaman/confirm-return/{$peminjaman->PeminjamanID}", [
            'kondisi_buku' => Peminjaman::KONDISI_BAIK,
            'catatan_petugas' => 'Buku dikembalikan dalam kondisi mulus.',
        ]);
        $resReturn->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertEquals(Peminjaman::STATUS_DIKEMBALIKAN, $peminjaman->statuspeminjaman);
        $this->assertEquals(Peminjaman::KONDISI_BAIK, $peminjaman->kondisi_buku);
        $this->assertNotNull($peminjaman->tanggal_kembali_aktual);
    }

    /**
     * Test 4: Setiap transisi status ilegal ditolak
     */
    public function test_setiap_transisi_ilegal_ditolak(): void
    {
        // 1. Confirm peminjaman yang BUKAN status 'menunggu persetujuan'
        $loanDipinjam = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);
        $resConfIllegal = $this->actingAs($this->petugas)->post("/peminjaman/confirm/{$loanDipinjam->PeminjamanID}");
        $resConfIllegal->assertSessionHas('error');
        $this->assertEquals(Peminjaman::STATUS_DIPINJAM, $loanDipinjam->fresh()->statuspeminjaman);

        // 2. Scan peminjaman yang BUKAN status 'disetujui'
        $loanMenunggu = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
            'qr_code' => 'QR-ILLEGAL-01',
        ]);
        $resScanIllegal = $this->actingAs($this->petugas)->post('/peminjaman/scan', [
            'kode_peminjaman' => 'QR-ILLEGAL-01',
        ]);
        $resScanIllegal->assertSessionHas('error');
        $this->assertEquals(Peminjaman::STATUS_MENUNGGU, $loanMenunggu->fresh()->statuspeminjaman);

        // 3. User mengajukan pengembalian peminjaman yang BUKAN status 'sedang dipinjam'
        $resKembalikanIllegal = $this->actingAs($this->user)->post("/peminjaman/kembalikan/{$loanMenunggu->PeminjamanID}");
        $resKembalikanIllegal->assertSessionHas('error');
        $this->assertEquals(Peminjaman::STATUS_MENUNGGU, $loanMenunggu->fresh()->statuspeminjaman);

        // 4. ConfirmReturn peminjaman yang statusnya sudah 'dikembalikan'
        $loanSelesai = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now()->subDays(10),
            'tanggal_kembali' => now()->subDays(3),
            'statuspeminjaman' => Peminjaman::STATUS_DIKEMBALIKAN,
        ]);
        $resReturnIllegal = $this->actingAs($this->petugas)->post("/peminjaman/confirm-return/{$loanSelesai->PeminjamanID}", [
            'kondisi_buku' => Peminjaman::KONDISI_BAIK,
        ]);
        $resReturnIllegal->assertSessionHas('error');
    }

    /**
     * Test 5: ConfirmReturn bersifat idempoten (dua kali panggil tidak menduplikasi efek)
     */
    public function test_confirm_return_idempoten_dua_kali_tidak_menggandakan_efek(): void
    {
        $book = Buku::create([
            'judul' => 'Buku Idempoten Test',
            'penulis' => 'Penulis Idempoten',
            'penerbit' => 'Penerbit Test',
            'tahun_terbit' => 2023,
            'isbn' => '978-555-555-001',
            'cover' => 'covers/test.png',
            'jumlah_total' => 5,
        ]);
        $book->kategoris()->attach($this->kategori->KategoriID);

        $loan = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $book->BukuID,
            'tanggal_pinjam' => now()->subDays(5),
            'tanggal_kembali' => now()->addDays(2),
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // Panggilan pertama dengan kondisi hilang: jumlah_total berkurang dari 5 menjadi 4
        $res1 = $this->actingAs($this->petugas)->post("/peminjaman/confirm-return/{$loan->PeminjamanID}", [
            'kondisi_buku' => Peminjaman::KONDISI_HILANG,
        ]);
        $res1->assertSessionHas('success');
        $this->assertEquals(4, $book->fresh()->jumlah_total);
        $this->assertEquals(Peminjaman::STATUS_DIKEMBALIKAN, $loan->fresh()->statuspeminjaman);

        // Panggilan kedua (re-submit / double click)
        $res2 = $this->actingAs($this->petugas)->post("/peminjaman/confirm-return/{$loan->PeminjamanID}", [
            'kondisi_buku' => Peminjaman::KONDISI_HILANG,
        ]);
        $res2->assertSessionHas('error');
        // Jumlah total tidak boleh berkurang lagi! Tetap 4
        $this->assertEquals(4, $book->fresh()->jumlah_total);
    }

    /**
     * Test 6: Batas maksimal 3 pinjaman aktif (pinjaman ke-4 ditolak)
     */
    public function test_batas_maksimal_tiga_pinjaman_aktif(): void
    {
        // Buat 3 pinjaman aktif untuk user (status: menunggu persetujuan, disetujui, sedang dipinjam)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
        ]);

        $buku3 = Buku::create([
            'judul' => 'Buku Extra 1', 'penulis' => 'P1', 'penerbit' => 'P1',
            'tahun_terbit' => 2024, 'isbn' => '978-888-001', 'cover' => 'covers/1.png', 'jumlah_total' => 5,
        ]);
        $buku3->kategoris()->attach($this->kategori->KategoriID);

        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku3->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_DISETUJUI,
        ]);

        $buku4 = Buku::create([
            'judul' => 'Buku Extra 2', 'penulis' => 'P2', 'penerbit' => 'P2',
            'tahun_terbit' => 2024, 'isbn' => '978-888-002', 'cover' => 'covers/2.png', 'jumlah_total' => 5,
        ]);
        $buku4->kategoris()->attach($this->kategori->KategoriID);

        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $buku4->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // Percobaan pengajuan ke-4 harus ditolak
        $jamPinjam = now()->addDay()->setTime(10, 0)->format('Y-m-d H:i');
        $jamKembali = Carbon::createFromFormat('Y-m-d H:i', $jamPinjam)->addDays(5)->setTime(14, 0)->format('Y-m-d H:i');

        $res4 = $this->actingAs($this->user)->post('/peminjaman', [
            'buku_id' => $this->buku2->BukuID,
            'tanggal_pinjam' => $jamPinjam,
            'tanggal_kembali' => $jamKembali,
        ]);

        $res4->assertSessionHas('error');
        $this->assertDatabaseMissing('peminjaman', [
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku2->BukuID,
        ]);
    }

    /**
     * Test 7: Larangan meminjam buku yang sama bila masih aktif
     */
    public function test_larangan_meminjam_buku_yang_sama_bila_masih_memiliki_pinjaman_aktif(): void
    {
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
        ]);

        $jamPinjam = now()->addDay()->setTime(10, 0)->format('Y-m-d H:i');
        $jamKembali = Carbon::createFromFormat('Y-m-d H:i', $jamPinjam)->addDays(5)->setTime(14, 0)->format('Y-m-d H:i');

        // Mengajukan buku1 lagi -> ditolak
        $resDuplicate = $this->actingAs($this->user)->post('/peminjaman', [
            'buku_id' => $this->buku1->BukuID,
            'tanggal_pinjam' => $jamPinjam,
            'tanggal_kembali' => $jamKembali,
        ]);
        $resDuplicate->assertSessionHas('error');

        // Mengajukan buku2 -> sukses
        $resBuku2 = $this->actingAs($this->user)->post('/peminjaman', [
            'buku_id' => $this->buku2->BukuID,
            'tanggal_pinjam' => $jamPinjam,
            'tanggal_kembali' => $jamKembali,
        ]);
        $resBuku2->assertRedirect(route('peminjaman.riwayat'));
    }

    /**
     * Test 8: Pengembalian dua jalur (dari pengajuan user vs langsung oleh petugas)
     */
    public function test_pengembalian_dua_jalur(): void
    {
        // Jalur 1: Dari pengajuan user (status 'menunggu konfirmasi pengembalian')
        $loan1 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now()->subDays(5),
            'tanggal_kembali' => now()->addDays(2),
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN,
        ]);

        $resJalur1 = $this->actingAs($this->petugas)->post("/peminjaman/confirm-return/{$loan1->PeminjamanID}", [
            'kondisi_buku' => Peminjaman::KONDISI_BAIK,
        ]);
        $resJalur1->assertSessionHas('success');
        $this->assertEquals(Peminjaman::STATUS_DIKEMBALIKAN, $loan1->fresh()->statuspeminjaman);

        // Jalur 2: Langsung oleh petugas saat user datang tanpa pengajuan (status 'sedang dipinjam')
        $loan2 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku2->BukuID,
            'tanggal_pinjam' => now()->subDays(5),
            'tanggal_kembali' => now()->addDays(2),
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        $resJalur2 = $this->actingAs($this->petugas)->post("/peminjaman/confirm-return/{$loan2->PeminjamanID}", [
            'kondisi_buku' => Peminjaman::KONDISI_BAIK,
        ]);
        $resJalur2->assertSessionHas('success');
        $this->assertEquals(Peminjaman::STATUS_DIKEMBALIKAN, $loan2->fresh()->statuspeminjaman);

        // Opsi: Belum Diterima mengembalikan status ke 'sedang dipinjam'
        $loan3 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now()->subDays(5),
            'tanggal_kembali' => now()->addDays(2),
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN,
        ]);

        $resBelum = $this->actingAs($this->petugas)->post("/peminjaman/confirm-return/{$loan3->PeminjamanID}", [
            'belum_diterima' => 1,
            'catatan_petugas' => 'Buku fisik belum ada di meja.',
        ]);
        $resBelum->assertSessionHas('info');
        $this->assertEquals(Peminjaman::STATUS_DIPINJAM, $loan3->fresh()->statuspeminjaman);
    }

    /**
     * Test 9: Kondisi buku hilang mengurangi jumlah_total secara aman
     */
    public function test_kondisi_hilang_mengurangi_jumlah_total_buku_secara_aman(): void
    {
        $book = Buku::create([
            'judul' => 'Buku Uji Hilang',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'isbn' => '978-777-111-001',
            'cover' => 'covers/uji.png',
            'jumlah_total' => 2,
        ]);
        $book->kategoris()->attach($this->kategori->KategoriID);

        $loan1 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $book->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        $loan2 = Peminjaman::create([
            'UserID' => $this->otherUser->UserID,
            'BukuID' => $book->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // loan1 dilaporkan hilang: jumlah_total berkurang dari 2 ke 1
        $this->actingAs($this->petugas)->post("/peminjaman/confirm-return/{$loan1->PeminjamanID}", [
            'kondisi_buku' => Peminjaman::KONDISI_HILANG,
        ]);

        $book->refresh();
        $this->assertEquals(1, $book->jumlah_total);
        // Pastikan jumlah_total tidak di bawah loan2 yang masih dipinjam
        $this->assertGreaterThanOrEqual($book->dipinjam, $book->jumlah_total);
    }

    /**
     * Test 10: Scheduled Command peminjaman:check-status mengubah status kadaluarsa
     */
    public function test_scheduled_command_peminjaman_check_status_kadaluarsa(): void
    {
        // 1. Peminjaman disetujui yang dibuat 3 hari lalu (> 48 jam pickup_expiry_hours)
        $loanExpired = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now()->subDays(3),
            'tanggal_kembali' => now()->addDays(4),
            'statuspeminjaman' => Peminjaman::STATUS_DISETUJUI,
        ]);
        // Paksa updated_at dan tanggal_pinjam lewat threshold
        DB::table('peminjaman')->where('PeminjamanID', $loanExpired->PeminjamanID)->update([
            'updated_at' => now()->subHours(50),
            'tanggal_pinjam' => now()->subHours(50),
        ]);

        // 2. Peminjaman disetujui yang masih baru (1 jam lalu)
        $loanFresh = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku2->BukuID,
            'tanggal_pinjam' => now()->addHours(5),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_DISETUJUI,
        ]);

        // Jalankan artisan command
        $this->artisan('peminjaman:check-status')->assertSuccessful();

        $this->assertEquals(Peminjaman::STATUS_KADALUARSA, $loanExpired->fresh()->statuspeminjaman);
        $this->assertEquals(Peminjaman::STATUS_DISETUJUI, $loanFresh->fresh()->statuspeminjaman);
    }

    /**
     * Test 11: Pembatalan oleh user pemilik
     */
    public function test_pembatalan_oleh_user_pemilik(): void
    {
        // Batal saat 'menunggu persetujuan'
        $loan1 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
        ]);

        $res1 = $this->actingAs($this->user)->post("/peminjaman/cancel-user/{$loan1->PeminjamanID}");
        $res1->assertSessionHas('success');
        $this->assertEquals(Peminjaman::STATUS_DIBATALKAN, $loan1->fresh()->statuspeminjaman);

        // Batal saat 'disetujui'
        $loan2 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_DISETUJUI,
        ]);

        $res2 = $this->actingAs($this->user)->post("/peminjaman/cancel-user/{$loan2->PeminjamanID}");
        $res2->assertSessionHas('success');
        $this->assertEquals(Peminjaman::STATUS_DIBATALKAN, $loan2->fresh()->statuspeminjaman);

        // User lain mencoba membatalkan -> ditolak
        $loan3 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
        ]);

        $res3 = $this->actingAs($this->otherUser)->post("/peminjaman/cancel-user/{$loan3->PeminjamanID}");
        $res3->assertSessionHas('error');
        $this->assertEquals(Peminjaman::STATUS_MENUNGGU, $loan3->fresh()->statuspeminjaman);
    }

    /**
     * Test 12: Semua halaman terkait peminjaman benar-benar dibuka (assertOk)
     */
    public function test_semua_halaman_terkait_peminjaman_dibuka_sukses(): void
    {
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(7),
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
        ]);

        // 1. User: Riwayat Peminjaman
        $this->actingAs($this->user)->get('/peminjaman/riwayat')->assertOk();

        // 2. User: Form Peminjaman Create
        $this->actingAs($this->user)->get("/peminjaman/create/{$this->buku1->BukuID}")->assertOk();

        // 3. Petugas: Dashboard Peminjaman Petugas (/dashboardpetugas2)
        $this->actingAs($this->petugas)->get('/dashboardpetugas2')->assertOk();

        // 4. Petugas: Halaman Scan QR Code
        $this->actingAs($this->petugas)->get('/peminjaman/scan')->assertOk();

        // 5. Admin: Dashboard Peminjaman Admin (/dashboardadmin4)
        $this->actingAs($this->admin)->get('/dashboardadmin4')->assertOk();

        // 6. Admin/Petugas: Daftar Peminjaman (/peminjaman)
        $this->actingAs($this->admin)->get('/peminjaman')->assertOk();
    }

    /**
     * Test 13: Peminjaman dengan input tanggal saja (date only YYYY-MM-DD tanpa jam) berhasil
     */
    public function test_peminjaman_dengan_input_tanggal_saja_berhasil(): void
    {
        $buku = Buku::create([
            'judul' => 'Buku Date Only Test',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2026,
            'isbn' => '978-602-999-11',
            'jumlah_total' => 5,
        ]);

        $res = $this->actingAs($this->user)->post('/peminjaman', [
            'buku_id' => $buku->BukuID,
            'tanggal_pinjam' => now()->format('Y-m-d'),
            'tanggal_kembali' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $res->assertRedirect(route('peminjaman.riwayat'));
        $loan = Peminjaman::where('UserID', $this->user->UserID)->where('BukuID', $buku->BukuID)->first();
        $this->assertNotNull($loan);
        $this->assertEquals(Peminjaman::STATUS_MENUNGGU, $loan->statuspeminjaman);
    }
}

