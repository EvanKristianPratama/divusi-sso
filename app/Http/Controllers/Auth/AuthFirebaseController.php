<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\FirebaseLoginRequest;
use App\Models\User;
use App\Services\Firebase\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthFirebaseController extends Controller
{
    public function __construct(protected FirebaseService $firebaseService)
    {
    }

    /**
     * Register user baru
     */
    public function register(FirebaseLoginRequest $request): JsonResponse
    {
        try {
            // Verify token
            $tokenData = $this->firebaseService->verifyToken($request->id_token);

            // Check if user already exists
            if (User::where('firebase_uid', $tokenData['firebase_uid'])->exists()) {
                return response()->json([
                    'message' => 'User sudah terdaftar',
                    'status' => 'exists',
                ], 409);
            }

            // Create new user
            $user = User::create([
                'firebase_uid' => $tokenData['firebase_uid'],
                'email' => $tokenData['email'],
                'name' => $tokenData['name'] ?? $tokenData['email'],
                'role' => 'user',
                'status' => 'active',
            ]);

            // Auto login
            Auth::login($user);

            return response()->json([
                'message' => 'Registrasi berhasil',
                'user' => $user->only(['id', 'name', 'email', 'role', 'status']),
                'status' => 'success',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Login dengan Firebase ID Token
     *
     * Flow:
     * 1. Frontend kirim Firebase ID Token
     * 2. Backend verify token
     * 3. Find or create user di mst_users
     * 4. Check status & deleted_at
     * 5. Login via Laravel Auth
     */
    public function login(FirebaseLoginRequest $request): JsonResponse
    {
        try {
            // Verify token
            $tokenData = $this->firebaseService->verifyToken($request->id_token);

            // Find or create user
            $user = $this->findOrCreateUser($tokenData);

            // Check user status
            if (!$user->isActive()) {
                return response()->json([
                    'message' => 'User tidak aktif atau sudah dihapus',
                    'status' => 'blocked',
                ], 403);
            }

            // Login with remember
            Auth::login($user, true);
            
            // Regenerate session
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user->only(['id', 'name', 'email', 'role', 'status']),
                'status' => 'success',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout berhasil',
            'status' => 'success',
        ], 200);
    }

    /**
     * Get current authenticated user
     */
    public function me(): JsonResponse
    {
        return Auth::check()
            ? response()->json([
                'user' => Auth::user()->only(['id', 'name', 'email', 'role', 'status']),
                'status' => 'success',
            ], 200)
            : response()->json([
                'message' => 'User tidak terautentikasi',
                'status' => 'unauthenticated',
            ], 401);
    }

    /**
     * Find or create user
     */
    private function findOrCreateUser(array $tokenData): User
    {
        return User::firstOrCreate(
            ['firebase_uid' => $tokenData['firebase_uid']],
            [
                'email' => $tokenData['email'],
                'name' => $tokenData['name'] ?? $tokenData['email'],
                'role' => 'user',
                'status' => 'active',
            ]
        );
    }
}
