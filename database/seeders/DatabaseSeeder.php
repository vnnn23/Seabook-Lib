<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\KategoriBuku;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $isLocalOrTesting = app()->environment(['local', 'testing']);
        if ($isLocalOrTesting) {
            $defaultPassword = 'password';
        } else {
            $defaultPassword = env('SEEDER_DEFAULT_PASSWORD');
            if (empty($defaultPassword)) {
                throw new \RuntimeException("Seeder dibatalkan: SEEDER_DEFAULT_PASSWORD wajib diatur pada file .env untuk environment non-local (" . app()->environment() . ").");
            }
        }

        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'nama' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt($defaultPassword),
                'role' => User::ROLE_ADMIN,
                'status_akun' => User::STATUS_AKTIF,
            ]);
        }

        if (!User::where('email', 'petugas@example.com')->exists()) {
            User::create([
                'nama' => 'Petugas',
                'email' => 'petugas@example.com',
                'password' => bcrypt($defaultPassword),
                'role' => User::ROLE_PETUGAS,
                'status_akun' => User::STATUS_AKTIF,
            ]);
        }

        $kategoriList = ['Fiksi', 'Non-Fiksi', 'Pendidikan', 'Biografi', 'Anak-Anak'];
        foreach ($kategoriList as $kategori) {
            KategoriBuku::firstOrCreate([
                'nama_kategori' => $kategori,
            ]);
        }
    }
}
