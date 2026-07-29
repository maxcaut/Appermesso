<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class SupabaseAuthService
{
    /**
     * @return array<string, mixed>
     */
    public function signUp(string $email, string $password): array
    {
        $payload = $this->request()
            ->post($this->url('/auth/v1/signup'), compact('email', 'password'))
            ->throw()
            ->json();

        if (! isset($payload['access_token'])) {
            return $this->signIn($email, $password);
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function signIn(string $email, string $password): array
    {
        return $this->request()
            ->post($this->url('/auth/v1/token?grant_type=password'), compact('email', 'password'))
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function refresh(string $refreshToken): array
    {
        return $this->request()
            ->post($this->url('/auth/v1/token?grant_type=refresh_token'), [
                'refresh_token' => $refreshToken,
            ])
            ->throw()
            ->json();
    }

    public function sendPasswordRecovery(string $email, string $redirectTo): void
    {
        $this->request()
            ->post($this->url('/auth/v1/recover'), [
                'email' => $email,
                'redirect_to' => $redirectTo,
            ])
            ->throw();
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyRecoveryToken(string $tokenHash): array
    {
        return $this->request()
            ->post($this->url('/auth/v1/verify'), [
                'type' => 'recovery',
                'token_hash' => $tokenHash,
            ])
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function user(string $accessToken): array
    {
        return $this->request($accessToken)
            ->get($this->url('/auth/v1/user'))
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function updatePassword(string $accessToken, string $password): array
    {
        return $this->request($accessToken)
            ->put($this->url('/auth/v1/user'), ['password' => $password])
            ->throw()
            ->json();
    }

    public function logout(string $accessToken): void
    {
        try {
            $this->request($accessToken)->post($this->url('/auth/v1/logout'))->throw();
        } catch (Throwable) {
            // The local session must still be destroyed when remote revocation fails.
        }
    }

    private function request(?string $accessToken = null): PendingRequest
    {
        $key = (string) config('services.supabase.anon_key');

        if ($key === '') {
            throw new RuntimeException('Supabase authentication is not configured.');
        }

        $request = Http::acceptJson()
            ->asJson()
            ->withHeader('apikey', $key)
            ->timeout(8);

        return $accessToken === null
            ? $request
            : $request->withToken($accessToken);
    }

    private function url(string $path): string
    {
        $baseUrl = rtrim((string) config('services.supabase.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Supabase authentication is not configured.');
        }

        return $baseUrl.$path;
    }
}
