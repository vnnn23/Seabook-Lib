<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;

class PerpustakaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed default categories
        KategoriBuku::create(['nama_kategori' => 'Teknologi']);
    }

    public function test_welcome_page_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_books_catalog_is_accessible(): void
    {
        $buku = Buku::create([
            'judul' => 'Belajar Laravel Clean Code',
            'penulis' => 'John Doe',
            'penerbit' => 'Tech Press',
            'tahun_terbit' => 2024,
            'isbn' => '978-602-000-1',
            'deskripsi' => 'Buku panduan refactoring dan clean code di Laravel.',
            'cover' => 'covers/default.png',
            'jumlah_total' => 5,
        ]);

        $response = $this->get('/books');
        $response->assertStatus(200);
        $response->assertSee('Belajar Laravel Clean Code');
    }

    public function test_admin_can_access_dashboardadmin(): void
    {
        $admin = User::create([
            'nama' => 'Admin Test',
            'email' => 'admintest@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $response = $this->actingAs($admin)->get('/dashboardadmin');
        $response->assertStatus(200);

        $responseUserList = $this->actingAs($admin)->get('/dashboardadmin2');
        $responseUserList->assertStatus(200);
        $responseUserList->assertViewHas('users');

        $responsePetugasList = $this->actingAs($admin)->get('/dashboardadmin3');
        $responsePetugasList->assertStatus(200);
        $responsePetugasList->assertViewHas('petugas');
    }

    public function test_petugas_can_access_dashboardpetugas(): void
    {
        $petugas = User::create([
            'nama' => 'Petugas Test',
            'email' => 'petugastest@example.com',
            'password' => bcrypt('password123'),
            'role' => 'petugas',
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $response = $this->actingAs($petugas)->get('/dashboardpetugas');
        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::create([
            'nama' => 'User Biasa',
            'email' => 'userbiasa@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $response = $this->actingAs($user)->get('/dashboardadmin');
        $response->assertStatus(403);
    }

    public function test_user_can_view_book_detail(): void
    {
        $buku = Buku::create([
            'judul' => 'Clean Architecture',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2017,
            'isbn' => '978-013-449-4166',
            'deskripsi' => 'A Craftsman Guide to Software Structure and Design.',
            'cover' => 'covers/clean-arch.png',
            'jumlah_total' => 3,
        ]);

        $response = $this->get("/books/{$buku->BukuID}");
        $response->assertStatus(200);
        $response->assertSee('Clean Architecture');
    }

    public function test_user_can_add_to_collection(): void
    {
        $user = User::create([
            'nama' => 'Anggota Test',
            'email' => 'anggota@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'status_akun' => User::STATUS_AKTIF,
        ]);

        $buku = Buku::create([
            'judul' => 'Refactoring Guru',
            'penulis' => 'Alexander Shvets',
            'penerbit' => 'Guru Media',
            'tahun_terbit' => 2020,
            'isbn' => '978-111-222-3333',
            'deskripsi' => 'Guide to design patterns and refactoring.',
            'cover' => 'covers/refactor.png',
            'jumlah_total' => 2,
        ]);

        $response = $this->actingAs($user)->post('/koleksi', [
            'BukuID' => $buku->BukuID,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('koleksi_pribadi', [
            'UserID' => $user->UserID,
            'BukuID' => $buku->BukuID,
        ]);
    }
}
