<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO Configuration
    |--------------------------------------------------------------------------
    */

    // Token expiry dalam menit
    'token_expiry_minutes' => (int) env('SSO_TOKEN_EXPIRY', 5),

    // Daftar aplikasi yang terintegrasi
    'apps' => [
        'cobit' => [
            'name' => 'COBIT',
            'callback_url' => env('SSO_COBIT_URL', 'http://localhost:8001/sso/callback'),
            'enabled' => true,
        ],
        'pmo' => [
            'name' => 'PMO',
            'callback_url' => env('SSO_PMO_URL', 'http://localhost:8002/sso/callback'),
            'enabled' => true,
        ],
        'hr' => [
            'name' => 'HR Management',
            'callback_url' => env('SSO_HR_URL', 'http://localhost:8003/sso/callback'),
            'enabled' => false,
        ],
        'finance' => [
            'name' => 'Finance',
            'callback_url' => env('SSO_FINANCE_URL', 'http://localhost:8004/sso/callback'),
            'enabled' => false,
        ],
    ],
];
