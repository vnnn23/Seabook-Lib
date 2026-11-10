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
            $table->integer('durasi')->default(7); // durasi dalam hari, default 7
            $table->decimal('denda', 8, 2)->default(0); // denda
            $table->string('qr_code')->nullable(); // kode QR untuk pickup
            $table->date('tanggal_kembali_aktual')->nullable(); // tanggal pengembalian aktual
            $table->unsignedBigInteger('confirmed_by')->nullable(); // UserID yang mengkonfirmasi
            $table->foreign('confirmed_by')->references('UserID')->on('pengguna');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['durasi', 'denda', 'qr_code', 'tanggal_kembali_aktual', 'confirmed_by']);
        });
    }
};
