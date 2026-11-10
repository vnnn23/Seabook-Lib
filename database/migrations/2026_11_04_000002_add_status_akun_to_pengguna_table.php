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
            if (!Schema::hasColumn('pengguna', 'status_akun')) {
                $table->string('status_akun', 20)->default('pending')->after('role');
            }
            if (!Schema::hasColumn('pengguna', 'disetujui_oleh')) {
                $table->unsignedBigInteger('disetujui_oleh')->nullable()->after('status_akun');
                $table->foreign('disetujui_oleh')->references('UserID')->on('pengguna')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengguna', 'disetujui_pada')) {
                $table->dateTime('disetujui_pada')->nullable()->after('disetujui_oleh');
            }
            if (!Schema::hasColumn('pengguna', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('disetujui_pada');
            }
        });

        // Migrasikan semua akun lama yang sudah ada di database menjadi 'aktif'
        DB::table('pengguna')->update(['status_akun' => 'aktif']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            if (Schema::hasColumn('pengguna', 'disetujui_oleh')) {
                // Drop foreign key if using MySQL/driver that supports it
                if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                    $table->dropForeign(['disetujui_oleh']);
                }
            }
            $columnsToDrop = array_filter(['status_akun', 'disetujui_oleh', 'disetujui_pada', 'alasan_penolakan'], function ($col) {
                return Schema::hasColumn('pengguna', $col);
            });
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

