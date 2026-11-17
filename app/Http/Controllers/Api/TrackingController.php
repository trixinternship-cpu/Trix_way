<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\TripLocation;
use App\Models\TripStop;
use App\Models\CompanyLocation;
use App\Services\TripNotificationService;
use App\Services\TripAutoCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // PERBAIKAN: Tambahkan import Carbon

class TrackingController extends Controller
{
    /**
     * Menerima lokasi GPS dari aplikasi Driver.
     */
    public function storeLocation(Request $request, string $tripId)
    {
        $trip = Trip::with('trackingSession')->findOrFail($tripId);

        abort_unless(
            $request->user()->role === 'hr'
            || $trip->driver_id == $request->user()->id,
            403
        );

        if (
            $trip->status !== 'active'
            || !$trip->trackingSession
            || $trip->trackingSession->status !== 'active'
        ) {
            return response()->json([
                'message' => 'Sesi pelacakan tidak aktif.',
            ], 422);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'recorded_at' => ['required', 'date'],
        ]);

        // PERBAIKAN: Konversi format tanggal dari ISO 8601 (Flutter) ke format MySQL
        // Contoh: '2026-11-12T06:08:52.312Z' menjadi '2026-11-12 06:08:52'
        $recordedAt = Carbon::parse($data['recorded_at'])->format('Y-m-d H:i:s');

