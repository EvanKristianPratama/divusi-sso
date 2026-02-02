<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoController extends Controller
{
    /**
     * Validate SSO Token dari apps lain
     * 
     * Apps lain kirim token ke sini untuk verify user
     */
    public function validateToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Find user by token
        $user = User::where('sso_token', $request->token)
            ->where('sso_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token',
            ], 401);
        }

        if (!$user->isActive()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not active',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'firebase_uid' => $user->firebase_uid,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Generate SSO Token untuk redirect ke apps lain
     */
    public function generateToken(Request $request): JsonResponse
    {
        $request->validate([
            'app' => 'required|string|in:cobit,pmo,hr,finance,inventory,report',
            'callback_url' => 'required|url',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        
        // Save token (expires in 5 minutes - one-time use)
        $user->update([
            'sso_token' => $token,
            'sso_token_expires_at' => now()->addMinutes(5),
        ]);

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'redirect_url' => $request->callback_url . '?sso_token=' . $token,
            'expires_in' => 300, // 5 minutes
        ]);
    }

    /**
     * Redirect ke app lain dengan SSO token
     */
    public function redirectToApp(Request $request, string $app)
    {
        $apps = [
            'cobit' => env('APP_COBIT_URL', 'http://cobit.localhost:8001'),
            'pmo' => env('APP_PMO_URL', 'http://pmo.localhost:8002'),
            'hr' => env('APP_HR_URL', 'http://hr.localhost:8003'),
            'finance' => env('APP_FINANCE_URL', 'http://finance.localhost:8004'),
            'inventory' => env('APP_INVENTORY_URL', 'http://inventory.localhost:8005'),
            'report' => env('APP_REPORT_URL', 'http://report.localhost:8006'),
        ];

        if (!isset($apps[$app])) {
            abort(404, 'App not found');
        }

        $user = Auth::user();

        // Generate one-time token
        $token = bin2hex(random_bytes(32));
        $user->update([
            'sso_token' => $token,
            'sso_token_expires_at' => now()->addMinutes(5),
        ]);

        // Redirect to app with token
        return redirect($apps[$app] . '/sso/callback?token=' . $token);
    }
}
