<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class LogoutConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Logout Test',
            'email' => 'adminlogout@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $this->user = User::create([
            'nama' => 'User Logout Test',
            'email' => 'userlogout@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
        ]);
    }

    /**
     * Test modal konfirmasi logout dan aset terpasang di halaman member
     */
    public function test_logout_modal_assets_and_elements_rendered_on_user_pages(): void
    {
        $response = $this->actingAs($this->user)->get('/books');

        $response->assertOk();
        $response->assertSee('logout-modal.css');
        $response->assertSee('logout-confirm.js');
        $response->assertSee('sybookLogoutModal');
        $response->assertSee('Konfirmasi Logout');
        $response->assertSee('Ya, Logout');
        $response->assertSee('Batal');
        $response->assertSee('data-action="logout"', false);
    }

    /**
     * Test modal konfirmasi logout dan tombol logout di sidebar staff admin/petugas
     */
    public function test_logout_modal_and_sidebar_button_rendered_on_staff_pages(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboardadmin');

        $response->assertOk();
        $response->assertSee('sidebar-logout-btn');
        $response->assertSee('sybookLogoutModal');
        $response->assertSee('Konfirmasi Logout');
        $response->assertSee('Ya, Logout');
        $response->assertSee('Batal');
    }

    /**
     * Test form submission ke endpoint POST /logout tetap berhasil mengakhiri sesi
     */
    public function test_direct_logout_post_successfully_invalidates_session(): void
    {
        $this->actingAs($this->user);
        $this->assertTrue(Auth::check());

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertFalse(Auth::check());
    }
}

