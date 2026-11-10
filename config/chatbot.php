<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported provider: "gemini" (Google Gemini API) dan "fake" (untuk pengujian/offline).
    |
    */
    'provider' => env('CHATBOT_PROVIDER', 'fake'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'gemini' => [
            'api_key' => env('CHATBOT_API_KEY'),
            // Dokumentasi resmi model aktif: https://ai.google.dev/gemini-api/docs/models
            'model' => env('CHATBOT_MODEL', 'gemini-3.5-flash'),
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
            'doc_url' => 'https://ai.google.dev/gemini-api/docs/models',
        ],

        'fake' => [
            'driver' => 'fake',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Batasan & Parameter Chatbot
    |--------------------------------------------------------------------------
    */
    'max_message_length' => 500,

    'max_tokens' => 2048, // Batas panjang token output AI (mencakup reasoning/thinking dan teks jawaban)

    'daily_limit_per_user' => env('CHATBOT_DAILY_LIMIT', 50), // Batas maksimal pesan AI per user per hari

    'timeout' => 30, // detik (memberi waktu cukup saat model berpikir dan menyusun panduan lengkap)

    'throttle_send' => '20,1', // 20 pesan per menit

    'throttle_poll' => '120,1', // 120 request per menit

    /*
    |--------------------------------------------------------------------------
    | Pesan Fallback Saat Error atau Belum Dikonfigurasi
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'unconfigured' => 'Layanan Asisten AI belum dikonfigurasi oleh administrator. Anda dapat beralih ke mode "Hubungi Petugas" untuk bantuan langsung.',
        'error_fallback' => 'Maaf, saat ini asisten AI sedang mengalami kendala teknis atau model tidak tersedia. Silakan coba beberapa saat lagi atau beralih ke mode Hubungi Petugas.',
        'off_topic' => 'Maaf, sebagai asisten perpustakaan resmi, saya hanya dapat membantu pertanyaan terkait buku, aturan perpustakaan, serta status akun Anda.',
        'daily_limit_reached' => 'Anda telah mencapai batas maksimal kuota harian pesan AI (50 pesan/hari). Silakan coba lagi besok atau gunakan mode Hubungi Petugas.',
    ],

];
