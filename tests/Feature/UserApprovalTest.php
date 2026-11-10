<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserApprovalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registrasi menghasilkan user dengan status 'pending' dan tidak langsung login
     */
    public function test_registration_creates_user_with_pending_status(): void
    {
        $response = $this->post('/register', [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengguna', [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
        ]);

        // Pastikan tidak otomatis login
        $this->assertFalse(Auth::check());
    }

    /**
     * Test user dengan status 'pending' ditolak saat login dan melihat pesan menunggu approval
     */
    public function test_pending_user_cannot_login(): void
    {
        $user = User::create([
            'nama' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
        ]);

        $response = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $this->assertFalse(Auth::check());
        $response->assertSessionHas('error', function ($value) {
            return str_contains($value, 'menunggu persetujuan admin');
        });
    }

    /**
     * Test user dengan status 'ditolak' ditolak saat login dan melihat alasan penolakan
     */
    public function test_rejected_user_cannot_login_and_sees_reason(): void
    {
        $user = User::create([
            'nama' => 'Rejected User',
            'email' => 'rejected@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_DITOLAK,
            'alasan_penolakan' => 'Identitas KTP buram atau tidak terbaca.',
        ]);

        $response = $this->post('/login', [
            'email' => 'rejected@example.com',
            'password' => 'password123',
        ]);

        $this->assertFalse(Auth::check());
        $response->assertSessionHas('error', function ($value) {
            return str_contains($value, 'ditolak') && str_contains($value, 'Identitas KTP buram');
        });
    }

    /**
     * Test user dengan status 'aktif' dapat login dan diarahkan ke dashboard user
     */
    public function test_active_user_can_login_and_redirects_to_user_dashboard(): void
    {
        $user = User::create([
            'nama' => 'Active User',
            'email' => 'active@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $response = $this->post('/login', [
            'email' => 'active@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->UserID, Auth::id());
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test hanya admin yang dapat menyetujui akun member
     */
    public function test_admin_can_approve_member(): void
    {
        $admin = User::create([
            'nama' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $member = User::create([
            'nama' => 'Calon Anggota',
            'email' => 'calon@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->post("/admin/members/{$member->UserID}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $member->refresh();
        $this->assertEquals(User::STATUS_AKTIF, $member->status_akun);
        $this->assertEquals($admin->UserID, $member->disetujui_oleh);
        $this->assertNotNull($member->disetujui_pada);
        $this->assertNull($member->alasan_penolakan);
    }

    /**
     * Test admin dapat menolak member dengan mengisi alasan
     */
    public function test_admin_can_reject_member_with_reason(): void
    {
        $admin = User::create([
            'nama' => 'Super Admin',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $member = User::create([
            'nama' => 'Member Ditolak',
            'email' => 'ditolak@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->post("/admin/members/{$member->UserID}/reject", [
            'alasan' => 'Data nomor telepon tidak aktif.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $member->refresh();
        $this->assertEquals(User::STATUS_DITOLAK, $member->status_akun);
        $this->assertEquals($admin->UserID, $member->disetujui_oleh);
        $this->assertEquals('Data nomor telepon tidak aktif.', $member->alasan_penolakan);
    }

    /**
     * Test petugas dan user biasa tidak memiliki hak akses persetujuan member (403 Forbidden)
     */
    public function test_petugas_and_regular_user_cannot_access_or_action_member_approval(): void
    {
        $petugas = User::create([
            'nama' => 'Petugas Lib',
            'email' => 'petugas@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_PETUGAS,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $member = User::create([
            'nama' => 'Member Pending',
            'email' => 'pending2@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
        ]);

        // Petugas mencoba akses halaman approval
        $this->actingAs($petugas)->get('/admin/members/approval')->assertStatus(403);

        // Petugas mencoba aksi approve
        $this->actingAs($petugas)->post("/admin/members/{$member->UserID}/approve")->assertStatus(403);

        // Member biasa mencoba akses
        $regularUser = User::create([
            'nama' => 'Regular User',
            'email' => 'reg@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $this->actingAs($regularUser)->get('/admin/members/approval')->assertStatus(403);
    }

    /**
     * Test admin dan petugas hasil seeder dapat login via POST /login (bukan actingAs)
     */
    public function test_seeder_admin_and_petugas_can_login_via_post_login(): void
    {
        $this->artisan('db:seed');

        // 1. Login sebagai Admin dari seeder
        $responseAdmin = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $responseAdmin->assertRedirect('/dashboardadmin');
        $this->assertTrue(Auth::check());
        $this->assertEquals(User::ROLE_ADMIN, Auth::user()->role);

        // Logout
        $this->post('/logout');
        $this->assertFalse(Auth::check());

        // 2. Login sebagai Petugas dari seeder
        $responsePetugas = $this->post('/login', [
            'email' => 'petugas@example.com',
            'password' => 'password',
        ]);
        $responsePetugas->assertRedirect('/dashboardpetugas');
        $this->assertTrue(Auth::check());
        $this->assertEquals(User::ROLE_PETUGAS, Auth::user()->role);
    }

    /**
     * Test middleware EnsureAccountActive memaksa logout jika status_akun berubah menjadi pending/ditolak
     */
    public function test_ensure_account_active_middleware_forces_logout_for_inactive_session(): void
    {
        $user = User::create([
            'nama' => 'Test Sesi Member',
            'email' => 'sesi@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        // Login sukses
        $this->actingAs($user);
        $this->get('/dashboard')->assertOk();

        // Ubah status akun user di database menjadi pending (misal status diselidiki/dinonaktifkan)
        $user->update(['status_akun' => User::STATUS_PENDING]);

        // Coba akses route terproteksi kembali
        $responsePending = $this->get('/dashboard');
        $responsePending->assertRedirect('/');
        $responsePending->assertSessionHas('error', function ($msg) {
            return str_contains($msg, 'menunggu persetujuan admin');
        });
        $this->assertFalse(Auth::check());

        // Sekarang coba untuk akun yang ditolak
        $user->update([
            'status_akun' => User::STATUS_DITOLAK,
            'alasan_penolakan' => 'Pelanggaran ketentuan perpustakaan.',
        ]);

        $this->actingAs($user);
        $responseDitolak = $this->get('/dashboard');
        $responseDitolak->assertRedirect('/');
        $responseDitolak->assertSessionHas('error', function ($msg) {
            return str_contains($msg, 'ditolak') && str_contains($msg, 'Pelanggaran ketentuan');
        });
        $this->assertFalse(Auth::check());
    }

    /**
     * Test member aktif dapat membuka halaman dashboard user secara langsung (GET assertOk)
     */
    public function test_active_user_can_access_user_dashboard_page(): void
    {
        $user = User::create([
            'nama' => 'Ahmad Dahlan',
            'email' => 'ahmad@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Ahmad Dahlan');
        $response->assertSee('Dashboard Member');
    }

    /**
     * Test user pending belum memiliki poin (0 poin) dan baru mendapat 100 poin setelah disetujui admin
     */
    public function test_pending_user_has_no_points_and_approval_grants_initial_100_points(): void
    {
        $admin = User::create([
            'nama' => 'Admin Poin Approval',
            'email' => 'adminpoinappr@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $member = User::create([
            'nama' => 'Member Calon',
            'email' => 'membercalon@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
            'poin' => 0,
        ]);

        // Akun belum disetujui: poin = 0, status_poin = pending, peminjaman diblokir
        $this->assertEquals(0, $member->poin);
        $this->assertEquals('pending', $member->status_poin);
        $this->assertTrue($member->isBlockedFromBorrowing());

        // Admin menyetujui akun
        $this->actingAs($admin)->post("/admin/members/{$member->UserID}/approve");

        $member->refresh();
        $this->assertEquals(User::STATUS_AKTIF, $member->status_akun);
        $this->assertEquals(100, $member->poin);
        $this->assertEquals('aman', $member->status_poin);
        $this->assertFalse($member->isBlockedFromBorrowing());

        // Log poin perdana tercatat
        $this->assertDatabaseHas('poin_pelanggaran_log', [
            'UserID' => $member->UserID,
            'poin' => 100,
            'jenis' => \App\Models\PoinPelanggaranLog::JENIS_RESET_ADMIN,
        ]);
    }

    /**
     * Test halaman admin poin menampilkan status menunggu persetujuan dan tanpa text-decoration: underline
     */
    public function test_admin_poin_and_dashboardadmin2_ui_for_pending_account(): void
    {
        $admin = User::create([
            'nama' => 'Admin UI Check',
            'email' => 'adminuicheck@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $pendingMember = User::create([
            'nama' => 'Pending Member UI',
            'email' => 'pendingui@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
            'poin' => 0,
        ]);

        // Cek halaman admin poin
        $responsePoin = $this->actingAs($admin)->get('/admin/poin');
        $responsePoin->assertOk();
        $responsePoin->assertSee('Belum Ada Poin');
        $responsePoin->assertSee('Menunggu Persetujuan');
        $responsePoin->assertSee('Proses Akun');

        // Pastikan filter akun sehat tidak menampilkan akun pending
        $responseAman = $this->actingAs($admin)->get('/admin/poin?filter=aman');
        $responseAman->assertOk();
        $responseAman->assertDontSee('pendingui@example.com');

        // Pastikan filter pending menampilkan akun pending
        $responsePendingTab = $this->actingAs($admin)->get('/admin/poin?filter=pending');
        $responsePendingTab->assertOk();
        $responsePendingTab->assertSee('pendingui@example.com');

        // Cek halaman dashboardadmin2 (daftar pengguna) tidak ada text-decoration: underline pada tombol proses
        $responsePengguna = $this->actingAs($admin)->get('/dashboardadmin2');
        $responsePengguna->assertOk();
        $responsePengguna->assertSee('btn-proses-approval');
        $responsePengguna->assertDontSee('text-decoration: underline');
    }
}

