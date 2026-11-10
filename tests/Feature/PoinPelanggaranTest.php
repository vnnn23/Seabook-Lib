<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\PoinPelanggaranLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PoinPelanggaranTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $user;
    protected Buku $buku;
    protected KategoriBuku $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori = KategoriBuku::create(['nama_kategori' => 'Teknologi Informasi']);

        $this->admin = User::create([
            'nama' => 'Admin Poin Test',
            'email' => 'adminpoin@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 0,
        ]);

        $this->petugas = User::create([
            'nama' => 'Petugas Poin Test',
            'email' => 'petugaspoin@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_PETUGAS,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 0,
        ]);

        $this->user = User::create([
            'nama' => 'User Poin Test',
            'email' => 'userpoin@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 0,
        ]);

        $this->buku = Buku::create([
            'judul' => 'Arsitektur Perangkat Lunak Modern',
            'penulis' => 'Ir. Sudirman',
            'penerbit' => 'Informatika Press',
            'tahun_terbit' => 2024,
            'isbn' => '978-602-000-01',
            'deskripsi' => 'Buku acuan arsitektur sistem.',
            'jumlah_total' => 5,
        ]);
        $this->buku->kategoris()->attach($this->kategori->KategoriID);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset mocked time
        parent::tearDown();
    }

    /**
     * Test 1: Timezone Asia/Jakarta: Pengembalian pukul 16:59 vs 17:01 pada hari jatuh tempo
     */
    public function test_perhitungan_terlambat_pada_hari_jatuh_tempo_1659_vs_1701_di_zona_jakarta()
    {
        $dueDate = Carbon::create(2026, 11, 10, 17, 0, 0, 'Asia/Jakarta');

        // Skenario A: Kembali pukul 16:59:59 (sebelum tenggat)
        $peminjamanA = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $dueDate->copy()->subDays(3)->setTime(9, 0),
            'tanggal_kembali' => $dueDate,
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        Carbon::setTestNow($dueDate->copy()->setTime(16, 59, 59));

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $peminjamanA->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_BAIK,
            ])
            ->assertSessionHas('success');

        $peminjamanA->refresh();
        $this->assertEquals(0, $peminjamanA->hari_terlambat);
        $this->assertEquals(0, $peminjamanA->poin_denda);
        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);

        // Skenario B: Kembali pukul 17:01:00 (1 menit melewati tenggat -> 1 hari terlambat)
        $peminjamanB = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $dueDate->copy()->subDays(3)->setTime(9, 0),
            'tanggal_kembali' => $dueDate,
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        Carbon::setTestNow($dueDate->copy()->setTime(17, 1, 0));

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $peminjamanB->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_BAIK,
            ])
            ->assertSessionHas('success');

        $peminjamanB->refresh();
        $this->assertEquals(1, $peminjamanB->hari_terlambat);
        // Sistem poin dinonaktifkan: poin_denda dan akumulasi poin tidak bertambah
        $this->assertEquals(0, $peminjamanB->poin_denda);
        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);
    }

    /**
     * Test 2: Perhitungan hari terlambat untuk 0, 1, dan 2 hari dengan poin yang sesuai
     */
    public function test_perhitungan_hari_terlambat_0_1_2_hari_dan_poin_terlambat()
    {
        $dueDate = Carbon::create(2026, 11, 10, 17, 0, 0, 'Asia/Jakarta');

        // Kasus 1 hari terlambat (misal 20 jam setelah jatuh tempo)
        $loan1 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $dueDate->copy()->subDays(2),
            'tanggal_kembali' => $dueDate,
            'durasi' => 2,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);
        Carbon::setTestNow($dueDate->copy()->addHours(20));

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loan1->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_BAIK,
            ]);

        $loan1->refresh();
        $this->assertEquals(1, $loan1->hari_terlambat);
        $this->assertEquals(0, $loan1->poin_denda);

        // Kasus 2 hari terlambat (misal 30 jam setelah jatuh tempo -> ceil(30/24) = 2 hari)
        $loan2 = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $dueDate->copy()->subDays(2),
            'tanggal_kembali' => $dueDate,
            'durasi' => 2,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);
        Carbon::setTestNow($dueDate->copy()->addHours(30));

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loan2->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_BAIK,
            ]);

        $loan2->refresh();
        $this->assertEquals(2, $loan2->hari_terlambat);
        $this->assertEquals(0, $loan2->poin_denda); // 2 hari * 2 poin
    }

    /**
     * Test 3: Batas kadaluarsa pengambilan 24 jam setelah disetujui
     */
    public function test_batas_kadaluarsa_pengambilan_24_jam()
    {
        $approvalTime = Carbon::create(2026, 11, 10, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($approvalTime);

        $loan = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $approvalTime->copy()->addDay()->setTime(9, 0),
            'tanggal_kembali' => $approvalTime->copy()->addDays(4)->setTime(16, 0),
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DISETUJUI,
            'qr_code' => 'SYB-EXP-001',
        ]);
        $loan->updated_at = $approvalTime;
        $loan->save();

        // Majukan waktu 25 jam kemudian (melewati batas 24 jam)
        Carbon::setTestNow($approvalTime->copy()->addHours(25));

        // 1. Eksekusi Command peminjaman:check-status
        Artisan::call('peminjaman:check-status');

        $loan->refresh();
        $this->assertEquals(Peminjaman::STATUS_KADALUARSA, $loan->statuspeminjaman);

        // 2. Coba scan pinjaman yang sudah kadaluarsa -> ditolak
        $this->actingAs($this->petugas)
            ->post(route('peminjaman.scan'), [
                'kode_peminjaman' => 'SYB-EXP-001',
            ])
            ->assertSessionHas('error');
    }

    /**
     * Test 4: Kode QR unik dengan prefix konfigurasi dan deteksi bentrok
     */
    public function test_qr_code_unik_dengan_prefix_config()
    {
        $prefix = config('perpustakaan.qr_prefix', 'SYB-');
        $this->assertEquals('SYB-', $prefix);

        Carbon::setTestNow(Carbon::create(2026, 11, 10, 10, 0, 0, 'Asia/Jakarta'));

        $loan = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => now()->addDays(3),
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
        ]);

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirm', $loan->PeminjamanID))
            ->assertSessionHas('success');

        $loan->refresh();
        $this->assertNotNull($loan->qr_code);
        $this->assertStringStartsWith($prefix, $loan->qr_code);
    }

    /**
     * Test 5: Perhitungan poin untuk setiap kondisi buku (rusak ringan: 5, rusak berat: 15, hilang: 30)
     */
    public function test_perhitungan_poin_setiap_kondisi_buku_dan_stok_berkurang()
    {
        $initialStock = $this->buku->jumlah_total; // 5

        // Kasus A: Rusak Ringan (+5 poin, stok tidak berkurang)
        $loanA = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => now()->subDays(2),
            'tanggal_kembali' => now()->addDay(),
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loanA->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_RUSAK_RINGAN,
            ]);

        $loanA->refresh();
        $this->assertEquals(0, $loanA->poin_denda);
        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);
        $this->assertEquals($initialStock, $this->buku->fresh()->jumlah_total);

        // Kasus B: Rusak Berat (poin ditiadakan, stok total berkurang 1)
        $loanB = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => now()->subDays(2),
            'tanggal_kembali' => now()->addDay(),
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loanB->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_RUSAK_BERAT,
            ]);

        $loanB->refresh();
        $this->assertEquals(0, $loanB->poin_denda);
        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);
        $this->assertEquals($initialStock - 1, $this->buku->fresh()->jumlah_total); // 4

        // Kasus C: Hilang (poin ditiadakan, stok total berkurang 1)
        $loanC = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => now()->subDays(2),
            'tanggal_kembali' => now()->addDay(),
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loanC->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_HILANG,
            ]);

        $loanC->refresh();
        $this->assertEquals(0, $loanC->poin_denda);
        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);
        $this->assertEquals($initialStock - 2, $this->buku->fresh()->jumlah_total); // 3
    }

    /**
     * Test 6: Kombinasi keterlambatan + kerusakan buku
     */
    public function test_kombinasi_terlambat_dan_kerusakan_buku()
    {
        $dueDate = Carbon::create(2026, 11, 10, 17, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($dueDate->copy()->addDays(2)->setTime(10, 0)); // Terlambat 2 hari

        $loan = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $dueDate->copy()->subDays(3),
            'tanggal_kembali' => $dueDate,
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loan->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_RUSAK_RINGAN,
            ]);

        $loan->refresh();
        $this->assertEquals(2, $loan->hari_terlambat);
        $this->assertEquals(0, $loan->poin_denda);
        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);
    }

    /**
     * Test 7: Idempotensi confirmReturn: Panggilan kedua tidak menduplikasi pengembalian
     */
    public function test_idempotensi_confirm_return_tidak_menambah_poin_ganda()
    {
        $dueDate = Carbon::create(2026, 11, 10, 17, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($dueDate->copy()->addDay()); // Terlambat 1 hari

        $loan = Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $dueDate->copy()->subDays(3),
            'tanggal_kembali' => $dueDate,
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // Panggilan pertama
        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loan->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_RUSAK_RINGAN,
            ])
            ->assertSessionHas('success');

        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);

        // Panggilan kedua (status sudah 'dikembalikan')
        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loan->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_RUSAK_RINGAN,
            ])
            ->assertSessionHas('error');

        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);
        $this->assertEquals(0, PoinPelanggaranLog::where('PeminjamanID', $loan->PeminjamanID)->count());
    }

    /**
     * Test 8: Peminjaman tidak diblokir oleh poin karena sistem poin dinonaktifkan
     */
    public function test_peminjaman_diblokir_jika_poin_mencapai_ambang_blokir()
    {
        $this->user->poin_pelanggaran = 25;
        $this->user->save();

        // Poin ditiadakan sehingga user tidak diblokir hanya karena nilai poin
        $this->assertFalse($this->user->isBlockedFromBorrowing());

        Carbon::setTestNow(Carbon::create(2026, 11, 10, 10, 0, 0, 'Asia/Jakarta'));

        $response = $this->actingAs($this->user)->post(route('peminjaman.store'), [
            'buku_id' => $this->buku->BukuID,
            'tanggal_pinjam' => '2026-11-10 10:00:00',
            'tanggal_kembali' => '2026-11-13 15:00:00',
        ]);

        $response->assertSessionHas('success');
    }

    /**
     * Test 9: Peminjaman diblokir jika memiliki buku terlambat meskipun poin < 25
     */
    public function test_peminjaman_diblokir_jika_memiliki_pinjaman_aktif_yang_terlambat()
    {
        $this->user->poin_pelanggaran = 0;
        $this->user->save();

        $now = Carbon::create(2026, 11, 10, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // Buat pinjaman aktif yang sudah lewat tanggal kembali (jatuh tempo kemarin)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(5),
            'tanggal_kembali' => $now->copy()->subDay(), // Kemarin (terlambat)
            'durasi' => 4,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        $this->assertTrue($this->user->isBlockedFromBorrowing());

        // Buat buku kedua untuk dicoba dipinjam
        $buku2 = Buku::create([
            'judul' => 'Buku Kedua',
            'penulis' => 'Penulis Kedua',
            'penerbit' => 'Penerbit Dua',
            'tahun_terbit' => 2024,
            'isbn' => '978-602-000-02',
            'jumlah_total' => 3,
        ]);

        $response = $this->actingAs($this->user)->post(route('peminjaman.store'), [
            'buku_id' => $buku2->BukuID,
            'tanggal_pinjam' => '2026-11-10 11:00:00',
            'tanggal_kembali' => '2026-11-13 15:00:00',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('diblokir', session('error'));
    }

    /**
     * Test 10: Admin dapat mengurangi poin pelanggaran dengan alasan wajib
     */
    public function test_admin_dapat_mengurangi_poin_pelanggaran_dengan_alasan_wajib()
    {
        $this->user->poin_pelanggaran = 20;
        $this->user->save();

        // 1. Gagal tanpa alasan
        $this->actingAs($this->admin)
            ->post(route('admin.poin.adjust', $this->user->UserID), [
                'aksi' => 'kurangi',
                'jumlah_poin' => 5,
                'alasan' => '',
            ])
            ->assertSessionHasErrors('alasan');

        $this->assertEquals(20, $this->user->fresh()->poin_pelanggaran);

        // 2. Berhasil dengan alasan
        $this->actingAs($this->admin)
            ->post(route('admin.poin.adjust', $this->user->UserID), [
                'aksi' => 'kurangi',
                'jumlah_poin' => 5,
                'alasan' => 'User telah mengikuti program bakti perpustakaan.',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(15, $this->user->fresh()->poin_pelanggaran);

        $log = PoinPelanggaranLog::where('UserID', $this->user->UserID)->latest('LogID')->first();
        $this->assertNotNull($log);
        $this->assertEquals(PoinPelanggaranLog::JENIS_PENGURANGAN_ADMIN, $log->jenis);
        $this->assertEquals(-5, $log->poin);
        $this->assertEquals($this->admin->UserID, $log->dibuat_oleh);
    }

    /**
     * Test 11: Admin dapat mereset total poin pelanggaran ke 0
     */
    public function test_admin_dapat_mereset_poin_pelanggaran_ke_nol()
    {
        $this->user->poin_pelanggaran = 30;
        $this->user->save();

        $this->actingAs($this->admin)
            ->post(route('admin.poin.adjust', $this->user->UserID), [
                'aksi' => 'reset',
                'alasan' => 'Pengampunan poin awal tahun ajaran baru.',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(0, $this->user->fresh()->poin_pelanggaran);

        $log = PoinPelanggaranLog::where('UserID', $this->user->UserID)->latest('LogID')->first();
        $this->assertNotNull($log);
        $this->assertEquals(PoinPelanggaranLog::JENIS_RESET_ADMIN, $log->jenis);
        $this->assertEquals(-30, $log->poin);
    }

    /**
     * Test 12: Petugas dan user biasa ditolak (403) saat mengakses fitur penyesuaian poin admin
     */
    public function test_petugas_dan_user_biasa_dilarang_mengakses_manajemen_poin_admin()
    {
        // Petugas -> 403
        $this->actingAs($this->petugas)
            ->get(route('admin.poin.index'))
            ->assertStatus(403);

        $this->actingAs($this->petugas)
            ->get(route('admin.poin.show', $this->user->UserID))
            ->assertStatus(403);

        $this->actingAs($this->petugas)
            ->post(route('admin.poin.adjust', $this->user->UserID), [
                'aksi' => 'reset',
                'alasan' => 'Coba reset',
            ])
            ->assertStatus(403);

        // User biasa -> 403
        $this->actingAs($this->user)
            ->get(route('admin.poin.index'))
            ->assertStatus(403);

        $this->actingAs($this->user)
            ->post(route('admin.poin.adjust', $this->user->UserID), [
                'aksi' => 'reset',
                'alasan' => 'Coba reset',
            ])
            ->assertStatus(403);
    }

    /**
     * Test 13: Halaman admin/poin dan admin/poin/{id} dapat diakses sukses oleh Admin (assertOk)
     */
    public function test_halaman_admin_poin_berhasil_dibuka_oleh_admin()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.poin.index'))
            ->assertOk()
            ->assertSee('Manajemen Poin Pelanggaran');

        $this->actingAs($this->admin)
            ->get(route('admin.poin.show', $this->user->UserID))
            ->assertOk()
            ->assertSee($this->user->nama);
    }

    /**
     * Test 14: Halaman user wajib menggunakan sidebar dan tidak boleh menggunakan navbar
     */
    public function test_dashboard_user_dan_katalog_menampilkan_status_poin_dan_blokir()
    {
        // 1. Dashboard user menggunakan sidebar, bukan navbar
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="sybookUserSidebar"', false)
            ->assertDontSee('<nav class="navbar">', false)
            ->assertDontSee('Status Poin Pelanggaran');

        // 2. Katalog buku menggunakan sidebar, bukan navbar
        $this->actingAs($this->user)
            ->get(route('books'))
            ->assertOk()
            ->assertSee('id="sybookUserSidebar"', false)
            ->assertDontSee('<nav class="navbar">', false);

        // 3. Detail buku menggunakan sidebar, bukan navbar
        $this->actingAs($this->user)
            ->get(route('detailbooks', $this->buku->BukuID))
            ->assertOk()
            ->assertSee('id="sybookUserSidebar"', false)
            ->assertDontSee('<nav class="navbar">', false);
    }

    /**
     * Test 15: Sistem poin dinonaktifkan: pengembalian terlambat / hilang tidak mengurangi poin member
     */
    public function test_sistem_100_poin_berkurang_tinjau_akun_60_dan_tinjauan_admin()
    {
        // 1. Akun mulai dengan status aktif
        $member = User::create([
            'nama' => 'Budi Santoso',
            'email' => 'budisantoso@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
            'poin' => 100,
            'poin_pelanggaran' => 0,
        ]);

        $this->assertEquals(100, $member->poin);
        $this->assertFalse($member->isBlockedFromBorrowing());

        // 2. Member meminjam buku dan mengembalikan dengan kondisi hilang
        $dueDate = Carbon::create(2026, 11, 10, 17, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($dueDate->copy()->addDays(5));

        $loan = Peminjaman::create([
            'UserID' => $member->UserID,
            'BukuID' => $this->buku->BukuID,
            'tanggal_pinjam' => $dueDate->copy()->subDays(5),
            'tanggal_kembali' => $dueDate,
            'durasi' => 5,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        $this->actingAs($this->petugas)
            ->post(route('peminjaman.confirmReturn', $loan->PeminjamanID), [
                'kondisi_buku' => Peminjaman::KONDISI_HILANG,
            ])
            ->assertSessionHas('success');

        $member->refresh();
        // Poin TIDAK berkurang karena sistem poin ditiadakan
        $this->assertEquals(100, $member->poin);
        $this->assertEquals(User::STATUS_AKTIF, $member->status_akun);
        $this->assertFalse($member->isBlockedFromBorrowing());

        // 3. Member tetap dapat meminjam buku baru karena tidak ada pemblokiran poin
        $bukuBaru = Buku::create([
            'judul' => 'Pemrograman Web Lanjut',
            'penulis' => 'Penulis Web',
            'penerbit' => 'Penerbit Web',
            'tahun_terbit' => 2024,
            'isbn' => '978-602-000-99',
            'jumlah_total' => 5,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 11, 20, 10, 0, 0, 'Asia/Jakarta'));
        $this->actingAs($member)
            ->post(route('peminjaman.store'), [
                'buku_id' => $bukuBaru->BukuID,
                'tanggal_pinjam' => '2026-11-20 10:00:00',
                'tanggal_kembali' => '2026-11-23 15:00:00',
            ])
            ->assertSessionHas('success');
    }
}
