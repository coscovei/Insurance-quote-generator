<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LifeIsHardService
{
    protected string $baseUrl = 'https://rca-qa.api.lifeishard.ro';

    protected function http(?string $token = null)
    {
        $http = Http::acceptJson()
            ->connectTimeout(8)
            ->timeout(25)
            ->retry(1, 500, throw: false);

        if ($token) {
            $http = $http->withHeaders([
                'Token' => $token,
                'Accept' => 'application/json',
            ]);
        }

        if (app()->environment('local')) {
            $http = $http->withOptions(['verify' => false]);
        }

        return $http;
    }

    public function getAuthToken(): ?string
    {
        $cachedToken = Cache::get('lih_auth_token');

        if ($cachedToken) {
            Log::info('LIH Auth Token loaded from cache');
            return $cachedToken;
        }

        try {
            $response = $this->http()->post($this->baseUrl . '/auth', [
                'account' => 'test',
                'password' => 'test',
            ]);

            Log::info('LIH Auth Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $token = data_get($data, 'data.token');
                $expiresAt = data_get($data, 'data.expires_at');

                if ($token) {
                    $ttl = $expiresAt
                        ? Carbon::parse($expiresAt)->subMinutes(5)
                        : now()->addHours(10);

                    Cache::put('lih_auth_token', $token, $ttl);

                    Log::info('LIH Auth Token cached', [
                        'expires_at' => $expiresAt,
                    ]);

                    return $token;
                }
            }

            Log::error('LIH Auth Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('LIH Auth Exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function clearCachedToken(): void
    {
        Cache::forget('lih_auth_token');
    }

    public function requestOffer(array $payload, string $token)
    {
        if (isset($payload['product']['policyholder']['taxId'])) {
            $cnp = preg_replace('/[^0-9]/', '', $payload['product']['policyholder']['taxId']);
            $payload['product']['policyholder']['taxId'] = (string) $cnp;
        }

        if (isset($payload['product']['vehicle']['owner']['taxId'])) {
            $cnp = preg_replace('/[^0-9]/', '', $payload['product']['vehicle']['owner']['taxId']);
            $payload['product']['vehicle']['owner']['taxId'] = (string) $cnp;
        }

        Log::info('LIH Offer Payload', ['payload' => $payload]);

        try {
            $response = $this->http($token)->post($this->baseUrl . '/offer', $payload);

            Log::info('LIH Offer Response Status', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('LIH Offer Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('LIH Offer Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
