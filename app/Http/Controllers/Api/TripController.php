<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\TripStop;
use App\Models\TripTrackingSession;
use App\Models\Vehicle;
use App\Models\VehicleStatusHistory;
use App\Models\User;
use App\Services\TripNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TripController extends Controller
{
    /**
     * Daftar perjalanan.
     *
     * Driver hanya melihat perjalanan miliknya
     * yang belum disembunyikan dari riwayat.
     *
     * HR tetap dapat melihat seluruh perjalanan.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Trip::with([
            'driver:id,name,employee_id',
            'vehicle:id,plate_number',
            'stops',
        ]);

        if ($user->role === 'driver') {
            $query->where('driver_id', $user->id);

            $query->whereNotExists(function ($subQuery) use ($user) {
                $subQuery->select(DB::raw(1))
                    ->from('driver_hidden_trips')
                    ->whereColumn(
                        'driver_hidden_trips.trip_id',
                        'trips.id'
                    )
                    ->where(
                        'driver_hidden_trips.driver_id',
                        $user->id
                    );
            });
        } elseif ($user->role !== 'hr') {
            abort(403);
        }

        if ($request->filled('status')) {
            $request->validate([
                'status' => [
                    'required',
                    'in:pending,active,completed,cancelled',
                ],
            ]);

            $query->where('status', $request->status);
        }

        return response()->json(
            $query->latest()->paginate(15)
        );
    }

    /**
     * Driver tidak boleh membuat penugasan sendiri.
     * Penugasan dibuat oleh HR melalui web.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Penugasan perjalanan hanya dapat dibuat oleh HR.',
        ], 403);
    }

    /**
     * Detail perjalanan.
     */
    public function show(Request $request, string $id)
    {
        $trip = Trip::with([
            'driver:id,name,employee_id',
            'vehicle',
            'stops',
            'trackingSession',
            'events',
        ])->findOrFail($id);

        abort_unless(
            $request->user()->role === 'hr'
            || (
                $request->user()->role === 'driver'
                && (int) $trip->driver_id === (int) $request->user()->id
            ),
            403
        );

        return response()->json([
            'data' => $trip,
        ]);
    }

    /**
     * Driver memulai perjalanan.
     *
     * Status Pending -> Active.
     * Kendaraan Available -> In Use.
     * Sesi GPS mulai dibuat.
     */
    public function start(Request $request, string $id)
    {
        abort_unless(
            $request->user()->role === 'driver',
            403
        );

        return DB::transaction(function () use ($request, $id) {

            // Lock akun driver untuk mencegah dua trip
            // dimulai secara bersamaan.
            $driver = User::whereKey($request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            $trip = Trip::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $trip->driver_id !== (int) $driver->id) {
                abort(403, 'Penugasan ini bukan milik Anda.');
            }

            if ($trip->status !== 'pending') {
                return response()->json([
                    'message' => 'Hanya perjalanan Pending yang dapat dimulai.',
                ], 422);
            }

            // Pastikan driver tidak mempunyai trip aktif lain.
            $activeTrip = Trip::where('driver_id', $driver->id)
                ->where('status', 'active')
                ->where('id', '!=', $trip->id)
                ->exists();

            if ($activeTrip) {
                return response()->json([
                    'message' => 'Anda masih memiliki perjalanan aktif lainnya.',
                ], 422);
            }

            // Lock kendaraan.
            $vehicle = Vehicle::whereKey($trip->vehicle_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($vehicle->status !== 'available') {
                return response()->json([
                    'message' => 'Kendaraan tidak tersedia.',
                ], 422);
            }

            $now = now();

            // Aktifkan perjalanan.
            $trip->update([
                'status' => 'active',
                'start_time' => $now,
            ]);

            // Buat atau aktifkan kembali sesi GPS.
            TripTrackingSession::updateOrCreate(
                [
                    'trip_id' => $trip->id,
                ],
                [
                    'status' => 'active',
                    'started_at' => $now,
                    'stopped_at' => null,
                ]
            );

            // Ubah status kendaraan.
            $oldStatus = $vehicle->status;

            $vehicle->update([
                'status' => 'in_use',
            ]);

            VehicleStatusHistory::create([
                'vehicle_id' => $vehicle->id,
                'changed_by' => $driver->id,
                'old_status' => $oldStatus,
                'new_status' => 'in_use',
                'reason' => 'Perjalanan dimulai: ' . $trip->trip_code,
            ]);

            // Catat aktivitas perjalanan.
            TripEvent::create([
                'trip_id' => $trip->id,
                'actor_id' => $driver->id,
                'event_type' => 'trip_started',
                'description' => 'Driver memulai perjalanan.',
                'event_time' => $now,
            ]);

            TripNotificationService::notifyParticipants(
                $trip,
                'Perjalanan dimulai',
                $driver->name . ' memulai perjalanan ' . $trip->trip_code . '.',
                'trip_started'
            );

            return response()->json([
                'message' => 'Perjalanan berhasil dimulai. Sesi GPS telah diaktifkan.',
                'data' => $trip->fresh()->load([
                    'driver',
                    'vehicle',
                    'stops',
                    'trackingSession',
                ]),
            ], 200);
        });
    }

    /**
     * Driver atau HR menyelesaikan perjalanan.
     *
     * Driver hanya boleh menyelesaikan perjalanan
     * jika seluruh tujuan sudah berstatus departed.
     *
     * HR tetap dapat menyelesaikan perjalanan secara manual.
     */
    public function complete(Request $request, string $id)
    {
        $trip = Trip::findOrFail($id);

        $user = $request->user();

        abort_unless(
            $user->role === 'hr'
            || (
                $user->role === 'driver'
                && (int) $trip->driver_id === (int) $user->id
            ),
            403
        );

        $data = $request->validate([
            'end_latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'end_longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        return DB::transaction(function () use (
            $request,
            $trip,
            $data
        ) {

            $trip = Trip::whereKey($trip->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($trip->status !== 'active') {
                return response()->json([
                    'message' => 'Perjalanan tidak sedang aktif.',
                ], 422);
            }

            /*
             * VALIDASI SELURUH TUJUAN
             *
             * Driver wajib mengunjungi dan meninggalkan
             * semua tujuan sebelum menyelesaikan perjalanan.
             *
             * Status tujuan yang dianggap selesai: departed.
             */
            if ($request->user()->role === 'driver') {

                $incompleteStops = TripStop::where(
                    'trip_id',
                    $trip->id
                )
                    ->where('status', '!=', 'departed')
                    ->lockForUpdate()
                    ->exists();

                if ($incompleteStops) {
                    return response()->json([
                        'message' => 'Anda belum menyelesaikan seluruh tujuan perjalanan. Selesaikan semua tujuan terlebih dahulu.',
                    ], 422);
                }
            }

            $now = now();

            // Selesaikan perjalanan.
            $trip->update([
                'status' => 'completed',
                'end_time' => $now,
                'end_latitude' => $data['end_latitude'] ?? null,
                'end_longitude' => $data['end_longitude'] ?? null,
            ]);

            // Hentikan sesi GPS.
            $trackingSession = TripTrackingSession::where(
                'trip_id',
                $trip->id
            )->lockForUpdate()->first();

            if ($trackingSession) {
                $trackingSession->update([
                    'status' => 'stopped',
                    'stopped_at' => $now,
                ]);
            }

            // Kembalikan kendaraan menjadi Available.
            $vehicle = Vehicle::whereKey($trip->vehicle_id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $vehicle->status;

            $vehicle->update([
                'status' => 'available',
            ]);

            VehicleStatusHistory::create([
                'vehicle_id' => $vehicle->id,
                'changed_by' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => 'available',
                'reason' => 'Perjalanan selesai: ' . $trip->trip_code,
            ]);

            // Catat event penyelesaian.
            $isHr = $request->user()->role === 'hr';

            TripEvent::create([
                'trip_id' => $trip->id,
                'actor_id' => $request->user()->id,
                'event_type' => $isHr
                    ? 'trip_force_completed'
                    : 'trip_completed',
                'description' => $data['reason'] ?? (
                    $isHr
                        ? 'Perjalanan diselesaikan oleh HR.'
                        : 'Driver menyelesaikan perjalanan.'
                ),
                'event_time' => $now,
            ]);

            TripNotificationService::notifyParticipants(
                $trip,
                'Perjalanan selesai',
                ($trip->driver?->name ?? 'Driver') . ' menyelesaikan perjalanan ' . $trip->trip_code . '.',
                'trip_completed'
            );

            return response()->json([
                'message' => 'Perjalanan berhasil diselesaikan.',
                'data' => $trip->fresh()->load([
                    'driver',
                    'vehicle',
                    'stops',
                    'trackingSession',
                ]),
            ]);
        });
    }

    /**
     * Koreksi waktu perjalanan oleh HR.
     */
    public function correctTime(Request $request, string $id)
    {
        abort_unless(
            $request->user()->role === 'hr',
            403
        );

        $trip = Trip::findOrFail($id);

        $data = $request->validate([
            'actual_start_time' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
            ],
            'actual_end_time' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
            ],
            'reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $startTime = !empty($data['actual_start_time'])
            ? Carbon::createFromFormat(
                'Y-m-d\TH:i',
                $data['actual_start_time'],
                'Asia/Jakarta'
            )
            : null;

        $endTime = !empty($data['actual_end_time'])
            ? Carbon::createFromFormat(
                'Y-m-d\TH:i',
                $data['actual_end_time'],
                'Asia/Jakarta'
            )
            : null;

        if ($startTime && $endTime && $endTime->lt($startTime)) {
            return response()->json([
                'message' => 'Waktu selesai tidak boleh lebih awal dari waktu mulai.',
            ], 422);
        }

        return DB::transaction(function () use (
            $request,
            $trip,
            $startTime,
            $endTime,
            $data
        ) {

            $trip = Trip::whereKey($trip->id)
                ->lockForUpdate()
                ->firstOrFail();

            $changes = [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];

            $corrections = [];

            foreach ($changes as $column => $newValue) {

                $oldValue = $trip->{$column};

                $oldComparable = $oldValue
                    ? Carbon::parse($oldValue)->format('Y-m-d H:i:s')
                    : null;

                $newComparable = $newValue
                    ? $newValue->format('Y-m-d H:i:s')
                    : null;

                if ($oldComparable === $newComparable) {
                    continue;
                }

                $trip->{$column} = $newValue;

                $corrections[] = [
                    'field_name' => $column,
                    'old_value' => $oldComparable,
                    'new_value' => $newComparable,
                ];
            }

            $trip->save();

            // Simpan audit jika tabel koreksi sudah tersedia.
            if (
                !empty($corrections)
                && Schema::hasTable('trip_time_corrections')
            ) {
                foreach ($corrections as $correction) {

                    DB::table('trip_time_corrections')->insert([
                        'trip_id' => $trip->id,
                        'field_name' => $correction['field_name'],
                        'old_value' => $correction['old_value'],
                        'new_value' => $correction['new_value'],
                        'reason' => $data['reason'],
                        'changed_by' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'message' => 'Koreksi waktu perjalanan berhasil disimpan.',
                'data' => $trip->fresh(),
                'corrections' => $corrections,
            ]);
        });
    }
}
