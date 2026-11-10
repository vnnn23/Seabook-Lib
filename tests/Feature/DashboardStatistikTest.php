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

class DashboardStatistikTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $user;
    protected Buku $buku1;
    protected Buku $buku2;
    protected KategoriBuku $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori = KategoriBuku::create(['nama_kategori' => 'Teknologi Informasi']);

        $this->admin = User::create([
            'nama' => 'Admin Dashboard Test',
            'email' => 'admindash@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 0,
        ]);

        $this->petugas = User::create([
            'nama' => 'Petugas Dashboard Test',
            'email' => 'petugasdash@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_PETUGAS,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 0,
        ]);

        $this->user = User::create([
            'nama' => 'User Dashboard Test',
            'email' => 'userdash@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 5,
        ]);

        $this->buku1 = Buku::create([
            'judul' => 'Pemrograman Laravel Modern',
            'penulis' => 'Taylor Otwell',
            'penerbit' => 'Laravel Press',
            'tahun_terbit' => 2024,
            'isbn' => '978-602-111-01',
            'deskripsi' => 'Panduan lengkap Laravel.',
            'jumlah_total' => 10,
        ]);
        $this->buku1->kategoris()->attach($this->kategori->KategoriID);

        $this->buku2 = Buku::create([
            'judul' => 'Arsitektur Basis Data Tingkat Lanjut',
            'penulis' => 'C.J. Date',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2023,
            'isbn' => '978-602-111-02',
            'deskripsi' => 'Konsep perancangan basis data relasional.',
            'jumlah_total' => 5,
        ]);
        $this->buku2->kategoris()->attach($this->kategori->KategoriID);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Test 1: Otorisasi akses: Admin & Petugas diizinkan (200), User biasa 403, Guest redirect
     */
    public function test_otorisasi_akses_dashboard_statistik()
    {
        // 1. Admin diizinkan
        $this->actingAs($this->admin)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Statistik & Metrik Perpustakaan', false);

        // 2. Petugas diizinkan
        $this->actingAs($this->petugas)
            ->get(route('dashboard.index'))
            ->assertOk();

        // 3. User biasa ditolak (403 Forbidden)
        $this->actingAs($this->user)
            ->get(route('dashboard.index'))
            ->assertStatus(403);

        $this->actingAs($this->user)
            ->get(route('dashboard.data'))
            ->assertStatus(403);

        // 4. Guest diarahkan ke login
        auth()->logout();
        $this->get(route('dashboard.index'))
            ->assertRedirect(route('login'));
    }

    /**
     * Test 2: Struktur JSON endpoint /dashboard/data
     */
    public function test_struktur_json_endpoint_statistik()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard.data'));

        $response->assertOk()
            ->assertJsonStructure([
                'startDate',
                'endDate',
                'summary' => [
                    'total_judul',
                    'total_eksemplar',
                    'tersedia',
                    'dipinjam',
                    'menunggu_persetujuan',
                    'terlambat',
                    'akun_pending',
                ],
                'charts' => [
                    'tren_peminjaman' => ['labels', 'data', 'empty'],
                    'buku_terpopuler' => ['labels', 'data', 'empty'],
                    'status_peminjaman' => ['labels', 'data', 'empty'],
                    'stok_tersedia_dipinjam' => ['labels', 'data', 'empty'],
                    'peminjaman_kategori' => ['labels', 'data', 'empty'],
                ],
                'top_users_poin',
            ]);
    }

    /**
     * Test 3: Penanganan data kosong (Empty State tanpa error 500)
     */
    public function test_penanganan_data_kosong_menampilkan_pesan_belum_ada_data()
    {
        // Hapus peminjaman (database kosong dari peminjaman)
        Peminjaman::query()->delete();

        $response = $this->actingAs($this->admin)->get(route('dashboard.data'));
        $response->assertOk();

        $json = $response->json();
        $this->assertTrue($json['charts']['tren_peminjaman']['empty']);
        $this->assertTrue($json['charts']['buku_terpopuler']['empty']);
        $this->assertTrue($json['charts']['status_peminjaman']['empty']);
        $this->assertTrue($json['charts']['peminjaman_kategori']['empty']);

        // Halaman web HTML dibuka sukses dan memuat fallback pesan
        $this->actingAs($this->admin)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Belum ada data peminjaman');
    }

    /**
     * Test 4: Akurasi data ringkasan dan pemisahan akun pending khusus admin
     */
    public function test_akurasi_ringkasan_dan_visibilitas_akun_pending_khusus_admin()
    {
        $now = Carbon::create(2026, 11, 10, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // Buat 1 peminjaman sedang dipinjam (tepat waktu)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(2),
            'tanggal_kembali' => $now->copy()->addDays(2),
            'durasi' => 4,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // Buat 1 peminjaman sedang dipinjam (terlambat)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku2->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(5),
            'tanggal_kembali' => $now->copy()->subDay(), // Terlambat kemarin
            'durasi' => 4,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // Buat 1 peminjaman menunggu persetujuan
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->addDay(),
            'tanggal_kembali' => $now->copy()->addDays(3),
            'durasi' => 2,
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
        ]);

        // Buat 2 user pending
        User::create([
            'nama' => 'Pending User 1',
            'email' => 'pending1@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
        ]);
        User::create([
            'nama' => 'Pending User 2',
            'email' => 'pending2@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
        ]);

        // Cek response sebagai Admin
        $resAdmin = $this->actingAs($this->admin)->get(route('dashboard.data'));
        $dataAdmin = $resAdmin->json('summary');

        $this->assertEquals(2, $dataAdmin['total_judul']);
        $this->assertEquals(15, $dataAdmin['total_eksemplar']); // 10 + 5
        $this->assertEquals(2, $dataAdmin['dipinjam']); // 2 status 'sedang dipinjam'
        $this->assertEquals(13, $dataAdmin['tersedia']); // 15 - 2
        $this->assertEquals(1, $dataAdmin['menunggu_persetujuan']);
        $this->assertEquals(1, $dataAdmin['terlambat']);
        $this->assertEquals(2, $dataAdmin['akun_pending']); // Khusus Admin ada angka 2

        // Cek response sebagai Petugas
        $resPetugas = $this->actingAs($this->petugas)->get(route('dashboard.data'));
        $dataPetugas = $resPetugas->json('summary');
        $this->assertNull($dataPetugas['akun_pending']); // Petugas tidak melihat metrik akun_pending
    }

    /**
     * Test 5: Filter rentang tanggal pada endpoint statistik
     */
    public function test_filter_rentang_tanggal_pada_statistik()
    {
        // Peminjaman di bulan Oktober
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => '2026-10-15 10:00:00',
            'tanggal_kembali' => '2026-10-20 15:00:00',
            'durasi' => 5,
            'statuspeminjaman' => Peminjaman::STATUS_DIKEMBALIKAN,
        ]);

        // Peminjaman di bulan November
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku2->BukuID,
            'tanggal_pinjam' => '2026-11-05 10:00:00',
            'tanggal_kembali' => '2026-11-10 15:00:00',
            'durasi' => 5,
            'statuspeminjaman' => Peminjaman::STATUS_DIKEMBALIKAN,
        ]);

        // Filter hanya November
        $res = $this->actingAs($this->admin)->get(route('dashboard.data', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ]));

        $res->assertOk();
        $this->assertEquals('2026-11-01', $res->json('startDate'));
        $this->assertEquals('2026-11-30', $res->json('endDate'));

        // Buku terpopuler dalam range November hanya buku 2
        $topBooks = $res->json('charts.buku_terpopuler');
        $this->assertContains('Arsitektur Basis Data Tingkat Lanjut', $topBooks['labels']);
    }

    /**
     * Test 6: Sisa Fase 6: Dashboard User menampilkan indikator warna jatuh tempo, riwayat poin, dan aturan peminjaman
     */
    public function test_dashboard_user_menampilkan_indikator_jatuh_tempo_riwayat_poin_dan_aturan()
    {
        $now = Carbon::create(2026, 11, 10, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // 1. Pinjaman Terlambat (Indikator Merah)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(5),
            'tanggal_kembali' => $now->copy()->subDay(), // Kemarin (merah)
            'durasi' => 4,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // 2. Pinjaman Segera Berakhir dalam 24 jam (Indikator Kuning)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku2->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(2),
            'tanggal_kembali' => $now->copy()->addHours(12), // 12 jam lagi (kuning)
            'durasi' => 3,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // Catat log poin pelanggaran
        PoinPelanggaranLog::create([
            'UserID' => $this->user->UserID,
            'jenis' => PoinPelanggaranLog::JENIS_KETERLAMBATAN,
            'poin' => 2,
            'keterangan' => 'Terlambat 1 hari pada peminjaman sebelumnya.',
            'dibuat_oleh' => $this->admin->UserID,
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Terlambat')
            ->assertSee('Segera Berakhir')
            ->assertDontSee('Riwayat Catatan Poin Pelanggaran')
            ->assertSee('Aturan Peminjaman Perpustakaan')
            ->assertSee('Jam Operasional:')
            ->assertSee('Durasi Maksimal:')
            ->assertSee('Batas Pinjaman Aktif:')
            ->assertSee('Batas Pengambilan:');
    }

    /**
     * Test 7: Ringkasan aturan peminjaman tampil di form peminjaman
     */
    public function test_ringkasan_aturan_tampil_di_form_peminjaman()
    {
        // 1. Halaman form peminjaman create
        $this->actingAs($this->user)
            ->get(route('peminjaman.create', $this->buku1->BukuID))
            ->assertOk()
            ->assertSee('Ringkasan Aturan Peminjaman')
            ->assertSee('Jam operasional pelayanan:')
            ->assertSee('Durasi maksimal peminjaman:');

        // 2. Halaman detail buku modal
        $this->actingAs($this->user)
            ->get(route('detailbooks', $this->buku1->BukuID))
            ->assertOk()
            ->assertSee('Ringkasan Aturan Peminjaman:');
    }

    /**
     * Test 8: Kartu "Terlambat" hanya menghitung peminjaman berstatus sedang dipinjam
     * atau menunggu konfirmasi pengembalian dengan tanggal_kembali < now().
     * Peminjaman yang sudah dikembalikan, ditolak, dibatalkan, atau disetujui tidak dihitung.
     */
    public function test_kartu_terlambat_hanya_menghitung_sedang_dipinjam_dan_menunggu_pengembalian()
    {
        $now = Carbon::create(2026, 11, 15, 12, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // 1. Lewat tempo tapi sudah DIKEMBALIKAN (TIDAK boleh dihitung terlambat)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(10),
            'tanggal_kembali' => $now->copy()->subDays(3),
            'durasi' => 7,
            'statuspeminjaman' => Peminjaman::STATUS_DIKEMBALIKAN,
        ]);

        // 2. Lewat tempo tapi status DITOLAK (TIDAK boleh dihitung)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(8),
            'tanggal_kembali' => $now->copy()->subDays(2),
            'durasi' => 6,
            'statuspeminjaman' => Peminjaman::STATUS_DITOLAK,
            'alasan_penolakan' => 'Buku rusak',
        ]);

        // 3. Lewat tempo tapi status DIBATALKAN (TIDAK boleh dihitung)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(8),
            'tanggal_kembali' => $now->copy()->subDays(2),
            'durasi' => 6,
            'statuspeminjaman' => Peminjaman::STATUS_DIBATALKAN,
        ]);

        // 4. Lewat tempo tapi status DISETUJUI / belum diambil (TIDAK boleh dihitung terlambat pengembalian)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(8),
            'tanggal_kembali' => $now->copy()->subDays(1),
            'durasi' => 7,
            'statuspeminjaman' => Peminjaman::STATUS_DISETUJUI,
        ]);

        // 5. SEDANG DIPINJAM tapi BELUM lewat tempo (TIDAK boleh dihitung terlambat)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(2),
            'tanggal_kembali' => $now->copy()->addDays(3),
            'durasi' => 5,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // 6. SEDANG DIPINJAM dan LEWAT tempo (HARUS dihitung: +1)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku1->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(7),
            'tanggal_kembali' => $now->copy()->subDays(2),
            'durasi' => 5,
            'statuspeminjaman' => Peminjaman::STATUS_DIPINJAM,
        ]);

        // 7. MENUNGGU KONFIRMASI PENGEMBALIAN dan LEWAT tempo (HARUS dihitung: +1)
        Peminjaman::create([
            'UserID' => $this->user->UserID,
            'BukuID' => $this->buku2->BukuID,
            'tanggal_pinjam' => $now->copy()->subDays(9),
            'tanggal_kembali' => $now->copy()->subDay(),
            'durasi' => 8,
            'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN,
        ]);

        $res = $this->actingAs($this->admin)->get(route('dashboard.data'));
        $res->assertOk();

        // Total yang benar-benar terlambat harus tepat 2
        $this->assertEquals(2, $res->json('summary.terlambat'));
    }
}
