<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Services\SupabaseAuthService;
use App\Services\SupabaseSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PasswordController extends Controller
{
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request, SupabaseAuthService $auth): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
        ]);

        try {
            $auth->sendPasswordRecovery(
                (string) $validated['email'],
                route('password.reset'),
            );
        } catch (Throwable) {
            // Keep the response indistinguishable to avoid account enumeration.
        }

        return back()->with(
            'status',
            'Se esiste un account associato, riceverai le istruzioni per reimpostare la password.',
        );
    }

    public function reset(
        Request $request,
        SupabaseAuthService $auth,
        SupabaseSession $session,
    ): View|RedirectResponse {
        $tokenHash = $request->query('token_hash');
        $type = $request->query('type');

        if (! is_string($tokenHash) || $tokenHash === '' || $type !== 'recovery') {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Link di recupero non valido o scaduto.']);
        }

        try {
            $session->establish($auth->verifyRecoveryToken($tokenHash), passwordRecovery: true);
        } catch (Throwable) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Link di recupero non valido o scaduto.']);
        }

        return view('auth.reset-password');
    }

    public function update(
        UpdatePasswordRequest $request,
        SupabaseAuthService $auth,
        SupabaseSession $session,
    ): RedirectResponse {
        $accessToken = $session->accessToken();

        if ($accessToken === null || ! $session->isPasswordRecovery()) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'La sessione di recupero è scaduta.']);
        }

        try {
            $auth->updatePassword(
                $accessToken,
                (string) $request->validated('password'),
            );
        } catch (Throwable) {
            return back()->withErrors([
                'password' => 'Impossibile aggiornare la password. Riprova.',
            ]);
        }

        $session->completePasswordRecovery();

        return redirect()->route('profile.show')
            ->with('status', 'Password aggiornata.');
    }
}
