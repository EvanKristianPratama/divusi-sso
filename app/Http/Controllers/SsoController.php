<?php

namespace App\Http\Controllers;

use App\Services\SsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk handle SSO operations
 * Thin Controller: Hanya routing, business logic di SsoService
 */
class SsoController extends Controller
{
    public function __construct(
        private SsoService $ssoService
    ) {}

    /**
     * Redirect ke app lain dengan SSO token
     */
    public function redirect(string $app): RedirectResponse
    {
        $token = $this->ssoService->generateToken(Auth::user(), $app);

        if (!$token) {
            return back()->with('error', 'Aplikasi tidak tersedia');
        }

        return redirect()->away($this->ssoService->getRedirectUrl($token));
    }

    /**
     * Generate token via API
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate(['app' => 'required|string|max:50']);

        $token = $this->ssoService->generateToken(Auth::user(), $request->app);

        if (!$token) {
            return response()->json([
                'message' => 'Aplikasi tidak tersedia',
                'status' => 'error',
            ], 404);
        }

        return response()->json([
            'token' => $token->token,
            'redirect_url' => $this->ssoService->getRedirectUrl($token),
            'expires_at' => $token->expires_at->toIso8601String(),
            'status' => 'success',
        ]);
    }

    /**
     * Validate token dari app lain
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string|size:64']);

        $userData = $this->ssoService->validateToken($request->token);

        if (!$userData) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah expired',
                'status' => 'error',
            ], 401);
        }

        return response()->json([
            'user' => $userData,
            'status' => 'success',
        ]);
    }
}
