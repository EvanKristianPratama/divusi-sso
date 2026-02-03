<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Portal Configuration
    |--------------------------------------------------------------------------
    | Divusi SSO Portal - Menampilkan daftar modul yang tersedia
    | User akan di-redirect ke halaman login masing-masing modul
    */

    // Daftar aplikasi/modul yang tersedia
    'apps' => [
        'cobit' => [
            'name' => 'COBIT Assessment',
            'description' => 'Sistem penilaian dan audit tata kelola IT berbasis COBIT 2019',
            'url' => env('APP_COBIT_URL', 'http://cobit.divusi.local'),
            'icon' => 'shield-check', // heroicons name
            'color' => 'emerald',
            'enabled' => true,
        ],
        'pmo' => [
            'name' => 'Project Management Office',
            'description' => 'Pengelolaan proyek, timeline, dan resource perusahaan',
            'url' => env('APP_PMO_URL', 'http://pmo.divusi.local'),
            'icon' => 'clipboard-document-check',
            'color' => 'blue',
            'enabled' => true,
        ],
        'hr' => [
            'name' => 'HR Management',
            'description' => 'Pengelolaan SDM, absensi, dan payroll karyawan',
            'url' => env('APP_HR_URL', 'http://hr.divusi.local'),
            'icon' => 'users',
            'color' => 'violet',
            'enabled' => false,
        ],
        'finance' => [
            'name' => 'Finance & Accounting',
            'description' => 'Pengelolaan keuangan, budgeting, dan laporan finansial',
            'url' => env('APP_FINANCE_URL', 'http://finance.divusi.local'),
            'icon' => 'currency-dollar',
            'color' => 'amber',
            'enabled' => false,
        ],
    ],
];
