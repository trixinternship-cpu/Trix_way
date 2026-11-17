<?php

namespace App\Services;

use App\Models\CompanyLocation;
use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\TripLocation;
use App\Models\TripStop;
use App\Models\TripTrackingSession;
use App\Models\Vehicle;
use App\Models\VehicleStatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TripAutoCompletionService
{
    /**
     * Selesaikan otomatis hanya jika:
     * - Trip masih aktif.
     * - Semua tujuan sudah berstatus departed.
     * - GPS terbaru dan seluruh sampel selama 3 menit berada di geofence.
     * - GPS terbaru diterima maksimal 45 detik yang lalu.
     */
    public function completeIfReturnedToCompany(Trip $trip, TripLocation $latestLocation): bool
    {
        if ($trip->status !== 'active') {
            return false;
        }

        $company = CompanyLocation::query()
            ->where('is_active', true)
            ->first();

        if (!$company) {
            return false;
        }

        $now = now();
        $dwellStartedAt = $now->copy()->subMinutes(3);

        // Mencegah penyelesaian otomatis bila masih ada tujuan belum selesai.
        if (
            TripStop::where('trip_id', $trip->id)
                ->where('status', '!=', 'departed')
                ->exists()
        ) {
            return false;
        }

        // Ambil satu sampel sebelum batas 3 menit untuk membuktikan
        // bahwa Driver sudah berada di area sejak awal periode.
        $sampleAtStart = TripLocation::query()
            ->where('trip_id', $trip->id)
            ->where('received_at', '<=', $dwellStartedAt)
            ->where('received_at', '>=', $dwellStartedAt->copy()->subSeconds(45))
            ->latest('received_at')
            ->first(['latitude', 'longitude', 'accuracy', 'received_at']);

        $samples = TripLocation::query()
            ->where('trip_id', $trip->id)
            ->where('received_at', '>', $dwellStartedAt)
            ->orderBy('received_at')
            ->get(['latitude', 'longitude', 'accuracy', 'received_at']);

        // Perlu sampel di awal periode, sampel setelahnya, dan GPS terbaru.
        if (!$sampleAtStart || $samples->isEmpty()) {
            return false;
        }

        $lastSample = $samples->last();

        if (Carbon::parse($lastSample->received_at)->lt($now->copy()->subSeconds(45))) {
            return false;
        }

        // Sampel awal dan seluruh titik GPS selama 3 menit harus berada di radius kantor.
        $samples->prepend($sampleAtStart);

        foreach ($samples as $sample) {
            if (!$this->isInsideGeofence(
                (float) $sample->latitude,
                (float) $sample->longitude,
                (float) $company->latitude,
                (float) $company->longitude,
                (int) $company->geofence_radius_meters
            )) {
                return false;
            }
        }

        return DB::transaction(function () use ($trip, $latestLocation, $company) {
            $lockedTrip = Trip::query()
                ->whereKey($trip->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Cegah dua request GPS menyelesaikan trip yang sama.
            if ($lockedTrip->status !== 'active') {
                return false;
            }

            // Periksa ulang tujuan di dalam transaksi.
            $incompleteStops = TripStop::where('trip_id', $lockedTrip->id)
                ->where('status', '!=', 'departed')
                ->exists();

            if ($incompleteStops) {
                return false;
            }

            $now = now();

            $lockedTrip->update([
                'status' => 'completed',
                'end_time' => $now,
                'end_latitude' => $latestLocation->latitude,
                'end_longitude' => $latestLocation->longitude,
            ]);

            $trackingSession = TripTrackingSession::query()
                ->where('trip_id', $lockedTrip->id)
                ->lockForUpdate()
                ->first();

            if ($trackingSession) {
                $trackingSession->update([
                    'status' => 'stopped',
                    'stopped_at' => $now,
                ]);
            }

            $vehicle = Vehicle::query()
                ->whereKey($lockedTrip->vehicle_id)
                ->lockForUpdate()
                ->first();

            if ($vehicle) {
                $oldStatus = $vehicle->status;

                $vehicle->update(['status' => 'available']);

                if ($oldStatus !== 'available') {
                    VehicleStatusHistory::create([
                        'vehicle_id' => $vehicle->id,
                        'changed_by' => $lockedTrip->driver_id,
                        'old_status' => $oldStatus,
                        'new_status' => 'available',
                        'reason' => 'Perjalanan otomatis selesai setelah Driver kembali ke '
                            . $company->company_name . ': ' . $lockedTrip->trip_code,
                    ]);
                }
            }

            TripEvent::create([
                'trip_id' => $lockedTrip->id,
                'actor_id' => $lockedTrip->driver_id,
                'event_type' => 'trip_auto_completed',
                'description' => 'Perjalanan otomatis diselesaikan karena GPS Driver terdeteksi berada di area '
                    . $company->company_name . ' selama minimal 3 menit.',
                'metadata' => [
                    'completion_method' => 'geofence_auto',
                    'company_name' => $company->company_name,
                    'geofence_radius_meters' => (int) $company->geofence_radius_meters,
                    'latitude' => (float) $latestLocation->latitude,
                    'longitude' => (float) $latestLocation->longitude,
                    'dwell_minutes' => 3,
                ],
                'event_time' => $now,
            ]);

            TripNotificationService::notifyParticipants(
                $lockedTrip,
                'Driver kembali ke ' . $company->company_name,
                ($lockedTrip->driver?->name ?? 'Driver')
                    . ' telah kembali ke ' . $company->company_name
                    . ' dan perjalanan ' . $lockedTrip->trip_code
                    . ' otomatis diselesaikan berdasarkan lokasi GPS.',
                'trip_auto_completed'
            );

            return true;
        });
    }

    private function isInsideGeofence(
        float $latitude,
        float $longitude,
        float $centerLatitude,
        float $centerLongitude,
        int $radiusMeters
    ): bool {
        $earthRadius = 6371000;

        $lat1 = deg2rad($latitude);
        $lat2 = deg2rad($centerLatitude);
        $deltaLat = deg2rad($centerLatitude - $latitude);
        $deltaLon = deg2rad($centerLongitude - $longitude);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;

        $distance = 2 * $earthRadius * asin(min(1, sqrt($a)));

        return $distance <= $radiusMeters;
    }
}
