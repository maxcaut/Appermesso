<?php

namespace App\Http\Controllers;

use App\Services\SupabaseProfileService;
use App\Services\SupabaseSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class PrivacyConsentController extends Controller
{
    public function accept(
        Request $request,
        SupabaseSession $session,
        SupabaseProfileService $profiles,
    ): JsonResponse {
        $auth = $session->current();

        if ($auth === null) {
            $request->session()->put('privacy_consent_seen', true);

            return response()->json(['accepted' => true, 'stored' => false]);
        }

        try {
            $profiles->updatePrivacyConsent(
                (string) $auth['user']['id'],
                true,
                (string) $auth['access_token'],
            );
        } catch (Throwable) {
            return response()->json([
                'message' => 'Impossibile memorizzare il consenso. Riprova.',
            ], 503);
        }

        return response()->json(['accepted' => true, 'stored' => true]);
    }

    public function revoke(
        Request $request,
        SupabaseSession $session,
        SupabaseProfileService $profiles,
    ): RedirectResponse {
        $auth = $session->current();

        try {
            $profiles->updatePrivacyConsent(
                (string) $auth['user']['id'],
                false,
                (string) $auth['access_token'],
            );
        } catch (Throwable) {
            return back()->withErrors([
                'privacy_consent' => 'Impossibile revocare il consenso. Riprova.',
            ]);
        }

        $session->forget();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('privacy.refused')
            ->with('status', 'Consenso revocato.');
    }
}
