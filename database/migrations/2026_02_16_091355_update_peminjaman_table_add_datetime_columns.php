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
        Schema::table('peminjaman', function (Blueprint $table) {
            // Ubah kolom date menjadi datetime untuk mendukung peminjaman kurang dari sehari
            $table->dateTime('tanggal_pinjam')->change();
            $table->dateTime('tanggal_kembali')->change();
            
            // Ubah tanggal_kembali_aktual menjadi datetime juga
            if (Schema::hasColumn('peminjaman', 'tanggal_kembali_aktual')) {
                $table->dateTime('tanggal_kembali_aktual')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->date('tanggal_pinjam')->change();
            $table->date('tanggal_kembali')->change();
            
            if (Schema::hasColumn('peminjaman', 'tanggal_kembali_aktual')) {
                $table->date('tanggal_kembali_aktual')->nullable()->change();
            }
        });
    }
};
