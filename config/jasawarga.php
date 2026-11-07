<?php

// Seluruh data dalam file ini bersifat DEMO untuk prototipe frontend.
// Belum terhubung dengan mitra, verifikasi, tarif, atau lokasi sungguhan.
return [
    'categories' => [
        ['slug' => 'bangunan', 'name' => 'Bangunan', 'description' => 'Renovasi, cat, atap', 'icon' => 'home_repair_service', 'bg' => '#E2E9F7', 'fg' => '#243147'],
        ['slug' => 'ac', 'name' => 'Servis AC', 'description' => 'Cuci, freon, pasang', 'icon' => 'ac_unit', 'bg' => '#FFDCBD', 'fg' => '#874F00'],
        ['slug' => 'listrik', 'name' => 'Listrik', 'description' => 'Instalasi, MCB', 'icon' => 'bolt', 'bg' => '#CEE5FF', 'fg' => '#006098'],
        ['slug' => 'pipa', 'name' => 'Air & Pipa', 'description' => 'Pompa, pipa bocor', 'icon' => 'water_drop', 'bg' => '#D8E2FF', 'fg' => '#465E90'],
        ['slug' => 'elektronik', 'name' => 'Elektronik', 'description' => 'Kulkas, mesin cuci', 'icon' => 'tv', 'bg' => '#D6E3FF', 'fg' => '#006098'],
        ['slug' => 'jahit', 'name' => 'Jahit', 'description' => 'Vermak, jahit baju', 'icon' => 'content_cut', 'bg' => '#DDF4E7', 'fg' => '#176B47'],
        ['slug' => 'kebersihan', 'name' => 'Kebersihan', 'description' => 'Rumah, sofa, kasur', 'icon' => 'cleaning_services', 'bg' => '#D9F4EF', 'fg' => '#166A63'],
    ],
    'providers' => [
        ['id' => 1, 'name' => 'Budi Teknik', 'category' => 'ac', 'service' => 'Servis AC & cuci AC', 'area' => 'Coblong', 'distance' => 1.2, 'rating' => 4.9, 'reviews' => 128, 'price' => 75000, 'available' => true, 'warranty' => true, 'verified' => true, 'initials' => 'BT', 'bg' => '#CEE5FF', 'lat' => -6.8836, 'lng' => 107.6154],
        ['id' => 2, 'name' => 'Pak Joko Santoso', 'category' => 'pipa', 'service' => 'Tukang ledeng & saluran mampet', 'area' => 'Lengkong', 'distance' => 2.4, 'rating' => 4.8, 'reviews' => 92, 'price' => 60000, 'available' => false, 'warranty' => true, 'verified' => true, 'initials' => 'JS', 'bg' => '#D8E2FF', 'lat' => -6.9388, 'lng' => 107.6199],
        ['id' => 3, 'name' => 'Elektrik Jaya Bandung', 'category' => 'listrik', 'service' => 'Instalasi listrik & perbaikan MCB', 'area' => 'Coblong', 'distance' => 0.8, 'rating' => 5.0, 'reviews' => 76, 'price' => 85000, 'available' => true, 'warranty' => true, 'verified' => true, 'initials' => 'EJ', 'bg' => '#FFDCBD', 'lat' => -6.9025, 'lng' => 107.5893],
        ['id' => 4, 'name' => 'Ahli Bangunan Bu Rani', 'category' => 'bangunan', 'service' => 'Perbaikan atap & renovasi rumah', 'area' => 'Sukajadi', 'distance' => 3.1, 'rating' => 4.8, 'reviews' => 64, 'price' => 120000, 'available' => false, 'warranty' => false, 'verified' => true, 'initials' => 'DR', 'bg' => '#D6E3FF', 'lat' => -6.895, 'lng' => 107.6123],
        ['id' => 5, 'name' => 'Tekno Servis Sukajadi', 'category' => 'elektronik', 'service' => 'Servis kulkas & mesin cuci', 'area' => 'Sukajadi', 'distance' => 2.0, 'rating' => 4.9, 'reviews' => 51, 'price' => 95000, 'available' => true, 'warranty' => true, 'verified' => true, 'initials' => 'TS', 'bg' => '#DDF4E7', 'lat' => -6.9239, 'lng' => 107.5925],
        ['id' => 6, 'name' => 'Jahit Bu Nia', 'category' => 'jahit', 'service' => 'Vermak, permak jeans & jahit busana', 'area' => 'Coblong', 'distance' => 1.7, 'rating' => 4.9, 'reviews' => 43, 'price' => 35000, 'available' => true, 'warranty' => false, 'verified' => true, 'initials' => 'BN', 'bg' => '#FFE9D4', 'lat' => -6.8955, 'lng' => 107.6084],
        ['id' => 7, 'name' => 'Bersih Rumah Kita', 'category' => 'kebersihan', 'service' => 'Cuci sofa, kasur & bersih rumah', 'area' => 'Kiaracondong', 'distance' => 4.2, 'rating' => 4.7, 'reviews' => 38, 'price' => 150000, 'available' => true, 'warranty' => true, 'verified' => true, 'initials' => 'BR', 'bg' => '#D9F4EF', 'lat' => -6.9166, 'lng' => 107.653],
        ['id' => 8, 'name' => 'AC Sejuk Bersama', 'category' => 'ac', 'service' => 'Cuci AC & isi freon', 'area' => 'Antapani', 'distance' => 4.8, 'rating' => 4.7, 'reviews' => 35, 'price' => 70000, 'available' => false, 'warranty' => false, 'verified' => false, 'initials' => 'AS', 'bg' => '#E2E9F7', 'lat' => -6.9155, 'lng' => 107.6576],
    ],
];
