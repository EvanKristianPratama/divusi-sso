<?php

/**
 * Firebase Configuration
 *
 * Konfigurasi untuk Firebase Admin SDK
 * Service account credentials harus di download dari Firebase Console
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Path ke file service account JSON dari Firebase Console
    | Download dari: Firebase Console → Project Settings → Service Accounts
    |
    */
    'credentials' => storage_path('app/firebase/service-account.json'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Config
    |--------------------------------------------------------------------------
    |
    | Konfigurasi Firebase dari Firebase Console
    |
    */
    'api_key' => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id' => env('FIREBASE_APP_ID'),
];
