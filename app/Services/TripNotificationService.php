<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Trip;
use App\Models\TripNotification;
use App\Models\User;

class TripNotificationService
{
    public static function notifyParticipants(
        Trip $trip,
        string $title,
        string $message,
        string $category = 'trip'
    ): void {
        $recipientIds = User::query()
            ->where('role', 'hr')
            ->where('is_active', true)
            ->pluck('id')
            ->push($trip->driver_id)
            ->filter()
            ->unique()
            ->values();

        // Tetap simpan notifikasi dalam database.
        foreach ($recipientIds as $userId) {
            TripNotification::create([
                'trip_id' => $trip->id,
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'category' => $category,
            ]);
        }

        // Kirim push notification ke perangkat driver.
        if ($trip->driver_id) {
            $deviceTokens = DeviceToken::where(
                'user_id',
                $trip->driver_id
            )->pluck('token');

            $fcmService = app(FcmService::class);

            foreach ($deviceTokens as $deviceToken) {
                $fcmService->sendToToken(
                    $deviceToken,
                    $title,
                    $message,
                    [
                        'type' => $category,
                        'trip_id' => $trip->id,
                        'trip_code' => $trip->trip_code,
                    ]
                );
            }
        }
    }
}
