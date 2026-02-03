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
     * Cek approval status sebelum login
     */
    public function loginWithFirebase(string $idToken, string $provider = 'google'): array
    {
        $tokenData = $this->firebase->verifyToken($idToken);
        $user = $this->findOrCreateUser($tokenData, $provider);

        // Cek status aktif
        if (!$user->isActive()) {
            return [
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
                'status' => 'blocked',
            ];
        }

        // Cek approval status (admin auto-approved)
        if (!$user->isAdmin() && !$user->isApproved()) {
            if ($user->isPending()) {
                return [
                    'success' => false,
                    'message' => 'Akun Anda menunggu persetujuan administrator.',
                    'status' => 'pending',
                ];
            }

            if ($user->isRejected()) {
                return [
                    'success' => false,
                    'message' => 'Akun Anda ditolak. Hubungi administrator.',
                    'status' => 'rejected',
                ];
            }
        }

        Auth::login($user, true);

        return [
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $this->formatUserData($user),
            'is_admin' => $user->isAdmin(),
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
     * User baru = pending, kecuali admin pertama
     */
    private function findOrCreateUser(array $tokenData, string $provider): User
    {
        $user = User::byFirebaseUid($tokenData['firebase_uid'])->first();

        if ($user) {
            // Update data jika ada perubahan
            $user->update([
                'name' => $tokenData['name'],
                'avatar_url' => $tokenData['avatar_url'] ?? $user->avatar_url,
            ]);
            return $user;
        }

        // Cek apakah ada email yang pre-registered
        $preRegistered = User::where('email', $tokenData['email'])
            ->whereNull('firebase_uid')
            ->first();

        if ($preRegistered) {
            // Update user yang sudah pre-registered
            $preRegistered->update([
                'firebase_uid' => $tokenData['firebase_uid'],
                'provider' => $provider,
                'name' => $tokenData['name'],
                'avatar_url' => $tokenData['avatar_url'] ?? null,
            ]);
            return $preRegistered;
        }

        // User baru
        $isFirstUser = User::count() === 0;

        $user = User::create([
            'firebase_uid' => $tokenData['firebase_uid'],
            'provider' => $provider,
            'email' => $tokenData['email'],
            'name' => $tokenData['name'],
            'avatar_url' => $tokenData['avatar_url'] ?? null,
            'role' => $isFirstUser ? 'admin' : 'user',
            'status' => 'active',
            'approval_status' => $isFirstUser ? 'approved' : 'pending', // Admin pertama auto-approved
            'approved_at' => $isFirstUser ? now() : null,
        ]);

        // Assign role dengan Spatie Permission
        if ($isFirstUser) {
            $user->assignRole('admin');
        } else {
            $user->assignRole('user');
        }

        return $user;
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
            'avatar_url' => $user->avatar_url,
            'role' => $user->role,
            'status' => $user->status,
            'approval_status' => $user->approval_status,
            'is_admin' => $user->isAdmin(),
        ];
    }
}
