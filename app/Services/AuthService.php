<?php

namespace App\Services;

use App\Models\User;
use App\Services\Firebase\FirebaseService;
use Illuminate\Support\Facades\Auth;

/**
 * Service untuk handle Authentication operations
 * Single Responsibility: Login, register, dan session management
 */
class AuthService
{
    public function __construct(
        private FirebaseService $firebase
    ) {}

    /**
     * Login dengan Firebase ID Token
     * Find or create user, lalu login ke Laravel
     */
    public function loginWithFirebase(string $idToken): array
    {
        $tokenData = $this->firebase->verifyToken($idToken);
        $user = $this->findOrCreateUser($tokenData);

        if (!$user->isActive()) {
            return [
                'success' => false,
                'message' => 'User tidak aktif atau sudah dihapus',
                'status' => 'blocked',
            ];
        }

        Auth::login($user, true);

        return [
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $this->formatUserData($user),
        ];
    }

    /**
     * Register user baru dengan Firebase
     */
    public function registerWithFirebase(string $idToken): array
    {
        $tokenData = $this->firebase->verifyToken($idToken);

        if (User::byFirebaseUid($tokenData['firebase_uid'])->exists()) {
            return [
                'success' => false,
                'message' => 'User sudah terdaftar',
                'status' => 'exists',
            ];
        }

        $user = User::create([
            'firebase_uid' => $tokenData['firebase_uid'],
            'email' => $tokenData['email'],
            'name' => $tokenData['name'],
            'role' => 'user',
            'status' => 'active',
        ]);

        Auth::login($user);

        return [
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user' => $this->formatUserData($user),
        ];
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        Auth::logout();

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?array
    {
        $user = Auth::user();
        return $user ? $this->formatUserData($user) : null;
    }

    /**
     * Find or create user dari Firebase data
     */
    private function findOrCreateUser(array $tokenData): User
    {
        return User::firstOrCreate(
            ['firebase_uid' => $tokenData['firebase_uid']],
            [
                'email' => $tokenData['email'],
                'name' => $tokenData['name'],
                'role' => 'user',
                'status' => 'active',
            ]
        );
    }

    /**
     * Format user data untuk response
     */
    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ];
    }
}
