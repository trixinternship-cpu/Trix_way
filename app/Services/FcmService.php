<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    public function sendToToken(
        string $deviceToken,
        string $title,
        string $body,
        array $data = []
    ): bool {
        try {
            $credentialsPath = base_path(
                config('services.firebase.credentials')
            );

            if (!file_exists($credentialsPath)) {
                Log::error('Firebase service account file not found.');
                return false;
            }

            $serviceAccount = json_decode(
                file_get_contents($credentialsPath),
                true
            );

            if (!$serviceAccount) {
                Log::error('Firebase service account JSON is invalid.');
                return false;
            }

            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                $serviceAccount
            );

            $token = $credentials->fetchAuthToken();
            $accessToken = $token['access_token'] ?? null;

            if (!$accessToken) {
                Log::error('Unable to obtain Firebase access token.');
                return false;
            }

            $projectId = config('services.firebase.project_id');

            $response = Http::withToken($accessToken)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                    [
                        'message' => [
                            'token' => $deviceToken,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => array_map(
                                fn ($value) => (string) $value,
                                $data
                            ),
                            'android' => [
                                'priority' => 'HIGH',
                            ],
                        ],
                    ]
                );

            if (!$response->successful()) {
                Log::error('FCM notification failed.', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                return false;
            }

            return true;

        } catch (\Throwable $e) {
            Log::error('FCM service error.', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
