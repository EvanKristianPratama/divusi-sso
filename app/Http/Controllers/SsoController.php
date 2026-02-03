<?php

namespace App\Http\Controllers;

use App\Services\SsoService;
use Illuminate\Http\RedirectResponse;

/**
 * Controller untuk Portal redirect
 * Redirect user ke halaman login modul yang dipilih
 */
class SsoController extends Controller
{
    public function __construct(
        private SsoService $ssoService
    ) {}

    /**
     * Redirect ke halaman login aplikasi
     */
    public function redirect(string $app): RedirectResponse
    {
        $url = $this->ssoService->getAppUrl($app);

        if (!$url) {
            return back()->with('error', 'Aplikasi tidak tersedia');
        }

        return redirect()->away($url);
    }
}
