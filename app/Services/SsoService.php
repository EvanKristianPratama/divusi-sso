<?php

namespace App\Services;

use App\Models\SsoToken;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Service untuk handle SSO Token operations
 * Single Responsibility: Generate dan validate SSO tokens
 */
class SsoService
{
    private const TOKEN_LENGTH = 64;

    /**
     * Generate SSO token untuk redirect ke app lain
     */
    public function generateToken(User $user, string $app): ?SsoToken
    {
        $config = $this->getAppConfig($app);

        if (!$config || !($config['enabled'] ?? false)) {
            return null;
        }

        return SsoToken::create([
            'user_id' => $user->id,
            'token' => Str::random(self::TOKEN_LENGTH),
            'app' => $app,
            'callback_url' => $config['callback_url'],
            'expires_at' => now()->addMinutes($this->getTokenExpiry()),
        ]);
    }

    /**
     * Validate token dan return user data jika valid
     */
    public function validateToken(string $token): ?array
    {
        $ssoToken = SsoToken::byToken($token)->valid()->first();

        if (!$ssoToken || !$ssoToken->user->isActive()) {
            return null;
        }

        $ssoToken->markUsed();

        return $this->formatUserData($ssoToken->user);
    }

    /**
     * Get redirect URL dengan token
     */
    public function getRedirectUrl(SsoToken $token): string
    {
        return "{$token->callback_url}?token={$token->token}";
    }

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
     * Get token expiry dari config
     */
    private function getTokenExpiry(): int
    {
        return (int) config('sso.token_expiry_minutes', 5);
    }

    /**
     * Format user data untuk response
     */
    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'firebase_uid' => $user->firebase_uid,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ];
    }
}
