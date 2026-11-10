<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Buku;
use App\Models\KategoriBuku;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageMotionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $user;
    protected Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Motion',
            'email' => 'admin_motion@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 0,
        ]);

        $this->petugas = User::create([
            'nama' => 'Petugas Motion',
            'email' => 'petugas_motion@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_PETUGAS,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 0,
        ]);

        $this->user = User::create([
            'nama' => 'User Motion',
            'email' => 'user_motion@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
            'poin_pelanggaran' => 0,
        ]);

        $kategori = KategoriBuku::create(['nama_kategori' => 'Fiksi']);

        $this->buku = Buku::create([
            'judul' => 'Buku Motion Test',
            'penulis' => 'Penulis Motion',
            'penerbit' => 'Penerbit Test',
            'tahun_terbit' => 2024,
            'isbn' => '978-602-000000',
            'deskripsi' => 'Deskripsi buku untuk motion test',
            'jumlah_total' => 5,
        ]);
        $this->buku->kategoris()->attach($kategori->KategoriID);
    }

    public function test_halaman_welcome_memuat_aset_motion(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('motion.css');
        $response->assertSee('motion.js');
    }

    public function test_halaman_katalog_buku_memuat_aset_motion_dan_kelas_skeleton(): void
    {
        $response = $this->get('/books');
        $response->assertOk();
        $response->assertSee('motion.css');
        $response->assertSee('motion.js');
        $response->assertSee('skeleton-card');
    }

    public function test_halaman_detail_buku_memuat_aset_motion(): void
    {
        $response = $this->get('/books/' . $this->buku->BukuID);
        $response->assertOk();
        $response->assertSee('motion.css');
        $response->assertSee('motion.js');
        $response->assertSee('skeleton-card');
    }

    public function test_halaman_user_dashboard_dan_koleksi_memuat_aset_motion(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertOk();
        $response->assertSee('motion.css');
        $response->assertSee('motion.js');

        $responseKoleksi = $this->actingAs($this->user)->get(route('koleksi.index'));
        $responseKoleksi->assertOk();
        $responseKoleksi->assertSee('motion.css');
        $responseKoleksi->assertSee('motion.js');
    }

    public function test_halaman_peminjaman_dan_riwayat_memuat_aset_motion(): void
    {
        $responsePinjam = $this->actingAs($this->user)->get('/peminjaman/create/' . $this->buku->BukuID);
        $responsePinjam->assertOk();
        $responsePinjam->assertSee('motion.css');
        $responsePinjam->assertSee('motion.js');

        $responseRiwayat = $this->actingAs($this->user)->get('/peminjaman/riwayat');
        $responseRiwayat->assertOk();
        $responseRiwayat->assertSee('motion.css');
        $responseRiwayat->assertSee('motion.js');
    }

    public function test_semua_dashboard_admin_memuat_aset_motion(): void
    {
        // 1. Dashboard Utama Admin (Manajemen Buku)
        $r1 = $this->actingAs($this->admin)->get('/dashboardadmin');
        $r1->assertOk();
        $r1->assertSee('motion.css');
        $r1->assertSee('motion.js');

        // 2. Dashboard Admin 2 (Manajemen Pengguna)
        $r2 = $this->actingAs($this->admin)->get('/dashboardadmin2');
        $r2->assertOk();
        $r2->assertSee('motion.css');
        $r2->assertSee('motion.js');

        // 3. Dashboard Admin 3 (Manajemen Petugas)
        $r3 = $this->actingAs($this->admin)->get('/dashboardadmin3');
        $r3->assertOk();
        $r3->assertSee('motion.css');
        $r3->assertSee('motion.js');

        // 4. Dashboard Admin 4 (Manajemen Peminjaman)
        $r4 = $this->actingAs($this->admin)->get('/dashboardadmin4');
        $r4->assertOk();
        $r4->assertSee('motion.css');
        $r4->assertSee('motion.js');

        // 5. Dashboard Statistik Grafik
        $rStat = $this->actingAs($this->admin)->get(route('dashboard.index'));
        $rStat->assertOk();
        $rStat->assertSee('motion.css');
        $rStat->assertSee('motion.js');

        // 6. Admin Approval & Poin
        $rApprove = $this->actingAs($this->admin)->get(route('admin.members.approval'));
        $rApprove->assertOk();
        $rApprove->assertSee('motion.css');
        $rApprove->assertSee('motion.js');

        $rPoin = $this->actingAs($this->admin)->get(route('admin.poin.index'));
        $rPoin->assertOk();
        $rPoin->assertSee('motion.css');
        $rPoin->assertSee('motion.js');
    }

    public function test_dashboard_petugas_dan_inbox_chat_memuat_aset_motion(): void
    {
        $rPetugas1 = $this->actingAs($this->petugas)->get('/dashboardpetugas');
        $rPetugas1->assertOk();
        $rPetugas1->assertSee('motion.css');
        $rPetugas1->assertSee('motion.js');

        $rPetugas2 = $this->actingAs($this->petugas)->get('/dashboardpetugas2');
        $rPetugas2->assertOk();
        $rPetugas2->assertSee('motion.css');
        $rPetugas2->assertSee('motion.js');

        $rInbox = $this->actingAs($this->petugas)->get(route('chat.inbox'));
        $rInbox->assertOk();
        $rInbox->assertSee('motion.css');
        $rInbox->assertSee('motion.js');
    }

    public function test_berkas_motion_css_dan_js_tersedia_secara_fisik(): void
    {
        $this->assertFileExists(public_path('css/motion.css'));
        $this->assertFileExists(public_path('js/motion.js'));
        $this->assertGreaterThan(0, filesize(public_path('css/motion.css')));
        $this->assertGreaterThan(0, filesize(public_path('js/motion.js')));
    }
}
