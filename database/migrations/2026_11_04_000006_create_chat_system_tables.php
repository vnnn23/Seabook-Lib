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
        if (!Schema::hasTable('chat_percakapan')) {
            Schema::create('chat_percakapan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('UserID');
                $table->unsignedBigInteger('petugas_id')->nullable();
                $table->string('status', 20)->default('menunggu'); // menunggu, aktif, selesai
                $table->string('mode', 20)->default('ai'); // ai, petugas
                $table->timestamp('terakhir_aktivitas')->nullable();
                $table->timestamps();

                $table->foreign('UserID')->references('UserID')->on('pengguna')->onDelete('cascade');
                $table->foreign('petugas_id')->references('UserID')->on('pengguna')->onDelete('set null');

                $table->index(['UserID', 'status']);
                $table->index(['petugas_id', 'status']);
                $table->index('status');
            });
        }

        if (!Schema::hasTable('chat_pesan')) {
            Schema::create('chat_pesan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('percakapan_id');
                $table->unsignedBigInteger('pengirim_id')->nullable();
                $table->string('pengirim_tipe', 20); // user, petugas, admin, ai
                $table->text('isi');
                $table->timestamp('dibaca_pada')->nullable();
                $table->timestamps();

                $table->foreign('percakapan_id')->references('id')->on('chat_percakapan')->onDelete('cascade');
                $table->foreign('pengirim_id')->references('UserID')->on('pengguna')->onDelete('set null');

                $table->index(['percakapan_id', 'dibaca_pada']);
                $table->index('pengirim_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_pesan');
        Schema::dropIfExists('chat_percakapan');
    }
};

