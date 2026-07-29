<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SupabaseProfileService
{
    public const FIELDS = [
        'nome',
        'cognome',
        'matricola',
        'centro_costo',
        'livello',
        'qualifica',
        'ente',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $userId, string $accessToken): ?array
    {
        $profiles = $this->request($accessToken)
            ->get($this->url('/rest/v1/profiles'), [
                'id' => "eq.{$userId}",
                'select' => implode(',', self::FIELDS),
                'limit' => 1,
            ])
            ->throw()
            ->json();

        return is_array($profiles) && isset($profiles[0]) ? $profiles[0] : null;
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    public function upsert(string $userId, array $profile, string $accessToken): array
    {
        $response = $this->request($accessToken)
            ->withHeader('Prefer', 'resolution=merge-duplicates,return=representation')
            ->post($this->url('/rest/v1/profiles?on_conflict=id'), [
                'id' => $userId,
                ...array_intersect_key($profile, array_flip(self::FIELDS)),
            ])
            ->throw()
            ->json();

        return is_array($response) && isset($response[0]) ? $response[0] : [];
    }

    private function request(string $accessToken): PendingRequest
    {
        $key = (string) config('services.supabase.anon_key');

        if ($key === '') {
            throw new RuntimeException('Supabase profiles are not configured.');
        }

        return Http::acceptJson()
            ->asJson()
            ->withHeader('apikey', $key)
            ->withToken($accessToken)
            ->timeout(8);
    }

    private function url(string $path): string
    {
        $baseUrl = rtrim((string) config('services.supabase.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Supabase profiles are not configured.');
        }

        return $baseUrl.$path;
    }
}
