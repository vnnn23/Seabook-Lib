<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrasi ini dibuat untuk memastikan tabel `buku` memiliki skema yang lengkap,
     * konsisten, dan aman terhadap potensi bentrok pada riwayat migrasi terdahulu
     * tanpa menghapus atau mengubah file migrasi lama.
     */
    public function up(): void
    {
        if (!Schema::hasTable('buku')) {
            Schema::create('buku', function (Blueprint $table) {
                $table->id('BukuID');
                $table->string('judul');
                $table->string('penulis');
                $table->string('penerbit');
                $table->year('tahun_terbit');
                $table->string('isbn')->unique();
                $table->text('deskripsi')->nullable();
                $table->string('cover')->nullable();
                $table->integer('stok')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('buku', function (Blueprint $table) {
                if (!Schema::hasColumn('buku', 'judul')) {
                    $table->string('judul')->after('BukuID');
                }
                if (!Schema::hasColumn('buku', 'penulis')) {
                    $table->string('penulis')->after('judul');
                }
                if (!Schema::hasColumn('buku', 'penerbit')) {
                    $table->string('penerbit')->after('penulis');
                }
                if (!Schema::hasColumn('buku', 'tahun_terbit')) {
                    $table->year('tahun_terbit')->after('penerbit');
                }
                if (!Schema::hasColumn('buku', 'isbn')) {
                    $table->string('isbn')->unique()->after('tahun_terbit');
                }
                if (!Schema::hasColumn('buku', 'deskripsi')) {
                    $table->text('deskripsi')->nullable()->after('isbn');
                }
                if (!Schema::hasColumn('buku', 'cover')) {
                    $table->string('cover')->nullable()->after('deskripsi');
                }
                if (!Schema::hasColumn('buku', 'stok')) {
                    $table->integer('stok')->default(0)->after('cover');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrasi ini bersifat kompatibilitas defensif,
        // tidak melakukan drop table agar data buku yang sudah ada tidak hilang.
    }
};

