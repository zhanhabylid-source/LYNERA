<?php

return [
    'default' => 'free',
    'trial_days' => 0,
    'plans' => [
        'free' => [
            'name' => 'Free',
            'price' => 'Rp 0',
            'billing_cycle' => 'selamanya',
            'booking_limit_total' => 10,
            'benefit' => 'Cocok untuk mulai. Hingga 10 booking total dengan fitur inti.',
            'features' => [
                'Kalender Booking',
                'Manajemen Pelanggan',
                'Laporan Dasar',
            ],
            'feature_flags' => [
                'advanced_reports' => false,
                'priority_support' => false,
                'dedicated_assistance' => false,
            ],
            'theme' => 'stone',
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 'Rp 199.000',
            'billing_cycle' => 'per bulan',
            'booking_limit_total' => null,
            'benefit' => 'Paling pas untuk MUA yang sedang bertumbuh. Booking tanpa batas dan operasional lebih lancar.',
            'features' => [
                'Semua fitur Free',
                'Booking Tanpa Batas',
                'Dukungan Prioritas',
            ],
            'feature_flags' => [
                'advanced_reports' => true,
                'priority_support' => true,
                'dedicated_assistance' => false,
            ],
            'theme' => 'rose',
        ],
        'premium' => [
            'name' => 'Premium',
            'price' => 'Rp 399.000',
            'billing_cycle' => 'per bulan',
            'booking_limit_total' => null,
            'benefit' => 'Paket lanjutan dengan dukungan prioritas dan analitik lebih mendalam.',
            'features' => [
                'Semua fitur Pro',
                'Analitik Lanjutan',
                'Pendampingan Khusus',
            ],
            'feature_flags' => [
                'advanced_reports' => true,
                'priority_support' => true,
                'dedicated_assistance' => true,
            ],
            'theme' => 'amber',
        ],
    ],
];
