<?php

/**
 * Data simulasi untuk halaman detail. ID harus cocok dengan config/jasawarga.php.
 * Seluruh layanan, portofolio, testimoni, pengalaman, dan jadwal adalah data fiktif.
 */
return [
    1 => [
        'headline' => 'Teknisi AC rumahan untuk perawatan dan perbaikan harian.',
        'about' => 'Budi Teknik melayani pemeriksaan, pembersihan, dan perawatan unit AC rumah maupun toko kecil. Setiap pekerjaan diawali dengan penjelasan kondisi unit dan perkiraan pengerjaan.',
        'experience' => '6 tahun', 'completed' => '180+', 'response' => '± 20 menit',
        'coverage' => ['Panakkukang', 'Rappocini', 'Makassar'],
        'hours' => 'Senin–Sabtu, 08.00–18.00 WITA',
        'services' => [
            ['name' => 'Cuci AC standar', 'description' => 'Pembersihan filter, evaporator, dan unit luar.', 'price' => 75000, 'duration' => '45–60 menit'],
            ['name' => 'Pemeriksaan AC tidak dingin', 'description' => 'Pengecekan suhu, aliran udara, dan kondisi komponen.', 'price' => 90000, 'duration' => '45–90 menit'],
            ['name' => 'Bongkar pasang AC', 'description' => 'Penanganan unit AC saat pindah ruangan.', 'price' => 250000, 'duration' => '2–3 jam'],
        ],
        'projects' => [
            ['title' => 'Perawatan AC ruang tamu', 'note' => 'Contoh pekerjaan · Panakkukang', 'icon' => 'ac_unit', 'color' => '#cee5ff'],
            ['title' => 'Pemeriksaan unit outdoor', 'note' => 'Contoh pekerjaan · Rappocini', 'icon' => 'settings_suggest', 'color' => '#d8e2ff'],
            ['title' => 'Pemasangan unit AC', 'note' => 'Contoh pekerjaan · Makassar', 'icon' => 'construction', 'color' => '#ffdcbd'],
        ],
        'reviews' => [
            ['name' => 'Ayu P.', 'date' => 'Oktober 2026', 'rating' => 5, 'text' => 'Penjelasan teknisinya mudah dipahami dan proses cuci AC rapi.'],
            ['name' => 'Fadil R.', 'date' => 'September 2026', 'rating' => 5, 'text' => 'Datang sesuai jadwal yang disepakati. AC terasa lebih dingin.'],
            ['name' => 'Rina M.', 'date' => 'Agustus 2026', 'rating' => 4, 'text' => 'Pengerjaan teliti dan harga dijelaskan sebelum mulai.'],
        ],
    ],
    2 => [
        'headline' => 'Penanganan saluran air, keran, dan pipa rumah.',
        'about' => 'Pak Joko membantu memperbaiki kebocoran sederhana, saluran tersumbat, dan masalah perlengkapan air di rumah. Pemeriksaan kondisi instalasi dilakukan sebelum tindakan perbaikan.',
        'experience' => '8 tahun', 'completed' => '230+', 'response' => '± 35 menit',
        'coverage' => ['Tamalate', 'Mariso', 'Rappocini'],
        'hours' => 'Senin–Sabtu, 08.00–17.00 WITA',
        'services' => [
            ['name' => 'Pemeriksaan saluran mampet', 'description' => 'Identifikasi sumber sumbatan dan tindakan ringan.', 'price' => 60000, 'duration' => '30–60 menit'],
            ['name' => 'Perbaikan keran bocor', 'description' => 'Pemeriksaan dan pemasangan komponen keran.', 'price' => 85000, 'duration' => '45–90 menit'],
            ['name' => 'Perbaikan sambungan pipa', 'description' => 'Penanganan kebocoran pada sambungan pipa.', 'price' => 125000, 'duration' => '1–2 jam'],
        ],
        'projects' => [
            ['title' => 'Perbaikan keran', 'note' => 'Contoh pekerjaan · Tamalate', 'icon' => 'water_drop', 'color' => '#d8e2ff'],
            ['title' => 'Penanganan pipa', 'note' => 'Contoh pekerjaan · Mariso', 'icon' => 'plumbing', 'color' => '#cee5ff'],
            ['title' => 'Pemeriksaan saluran', 'note' => 'Contoh pekerjaan · Rappocini', 'icon' => 'handyman', 'color' => '#d9f4ef'],
        ],
        'reviews' => [
            ['name' => 'Wawan S.', 'date' => 'Oktober 2026', 'rating' => 5, 'text' => 'Masalah keran dijelaskan dengan baik sebelum diperbaiki.'],
            ['name' => 'Nia K.', 'date' => 'September 2026', 'rating' => 4, 'text' => 'Rapi dan memberi saran perawatan pipa.'],
            ['name' => 'Dedi A.', 'date' => 'Juli 2026', 'rating' => 5, 'text' => 'Komunikasinya jelas dari awal.'],
        ],
    ],
    3 => [
        'headline' => 'Instalasi dan perbaikan kelistrikan rumah.',
        'about' => 'Elektrik Jaya membantu kebutuhan pemasangan lampu, pemeriksaan MCB, dan perbaikan titik instalasi listrik sesuai kondisi lapangan.',
        'experience' => '5 tahun', 'completed' => '145+', 'response' => '± 25 menit',
        'coverage' => ['Panakkukang', 'Manggala', 'Rappocini'],
        'hours' => 'Senin–Sabtu, 09.00–17.00 WITA',
        'services' => [
            ['name' => 'Pemeriksaan MCB', 'description' => 'Pemeriksaan awal panel dan sumber gangguan.', 'price' => 85000, 'duration' => '45–60 menit'],
            ['name' => 'Pasang lampu', 'description' => 'Pemasangan atau penggantian lampu rumah.', 'price' => 100000, 'duration' => '45–90 menit'],
            ['name' => 'Perbaikan titik stop kontak', 'description' => 'Pemeriksaan dan perbaikan titik listrik.', 'price' => 150000, 'duration' => '1–2 jam'],
        ],
        'projects' => [
            ['title' => 'Pemasangan lampu', 'note' => 'Contoh pekerjaan · Panakkukang', 'icon' => 'lightbulb', 'color' => '#ffdcbd'],
            ['title' => 'Pemeriksaan panel', 'note' => 'Contoh pekerjaan · Manggala', 'icon' => 'bolt', 'color' => '#cee5ff'],
            ['title' => 'Perbaikan stop kontak', 'note' => 'Contoh pekerjaan · Rappocini', 'icon' => 'electrical_services', 'color' => '#d8e2ff'],
        ],
        'reviews' => [
            ['name' => 'Indra H.', 'date' => 'Oktober 2026', 'rating' => 5, 'text' => 'Pemeriksaan gangguan listrik disampaikan secara rinci.'],
            ['name' => 'Vina D.', 'date' => 'September 2026', 'rating' => 5, 'text' => 'Proses pemasangan lampu tertata dan rapi.'],
            ['name' => 'Anwar T.', 'date' => 'Juli 2026', 'rating' => 4, 'text' => 'Komunikatif dan membantu.'],
        ],
    ],
    4 => [
        'headline' => 'Pekerjaan atap, pengecatan, dan renovasi ringan.',
        'about' => 'Daeng Rani menangani pekerjaan perawatan dan renovasi ringan bangunan rumah, mulai dari pemeriksaan atap hingga pengecatan area kecil.',
        'experience' => '10 tahun', 'completed' => '270+', 'response' => '± 40 menit',
        'coverage' => ['Rappocini', 'Panakkukang', 'Tamalate'],
        'hours' => 'Senin–Sabtu, 07.30–16.30 WITA',
        'services' => [
            ['name' => 'Survei kerusakan atap', 'description' => 'Pemeriksaan kondisi dan kebutuhan material.', 'price' => 120000, 'duration' => '45–60 menit'],
            ['name' => 'Perbaikan genteng ringan', 'description' => 'Pekerjaan perbaikan area atap terbatas.', 'price' => 200000, 'duration' => '2–4 jam'],
            ['name' => 'Pengecatan dinding kecil', 'description' => 'Pengecatan area sesuai ukuran dan kondisi.', 'price' => 250000, 'duration' => 'Setengah hari'],
        ],
        'projects' => [
            ['title' => 'Pemeriksaan atap', 'note' => 'Contoh pekerjaan · Rappocini', 'icon' => 'roofing', 'color' => '#d6e3ff'],
            ['title' => 'Pengecatan dinding', 'note' => 'Contoh pekerjaan · Panakkukang', 'icon' => 'format_paint', 'color' => '#ffdcbd'],
            ['title' => 'Perawatan teras', 'note' => 'Contoh pekerjaan · Tamalate', 'icon' => 'construction', 'color' => '#d9f4ef'],
        ],
        'reviews' => [
            ['name' => 'Mira B.', 'date' => 'Oktober 2026', 'rating' => 5, 'text' => 'Memberi gambaran kebutuhan material sebelum bekerja.'],
            ['name' => 'Fauzan N.', 'date' => 'September 2026', 'rating' => 4, 'text' => 'Hasil perbaikan ringan tampak rapi.'],
            ['name' => 'Linda K.', 'date' => 'Agustus 2026', 'rating' => 5, 'text' => 'Bekerja teratur dan komunikatif.'],
        ],
    ],
    5 => [
        'headline' => 'Perawatan peralatan elektronik rumah tangga.',
        'about' => 'Tekno Servis membantu pemeriksaan dan perawatan kulkas, mesin cuci, dan peralatan rumah tangga lain melalui diagnosis awal.',
        'experience' => '7 tahun', 'completed' => '165+', 'response' => '± 30 menit',
        'coverage' => ['Rappocini', 'Panakkukang', 'Makassar'],
        'hours' => 'Senin–Sabtu, 09.00–18.00 WITA',
        'services' => [
            ['name' => 'Pemeriksaan mesin cuci', 'description' => 'Pemeriksaan fungsi putar, air, dan panel.', 'price' => 95000, 'duration' => '45–90 menit'],
            ['name' => 'Pemeriksaan kulkas', 'description' => 'Pengecekan pendinginan dan kelistrikan awal.', 'price' => 110000, 'duration' => '1–2 jam'],
            ['name' => 'Perawatan elektronik ringan', 'description' => 'Pembersihan dan pengecekan komponen.', 'price' => 130000, 'duration' => '1–2 jam'],
        ],
        'projects' => [
            ['title' => 'Pemeriksaan mesin cuci', 'note' => 'Contoh pekerjaan · Rappocini', 'icon' => 'local_laundry_service', 'color' => '#d9f4ef'],
            ['title' => 'Pengecekan kulkas', 'note' => 'Contoh pekerjaan · Panakkukang', 'icon' => 'kitchen', 'color' => '#cee5ff'],
            ['title' => 'Perawatan panel', 'note' => 'Contoh pekerjaan · Makassar', 'icon' => 'settings', 'color' => '#ffdcbd'],
        ],
        'reviews' => [
            ['name' => 'Putri A.', 'date' => 'Oktober 2026', 'rating' => 5, 'text' => 'Pemeriksaannya runtut dan mudah dimengerti.'],
            ['name' => 'Adrian S.', 'date' => 'September 2026', 'rating' => 4, 'text' => 'Estimasi pekerjaan dijelaskan di awal.'],
            ['name' => 'Ocha R.', 'date' => 'Juli 2026', 'rating' => 5, 'text' => 'Ramah dan responsif.'],
        ],
    ],
    6 => [
        'headline' => 'Vermak dan jahit pakaian sesuai kebutuhan.',
        'about' => 'Jahit Bu Nia melayani perbaikan ukuran pakaian, permak celana, serta jahitan sederhana untuk penggunaan sehari-hari.',
        'experience' => '9 tahun', 'completed' => '320+', 'response' => '± 25 menit',
        'coverage' => ['Panakkukang', 'Rappocini', 'Manggala'],
        'hours' => 'Senin–Sabtu, 09.00–17.00 WITA',
        'services' => [
            ['name' => 'Permak panjang celana', 'description' => 'Penyesuaian panjang celana atau jeans.', 'price' => 35000, 'duration' => '1–2 hari'],
            ['name' => 'Kecilkan ukuran pakaian', 'description' => 'Penyesuaian ukuran pinggang atau sisi pakaian.', 'price' => 55000, 'duration' => '2–3 hari'],
            ['name' => 'Jahit ulang resleting', 'description' => 'Perbaikan dan penggantian resleting.', 'price' => 60000, 'duration' => '1–2 hari'],
        ],
        'projects' => [
            ['title' => 'Permak celana', 'note' => 'Contoh pekerjaan · Panakkukang', 'icon' => 'checkroom', 'color' => '#ffdcbd'],
            ['title' => 'Penyesuaian pakaian', 'note' => 'Contoh pekerjaan · Rappocini', 'icon' => 'content_cut', 'color' => '#d9f4ef'],
            ['title' => 'Perbaikan jahitan', 'note' => 'Contoh pekerjaan · Manggala', 'icon' => 'dry_cleaning', 'color' => '#cee5ff'],
        ],
        'reviews' => [
            ['name' => 'Dina L.', 'date' => 'Oktober 2026', 'rating' => 5, 'text' => 'Hasil permak celana sesuai ukuran yang diminta.'],
            ['name' => 'Citra M.', 'date' => 'September 2026', 'rating' => 5, 'text' => 'Jahitan rapi dan komunikasinya baik.'],
            ['name' => 'Fira U.', 'date' => 'Agustus 2026', 'rating' => 4, 'text' => 'Pilihan perbaikan dijelaskan sebelum dikerjakan.'],
        ],
    ],
    7 => [
        'headline' => 'Layanan kebersihan rumah, sofa, dan kasur.',
        'about' => 'Bersih Rumah Kita membantu pekerjaan kebersihan berkala serta pencucian furnitur rumah menggunakan perlengkapan sesuai kebutuhan.',
        'experience' => '4 tahun', 'completed' => '110+', 'response' => '± 45 menit',
        'coverage' => ['Manggala', 'Panakkukang', 'Biringkanaya'],
        'hours' => 'Setiap hari, 08.00–17.00 WITA',
        'services' => [
            ['name' => 'Bersih rumah ringan', 'description' => 'Sapu, pel, dan kebersihan permukaan.', 'price' => 150000, 'duration' => '2–3 jam'],
            ['name' => 'Cuci sofa kecil', 'description' => 'Pembersihan sofa ukuran kecil.', 'price' => 180000, 'duration' => '1–2 jam'],
            ['name' => 'Cuci kasur', 'description' => 'Pembersihan permukaan kasur.', 'price' => 210000, 'duration' => '2–3 jam'],
        ],
        'projects' => [
            ['title' => 'Pembersihan ruang tamu', 'note' => 'Contoh pekerjaan · Manggala', 'icon' => 'cleaning_services', 'color' => '#d9f4ef'],
            ['title' => 'Perawatan sofa', 'note' => 'Contoh pekerjaan · Panakkukang', 'icon' => 'chair', 'color' => '#d8e2ff'],
            ['title' => 'Pembersihan kasur', 'note' => 'Contoh pekerjaan · Biringkanaya', 'icon' => 'bed', 'color' => '#ffdcbd'],
        ],
        'reviews' => [
            ['name' => 'Bella H.', 'date' => 'Oktober 2026', 'rating' => 5, 'text' => 'Ruang tamu terasa lebih bersih setelah pengerjaan.'],
            ['name' => 'Alvin T.', 'date' => 'September 2026', 'rating' => 4, 'text' => 'Jadwal dan cakupan kerja dibicarakan sebelum datang.'],
            ['name' => 'Nora F.', 'date' => 'Agustus 2026', 'rating' => 5, 'text' => 'Pengerjaannya rapi dan sopan.'],
        ],
    ],
    8 => [
        'headline' => 'Layanan cuci dan perawatan AC rumah.',
        'about' => 'AC Sejuk Bersama menyediakan perawatan AC sederhana serta pemeriksaan awal keluhan pendinginan untuk warga di area layanan.',
        'experience' => '3 tahun', 'completed' => '85+', 'response' => '± 50 menit',
        'coverage' => ['Biringkanaya', 'Tamalanrea', 'Manggala'],
        'hours' => 'Senin–Sabtu, 08.00–16.00 WITA',
        'services' => [
            ['name' => 'Cuci AC standar', 'description' => 'Pembersihan ringan unit dalam dan luar.', 'price' => 70000, 'duration' => '45–60 menit'],
            ['name' => 'Pengecekan AC', 'description' => 'Pemeriksaan kondisi dan diagnosis awal.', 'price' => 90000, 'duration' => '45–90 menit'],
            ['name' => 'Perawatan AC berkala', 'description' => 'Pembersihan dan inspeksi fungsi unit.', 'price' => 115000, 'duration' => '1–2 jam'],
        ],
        'projects' => [
            ['title' => 'Pembersihan filter', 'note' => 'Contoh pekerjaan · Biringkanaya', 'icon' => 'air', 'color' => '#cee5ff'],
            ['title' => 'Pengecekan pendingin', 'note' => 'Contoh pekerjaan · Tamalanrea', 'icon' => 'ac_unit', 'color' => '#d8e2ff'],
            ['title' => 'Perawatan AC', 'note' => 'Contoh pekerjaan · Manggala', 'icon' => 'handyman', 'color' => '#ffdcbd'],
        ],
        'reviews' => [
            ['name' => 'Rafa H.', 'date' => 'Oktober 2026', 'rating' => 5, 'text' => 'Pekerjaan cuci AC sesuai kebutuhan.'],
            ['name' => 'Nanda V.', 'date' => 'September 2026', 'rating' => 4, 'text' => 'Mudah diajak koordinasi waktu kunjungan.'],
            ['name' => 'Ratih C.', 'date' => 'Agustus 2026', 'rating' => 5, 'text' => 'Informasi tarifnya cukup jelas.'],
        ],
    ],
];
