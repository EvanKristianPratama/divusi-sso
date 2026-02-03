<?php

namespace App\Services;

/**
 * Service untuk Portal
 * Mengelola daftar aplikasi/modul yang tersedia
 */
class SsoService
{
    /**
     * Get app config dari config/sso.php
     */
    public function getAppConfig(string $app): ?array
    {
        return config("sso.apps.{$app}");
    }

    /**
     * Get semua enabled apps
     */
    public function getEnabledApps(): array
    {
        return collect(config('sso.apps', []))
            ->filter(fn($app) => $app['enabled'] ?? false)
            ->toArray();
    }

    /**
     * Get URL aplikasi
     */
    public function getAppUrl(string $app): ?string
    {
        $config = $this->getAppConfig($app);
        
        if (!$config || !($config['enabled'] ?? false)) {
            return null;
        }

        return $config['url'] ?? null;
    }
}

