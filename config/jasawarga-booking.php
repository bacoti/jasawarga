<?php

return [
    'min_booking_hours' => 36,
    'partner_response_hours' => 24,
    'slot_interval_minutes' => 30,
    'services' => [
        'table' => 'partner_services',
        'columns' => [
            'id' => 'id',
            'partner_id' => 'partner_profile_id',
            'name' => 'name',
            'price_type' => null, // Tahap 3 tidak punya kolom jenis harga; semua harga awal fixed

            'price' => 'base_price',
            'duration_minutes' => 'duration_minutes',
            'active' => 'is_active',
        ],
        'price_types' => [
            'fixed' => 'fixed',
            'estimate' => 'estimate',
        ],
    ],
    'active_booking_statuses' => [
        'pending_partner_confirmation',
        'inspection_scheduled',
        'pending_price_approval',
        'confirmed',
        'in_progress',
        'completion_pending',
    ],
    'warga_cancelable_statuses' => [
        'pending_partner_confirmation',
        'inspection_scheduled',
        'pending_price_approval',
        'confirmed',
    ],
];
