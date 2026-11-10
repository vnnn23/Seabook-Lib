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
        Schema::table('pengguna', function (Blueprint $table) {
            if (!Schema::hasColumn('pengguna', 'poin')) {
                $table->integer('poin')->default(100)->after('alasan_penolakan');
            }
        });

        // Set default poin = 100 untuk seluruh user yang sudah ada
        DB::table('pengguna')->whereNull('poin')->orWhere('poin', 0)->update(['poin' => 100]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            if (Schema::hasColumn('pengguna', 'poin')) {
                $table->dropColumn('poin');
            }
        });
    }
};

