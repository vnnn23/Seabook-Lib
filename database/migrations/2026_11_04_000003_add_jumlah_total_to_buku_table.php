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
        Schema::table('buku', function (Blueprint $table) {
            if (!Schema::hasColumn('buku', 'jumlah_total')) {
                $table->integer('jumlah_total')->default(0)->after('stok');
            }
        });

        // Hitung jumlah_total = stok (tersedia) + peminjaman aktif status lama ('sedang dipinjam', 'menunggu konfirmasi pengembalian')
        $bukus = DB::table('buku')->get();
        foreach ($bukus as $b) {
            $dipinjamSaatIni = DB::table('peminjaman')
                ->where('BukuID', $b->BukuID)
                ->whereIn('statuspeminjaman', ['sedang dipinjam', 'menunggu konfirmasi pengembalian'])
                ->count();

            $stokTersedia = (int) ($b->stok ?? 0);
            $totalHitung = max(0, $stokTersedia + $dipinjamSaatIni);

            DB::table('buku')
                ->where('BukuID', $b->BukuID)
                ->update(['jumlah_total' => $totalHitung]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku', function (Blueprint $table) {
            if (Schema::hasColumn('buku', 'jumlah_total')) {
                $table->dropColumn('jumlah_total');
            }
        });
    }
};

