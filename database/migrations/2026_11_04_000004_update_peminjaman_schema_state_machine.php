<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            if (!Schema::hasColumn('peminjaman', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('statuspeminjaman');
            }
            if (!Schema::hasColumn('peminjaman', 'kondisi_buku')) {
                $table->string('kondisi_buku', 50)->nullable()->after('alasan_penolakan');
            }
            if (!Schema::hasColumn('peminjaman', 'catatan_petugas')) {
                $table->text('catatan_petugas')->nullable()->after('kondisi_buku');
            }
            if (!Schema::hasColumn('peminjaman', 'hari_terlambat')) {
                $table->integer('hari_terlambat')->default(0)->after('catatan_petugas');
            }
            if (!Schema::hasColumn('peminjaman', 'poin_denda')) {
                $table->integer('poin_denda')->default(0)->after('hari_terlambat');
            }
        });

        // Petakan status lama ke status baru:
        // 'menunggu konfirmasi' & 'menunggu antrian' -> 'menunggu persetujuan'
        DB::table('peminjaman')
            ->whereIn('statuspeminjaman', ['menunggu konfirmasi', 'menunggu antrian'])
            ->update(['statuspeminjaman' => 'menunggu persetujuan']);

        // 'siap diambil' -> 'disetujui'
        DB::table('peminjaman')
            ->where('statuspeminjaman', 'siap diambil')
            ->update(['statuspeminjaman' => 'disetujui']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan pemetaan status
        DB::table('peminjaman')
            ->where('statuspeminjaman', 'disetujui')
            ->update(['statuspeminjaman' => 'siap diambil']);

        DB::table('peminjaman')
            ->where('statuspeminjaman', 'menunggu persetujuan')
            ->update(['statuspeminjaman' => 'menunggu konfirmasi']);

        Schema::table('peminjaman', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['alasan_penolakan', 'kondisi_buku', 'catatan_petugas', 'hari_terlambat', 'poin_denda'] as $col) {
                if (Schema::hasColumn('peminjaman', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

