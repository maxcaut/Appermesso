<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\SupabaseAuthService;
use App\Services\SupabaseProfileService;
use App\Services\SupabaseSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(SupabaseSession $session): View|RedirectResponse
    {
        return $session->current() === null
            ? view('auth.login')
            : redirect()->route('profile.show');
    }

    public function login(
        LoginRequest $request,
        SupabaseAuthService $auth,
        SupabaseProfileService $profiles,
        SupabaseSession $session,
    ): RedirectResponse {
        try {
            $session->establish($auth->signIn(
                (string) $request->validated('email'),
                (string) $request->validated('password'),
            ));
        } catch (Throwable) {
            return back()
                ->withInput($request->safe()->only('email'))
                ->withErrors(['email' => 'Credenziali non valide o servizio temporaneamente non disponibile.']);
        }

        $current = $session->current();

        try {
            $profile = $profiles->find(
                (string) $current['user']['id'],
                (string) $current['access_token'],
            );
        } catch (Throwable) {
            $profile = null;
        }

        $hasProfileData = filled(data_get($profile, 'nome'))
            && filled(data_get($profile, 'cognome'));

        return redirect()->route($hasProfileData ? 'home' : 'profile.show');
    }

    public function showRegister(SupabaseSession $session): View|RedirectResponse
    {
        return $session->current() === null
            ? view('auth.register')
            : redirect()->route('profile.show');
    }

    public function register(
        RegisterRequest $request,
        SupabaseAuthService $auth,
        SupabaseSession $session,
    ): RedirectResponse {
        try {
            $session->establish($auth->signUp(
                (string) $request->validated('email'),
                (string) $request->validated('password'),
            ));
        } catch (Throwable) {
            return back()
                ->withInput($request->safe()->only('email'))
                ->withErrors(['email' => 'Registrazione non riuscita. Verifica i dati o riprova più tardi.']);
        }

        return redirect()->route('profile.show')
            ->with('status', 'Registrazione completata.');
    }

    public function logout(
        Request $request,
        SupabaseAuthService $auth,
        SupabaseSession $session,
    ): RedirectResponse {
        $accessToken = $session->accessToken();

        if ($accessToken !== null) {
            $auth->logout($accessToken);
        }

        $session->forget();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
