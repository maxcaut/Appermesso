<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Services\SupabaseAuthService;
use App\Services\SupabaseSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
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

    public function session(Request $request, SupabaseSession $session): JsonResponse
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
            'refresh_token' => ['required', 'string'],
            'expires_at' => ['nullable', 'integer'],
            'expires_in' => ['nullable', 'integer'],
            'type' => ['required', 'in:recovery'],
        ]);

        try {
            $session->establish([
                'access_token' => $validated['access_token'],
                'refresh_token' => $validated['refresh_token'],
                'expires_at' => $validated['expires_at'] ?? null,
                'expires_in' => $validated['expires_in'] ?? 3600,
            ], passwordRecovery: true);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Link di recupero non valido o scaduto.',
            ], 422);
        }

        return response()->json([
            'redirect' => route('password.reset'),
        ]);
    }

    public function reset(
        Request $request,
        SupabaseAuthService $auth,
        SupabaseSession $session,
    ): View|RedirectResponse {
        if ($session->isPasswordRecovery()) {
            return view('auth.reset-password');
        }

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