        return DB::transaction(function () use ($trip, $data, $recordedAt) {
            $receivedAt = now();

            $location = TripLocation::create([
                'trip_id' => $trip->id,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy' => $data['accuracy'] ?? null,
                'speed' => $data['speed'] ?? null,
                'heading' => $data['heading'] ?? null,
                'recorded_at' => $recordedAt, // PERBAIKAN: Gunakan variabel yang sudah diformat
                'received_at' => $receivedAt,
            ]);

            $trip->trackingSession()->update([
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy' => $data['accuracy'] ?? null,
                'speed' => $data['speed'] ?? null,
                'heading' => $data['heading'] ?? null,
                'last_recorded_at' => $recordedAt,
                'last_received_at' => $receivedAt,
            ]);

            // Otomatis selesaikan perjalanan jika GPS Driver telah stabil
            // di area PT TRIX INDONESIA selama minimal 3 menit.
            $autoCompleted = app(TripAutoCompletionService::class)
                ->completeIfReturnedToCompany($trip->fresh(), $location);

            return response()->json([
                'message' => $autoCompleted
                    ? 'Lokasi diterima. Perjalanan otomatis diselesaikan karena Driver telah kembali ke PT TRIX INDONESIA.'
                    : 'Lokasi GPS berhasil diterima.',
                'data' => $location,
                'auto_completed' => $autoCompleted,
            ], 201);
        });
    }

    /**
     * Data perjalanan aktif untuk HR Live Monitoring.
     */
    public function live(Request $request)
    {
        abort_unless($request->user()->role === 'hr', 403);

        $trips = Trip::with([
            'driver:id,name,employee_id,phone',
            'vehicle:id,plate_number',
            'trackingSession',
            'stops',
        ])
            ->where('status', 'active')
            ->latest()
            ->get();

        return response()->json([
            'data' => $trips,
        ]);
    }

    /**
     * Driver mengonfirmasi kedatangan di tujuan.
     * Tujuan wajib dikunjungi sesuai urutan HR.
     */
    public function arriveAtStop(Request $request, string $stopId)
    {
        abort_unless($request->user()->role === 'driver', 403);

        $data = $request->validate([
            'arrival_latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'arrival_longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ]);

        return DB::transaction(function () use ($request, $stopId, $data) {
            $stop = TripStop::whereKey($stopId)
                ->lockForUpdate()
                ->firstOrFail();

            $trip = Trip::whereKey($stop->trip_id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $trip->driver_id === (int) $request->user()->id,
                403
            );

            if ($trip->status !== 'active') {
                return response()->json([
                    'message' => 'Perjalanan belum aktif atau sudah selesai.',
                ], 422);
            }

            if ($stop->status !== 'pending') {
                return response()->json([
                    'message' => 'Tujuan ini sudah diproses atau tidak berstatus pending.',
                ], 422);
            }

            // Cegah driver melewati tujuan sebelumnya.
            $previousIncompleteStop = TripStop::where('trip_id', $trip->id)
                ->where('sequence', '<', $stop->sequence)
                ->where('status', '!=', 'departed')
                ->exists();

            if ($previousIncompleteStop) {
                return response()->json([
                    'message' => 'Selesaikan tujuan sebelumnya terlebih dahulu.',
                ], 422);
            }

            // Pastikan tujuan yang dipilih adalah tujuan berikutnya.
            $nextPendingStop = TripStop::where('trip_id', $trip->id)
                ->where('status', 'pending')
                ->orderBy('sequence')
                ->first();

            if (
                !$nextPendingStop
                || (int) $nextPendingStop->id !== (int) $stop->id
            ) {
                return response()->json([
                    'message' => 'Kunjungi tujuan sesuai urutan penugasan dari HR.',
                ], 422);
            }

            $now = now();

            $stop->update([
                'status' => 'arrived',
                'arrival_time' => $now,
                'arrival_latitude' => $data['arrival_latitude'] ?? null,
                'arrival_longitude' => $data['arrival_longitude'] ?? null,
            ]);

            TripEvent::create([
                'trip_id' => $trip->id,
                'actor_id' => $request->user()->id,
                'event_type' => 'destination_arrived',
                'description' => 'Tiba di ' . $stop->destination,
                'metadata' => [
                    'stop_id' => $stop->id,
                    'sequence' => $stop->sequence,
                ],
                'event_time' => $now,
            ]);

            TripNotificationService::notifyParticipants(
                $trip,
                'Driver tiba di tujuan',
                $trip->driver?->name . ' tiba di ' . $stop->destination . ' (' . $trip->trip_code . ').',
                'destination_arrived'
            );

            return response()->json([
                'message' => 'Kedatangan di ' . $stop->destination . ' berhasil dicatat.',
                'data' => $stop->fresh(),
            ]);
        });
    }

    /**
     * Driver mengonfirmasi bahwa ia melanjutkan perjalanan dari tujuan.
     */
    public function departFromStop(Request $request, string $stopId)
    {
        abort_unless($request->user()->role === 'driver', 403);

        return DB::transaction(function () use ($request, $stopId) {
            $stop = TripStop::whereKey($stopId)
                ->lockForUpdate()
                ->firstOrFail();

            $trip = Trip::whereKey($stop->trip_id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $trip->driver_id === (int) $request->user()->id,
                403
            );

            if ($trip->status !== 'active') {
                return response()->json([
                    'message' => 'Perjalanan tidak sedang aktif.',
                ], 422);
            }

            if ($stop->status !== 'arrived') {
                return response()->json([
                    'message' => 'Konfirmasi tiba di tujuan ini terlebih dahulu.',
                ], 422);
            }

            $previousIncompleteStop = TripStop::where('trip_id', $trip->id)
                ->where('sequence', '<', $stop->sequence)
                ->where('status', '!=', 'departed')
                ->exists();

            if ($previousIncompleteStop) {
                return response()->json([
                    'message' => 'Selesaikan urutan tujuan sebelumnya terlebih dahulu.',
                ], 422);
            }

            $now = now();

            $stop->update([
                'status' => 'departed',
                'departure_time' => $now,
            ]);

            TripEvent::create([
                'trip_id' => $trip->id,
                'actor_id' => $request->user()->id,
                'event_type' => 'destination_departed',
                'description' => 'Melanjutkan perjalanan dari ' . $stop->destination,
                'metadata' => [
                    'stop_id' => $stop->id,
                    'sequence' => $stop->sequence,
                ],
                'event_time' => $now,
            ]);

            TripNotificationService::notifyParticipants(
                $trip,
                'Driver melanjutkan perjalanan',
                $trip->driver?->name . ' melanjutkan perjalanan dari ' . $stop->destination . ' (' . $trip->trip_code . ').',
                'destination_departed'
            );

            $nextStop = TripStop::where('trip_id', $trip->id)
                ->where('status', 'pending')
                ->orderBy('sequence')
                ->first();

            return response()->json([
                'message' => $nextStop
                    ? 'Berhasil melanjutkan perjalanan. Tujuan berikutnya: ' . $nextStop->destination
                    : 'Semua tujuan telah dikunjungi. Silakan kembali ke PT TRIX Indonesia.',
                'data' => [
                    'completed_stop' => $stop->fresh(),
                    'next_stop' => $nextStop,
                    'all_destinations_completed' => $nextStop === null,
                ],
            ]);
        });
    }

    /**
     * Daftar lokasi perusahaan untuk kebutuhan geofence.
     */
    public function geofenceLocations()
    {
        return response()->json([
            'data' => CompanyLocation::where('is_active', true)->get(),
        ]);
    }
}