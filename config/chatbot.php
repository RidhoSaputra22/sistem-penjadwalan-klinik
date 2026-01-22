<?php

return [
    /*
    |--------------------------------------------------------------------------
    | NLP Chatbot Config
    |--------------------------------------------------------------------------
    | Pengaturan sederhana untuk chatbot rekomendasi layanan.
    |
    | - stopwords: kata-kata umum yang diabaikan saat pencocokan
    | - synonyms: perluasan kata agar keluhan user lebih mudah match ke layanan
    */

    'stopwords' => [
        'yang', 'dan', 'di', 'ke', 'dari', 'pada', 'untuk', 'dengan', 'atau', 'juga', 'agar', 'supaya',
        'saya', 'aku', 'kami', 'kita', 'anda', 'kamu',
        'ini', 'itu', 'tersebut',
        'lagi', 'sedang', 'sudah', 'belum', 'akan',
        'ada', 'tidak', 'tak', 'bukan',
        'nya', 'lah', 'kah',
        'sebagai', 'tentang', 'paling', 'lebih', 'kurang',
        'hari', 'minggu', 'bulan', 'tahun',
        'dokter', 'klinik',
    ],

    // Contoh sinonim (silakan sesuaikan dengan data layanan Anda)
    'synonyms' => [
        'gigi' => ['mulut', 'odont'],
        'behel' => ['ortodonti', 'bracket'],
        'karang' => ['plak', 'scaling'],
        'cabut' => ['pencabutan'],
        'tambal' => ['penambalan'],
        'akar' => ['saluran'],
    ],
];
