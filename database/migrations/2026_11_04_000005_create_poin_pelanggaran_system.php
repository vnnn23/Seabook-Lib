<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom poin_pelanggaran di tabel pengguna
        Schema::table('pengguna', function (Blueprint $table) {
            if (!Schema::hasColumn('pengguna', 'poin_pelanggaran')) {
                $table->integer('poin_pelanggaran')->default(0)->after('alasan_penolakan');
            }
        });

        // 2. Beri unique index pada kolom qr_code di tabel peminjaman
        Schema::table('peminjaman', function (Blueprint $table) {
            if (Schema::hasColumn('peminjaman', 'qr_code')) {
                $table->unique('qr_code');
            }
        });

        // 3. Buat tabel riwayat poin_pelanggaran_log
        if (!Schema::hasTable('poin_pelanggaran_log')) {
            Schema::create('poin_pelanggaran_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('UserID');
                $table->unsignedBigInteger('PeminjamanID')->nullable();
                $table->string('jenis', 50); // keterlambatan, rusak ringan, rusak berat, hilang, pengurangan_admin, reset_admin
                $table->integer('poin'); // delta poin (+ atau -)
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('dibuat_oleh');
                $table->timestamps();

                $table->foreign('UserID')->references('UserID')->on('pengguna')->onDelete('cascade');
                $table->foreign('PeminjamanID')->references('PeminjamanID')->on('peminjaman')->onDelete('set null');
                $table->foreign('dibuat_oleh')->references('UserID')->on('pengguna');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poin_pelanggaran_log');

        Schema::table('peminjaman', function (Blueprint $table) {
            if (Schema::hasColumn('peminjaman', 'qr_code')) {
                $table->dropUnique(['qr_code']);
            }
        });

        Schema::table('pengguna', function (Blueprint $table) {
            if (Schema::hasColumn('pengguna', 'poin_pelanggaran')) {
                $table->dropColumn('poin_pelanggaran');
            }
        });
    }
};

