<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\FirebaseLoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller untuk handle Firebase Authentication
 * Thin Controller: Hanya routing, business logic di AuthService
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Login dengan Firebase ID Token
     */
    public function login(FirebaseLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->loginWithFirebase($request->id_token);

            if (!$result['success']) {
                return response()->json([
                    'message' => $result['message'],
                    'status' => $result['status'],
                ], 403);
            }

            $request->session()->regenerate();

            return response()->json([
                'message' => $result['message'],
                'user' => $result['user'],
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Register user baru
     */
    public function register(FirebaseLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->registerWithFirebase($request->id_token);

            if (!$result['success']) {
                return response()->json([
                    'message' => $result['message'],
                    'status' => $result['status'],
                ], 409);
            }

            return response()->json([
                'message' => $result['message'],
                'user' => $result['user'],
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
     * Logout
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Logout berhasil',
            'status' => 'success',
        ]);
    }

    /**
     * Get current user
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak terautentikasi',
                'status' => 'unauthenticated',
            ], 401);
        }

        return response()->json([
            'user' => $user,
            'status' => 'success',
        ]);
    }
}
