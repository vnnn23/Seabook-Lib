<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aturan & Parameter Operasional SeaBook Perpustakaan
    |--------------------------------------------------------------------------
    */

    // Jam operasional pelayanan peminjaman (08:00 - 17:00)
    'jam_buka' => 8,
    'jam_tutup' => 17,

    // Batas maksimal durasi peminjaman buku dalam hitungan hari
    'durasi_maksimal_hari' => 30,

    // Batas waktu pengambilan setelah disetujui sebelum status menjadi kadaluarsa (jam)
    // Nilai default 24 jam sementara menunggu konfirmasi preferensi dari pengguna
    'pickup_expiry_hours' => 24,

    // Prefix untuk penerbitan kode unik QR Code peminjaman
    'qr_prefix' => 'SYB-',

    // Batas maksimal peminjaman aktif per anggota perpustakaan
    'max_active_loans' => 3,

    /*
    |--------------------------------------------------------------------------
    | Aturan Sistem Poin Akun (Skor 0 - 100)
    |--------------------------------------------------------------------------
    */
    // Poin awal setiap akun pengguna baru
    'poin_awal' => 100,

    // Ambang batas sanksi tinjau akun (jika poin <= 60, akun masuk tinjau & tidak bisa meminjam)
    'ambang_tinjau' => 60,

    // Ambang peringatan sebelum terkena tinjau
    'ambang_peringatan' => 75,

    // Nilai pengurangan poin per pelanggaran
    'poin_terlambat_per_hari' => 2,
    'poin_rusak_ringan' => 5,
    'poin_rusak_berat' => 15,
    'poin_hilang' => 30,
];
