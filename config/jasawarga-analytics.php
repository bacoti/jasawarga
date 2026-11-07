<?php

return [
    // Tahap 5 tables available in this merged package; metrics remain optional before migration.
    'optional_tables' => [
        'reviews' => env('JASAWARGA_REVIEWS_TABLE', 'service_reviews'),
        'reports' => env('JASAWARGA_REPORTS_TABLE', 'service_complaints'),
    ],
    // Set this only after checking the exact Stage 5 moderation status used by your schema.
    'reviews_visible_status' => env('JASAWARGA_REVIEWS_VISIBLE_STATUS'),
    'categories' => [
        'table' => env('JASAWARGA_CATEGORIES_TABLE', 'service_categories'),
        'active_column' => env('JASAWARGA_CATEGORIES_ACTIVE_COLUMN', 'is_active'),
    ],
    'services' => [
        'table' => env('JASAWARGA_SERVICES_TABLE', 'partner_services'),
        'active_column' => env('JASAWARGA_SERVICES_ACTIVE_COLUMN', 'is_active'),
    ],
    'date_filter_max_days' => 366,
    'default_period_days' => 30,
    'chart_max_days' => 90,
    'booking_statuses' => [
        'pending_partner_confirmation' => 'Menunggu konfirmasi Mitra',
        'inspection_scheduled' => 'Jadwal pemeriksaan',
        'pending_price_approval' => 'Menunggu persetujuan harga',
        'confirmed' => 'Dikonfirmasi',
        'in_progress' => 'Dalam pengerjaan',
        'completion_pending' => 'Menunggu konfirmasi selesai',
        'completed' => 'Selesai',
        'inspection_completed_no_repair' => 'Pemeriksaan tanpa perbaikan',
        'rejected' => 'Ditolak Mitra',
        'expired' => 'Kedaluwarsa',
        'cancelled' => 'Dibatalkan',
    ],
    'active_booking_statuses' => [
        'pending_partner_confirmation', 'inspection_scheduled', 'pending_price_approval',
        'confirmed', 'in_progress', 'completion_pending',
    ],
    'notifications' => [
        'enabled' => true,
        'admin_new_booking' => true,
        'respect_user_preferences' => true,
    ],
];
