<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Services\SupabaseProfileService;
use App\Services\SupabaseSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function show(
        SupabaseSession $session,
        SupabaseProfileService $profiles,
    ): View {
        $auth = $session->current();
        $profile = [];
        $profileError = null;

        try {
            $profile = $profiles->find(
                (string) $auth['user']['id'],
                (string) $auth['access_token'],
            ) ?? [];
        } catch (Throwable) {
            $profileError = 'Impossibile caricare il profilo. Riprova più tardi.';
        }

        return view('profile.show', [
            'currentUser' => $auth['user'],
            'profile' => $profile,
            'profileError' => $profileError,
            'requiresPrivacyConsent' => data_get($profile, 'privacy_consent_at') === null,
        ]);
    }

    public function update(
        ProfileRequest $request,
        SupabaseSession $session,
        SupabaseProfileService $profiles,
    ): RedirectResponse {
        $auth = $session->current();

        try {
            $profiles->upsert(
                (string) $auth['user']['id'],
                $request->validated(),
                (string) $auth['access_token'],
            );
        } catch (Throwable) {
            return back()
                ->withInput()
                ->withErrors(['profile' => 'Impossibile salvare il profilo. Riprova.']);
        }

        return redirect()->route('profile.show')
            ->with('status', 'Profilo aggiornato.');
    }
}
