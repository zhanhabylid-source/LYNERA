<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoogleCalendarAuthController extends Controller
{
    public function __construct(
        private readonly GoogleCalendarService $googleCalendarService
    ) {
    }

    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::uuid()->toString();
        $request->session()->put('google_oauth_state', $state);
        $authUrl = $this->googleCalendarService->getAuthUrl($state);

        if ($authUrl === null) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Kredensial Google Calendar belum dikonfigurasi.');
        }

        return redirect()->away($authUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('google_oauth_state', '');
        $receivedState = (string) $request->query('state', '');
        if ($expectedState === '' || $expectedState !== $receivedState) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'State OAuth Google tidak valid. Silakan coba hubungkan akun kembali.');
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Kode otorisasi Google tidak ditemukan.');
        }

        $token = $this->googleCalendarService->fetchAccessTokenWithCode($code);
        if ($token === null) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Gagal melakukan autentikasi Google Calendar.');
        }

        $this->googleCalendarService->storeTokenForUser((int) $request->user()->id, $token);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Google Calendar berhasil terhubung.');
    }
}
