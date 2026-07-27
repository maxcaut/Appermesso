<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppUsageTracker
{
    /**
     * @param  array<int, string>  $usageTypes
     */
    public function recordPdfGenerated(string $firstName, string $lastName, array $usageTypes): void
    {
        $url = rtrim((string) config('services.supabase.url'), '/');
        $secretKey = (string) config('services.supabase.secret_key');

        if ($url === '' || $secretKey === '') {
            return;
        }

        try {
            Http::withHeaders([
                'apikey' => $secretKey,
                'Authorization' => "Bearer {$secretKey}",
                'Prefer' => 'return=minimal',
            ])
                ->acceptJson()
                ->timeout(3)
                ->post("{$url}/rest/v1/app_usage", [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'usage_types' => $usageTypes,
                ])
                ->throw();
        } catch (Throwable $exception) {
            Log::warning('Unable to record app usage in Supabase.', [
                'exception' => $exception::class,
            ]);
        }
    }
}
