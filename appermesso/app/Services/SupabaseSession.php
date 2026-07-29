<?php

namespace App\Services;

use Illuminate\Contracts\Session\Session;
use Throwable;

class SupabaseSession
{
    public const SESSION_KEY = 'supabase_auth';

    public function __construct(
        private readonly Session $session,
        private readonly SupabaseAuthService $auth,
    ) {}

    /**
     * @param  array<string, mixed>  $authPayload
     */
    public function establish(array $authPayload, bool $passwordRecovery = false): void
    {
        if (! isset($authPayload['access_token'], $authPayload['refresh_token'])) {
            throw new \RuntimeException('Supabase did not return a valid session.');
        }

        $user = $authPayload['user'] ?? $this->auth->user((string) $authPayload['access_token']);
        $expiresAt = $authPayload['expires_at'] ?? (time() + (int) ($authPayload['expires_in'] ?? 3600));

        if (! is_array($user) || ! isset($user['id'])) {
            throw new \RuntimeException('Supabase did not return a valid user.');
        }

        $this->session->regenerate();
        $this->session->put(self::SESSION_KEY, [
            'access_token' => (string) $authPayload['access_token'],
            'refresh_token' => (string) $authPayload['refresh_token'],
            'expires_at' => (int) $expiresAt,
            'user' => $this->publicUser((array) $user),
            'password_recovery' => $passwordRecovery,
        ]);
        $this->session->put('supabase_user', $this->publicUser((array) $user));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function current(bool $refreshIfNeeded = true): ?array
    {
        $auth = $this->session->get(self::SESSION_KEY);

        if (! is_array($auth) || ! isset($auth['access_token'], $auth['refresh_token'])) {
            return null;
        }

        if ($refreshIfNeeded && (int) ($auth['expires_at'] ?? 0) <= time() + 60) {
            try {
                $this->establish($this->auth->refresh((string) $auth['refresh_token']));
                $auth = $this->session->get(self::SESSION_KEY);
            } catch (Throwable) {
                $this->forget();

                return null;
            }
        }

        return is_array($auth) ? $auth : null;
    }

    public function accessToken(): ?string
    {
        return $this->current()['access_token'] ?? null;
    }

    /**
     * @return array{id?: string, email?: string}|null
     */
    public function user(): ?array
    {
        return $this->current()['user'] ?? null;
    }

    public function forget(): void
    {
        $this->session->forget([self::SESSION_KEY, 'supabase_user']);
    }

    public function isPasswordRecovery(): bool
    {
        return (bool) ($this->current()['password_recovery'] ?? false);
    }

    public function completePasswordRecovery(): void
    {
        $auth = $this->current(false);

        if ($auth !== null) {
            $auth['password_recovery'] = false;
            $this->session->put(self::SESSION_KEY, $auth);
        }
    }

    /**
     * @param  array<string, mixed>  $user
     * @return array{id?: string, email?: string}
     */
    private function publicUser(array $user): array
    {
        return array_filter([
            'id' => isset($user['id']) ? (string) $user['id'] : null,
            'email' => isset($user['email']) ? (string) $user['email'] : null,
        ], fn ($value) => $value !== null);
    }
}
