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
        Schema::table('buku', function (Blueprint $table) {
            // Drop unique index pada kolom isbn agar placeholder seperti 'dalam proses' boleh duplikat.
            // Validasi keunikan untuk ISBN riil tetap ditegakkan di level aplikasi (BukuController).
            $table->dropUnique(['isbn']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku', function (Blueprint $table) {
            $table->unique('isbn');
        });
    }
};

