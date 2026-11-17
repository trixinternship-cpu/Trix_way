<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\TripNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TripManagementController extends Controller
{
    /**
     * Daftar perjalanan untuk HR.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Trip::with([
            'driver:id,name,employee_id',
            'vehicle:id,plate_number,brand,model',
            'stops',
        ]);

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));

            $query->where(function ($q) use ($term) {
                $q->where('trip_code', 'like', "%{$term}%")
                    ->orWhere('purpose', 'like', "%{$term}%")
                    ->orWhereHas('driver', fn ($driver) =>
                        $driver->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('vehicle', fn ($vehicle) =>
                        $vehicle->where('plate_number', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $trips = $query->latest()->paginate(15)->withQueryString();

        $trips->getCollection()->transform(function (Trip $trip) {
            $trip->setAttribute('actual_start_time', $trip->start_time);
            $trip->setAttribute('actual_end_time', $trip->end_time);
            $trip->setAttribute('formatted_start_time', $trip->start_time ? Carbon::parse($trip->start_time)->format('Y-m-d\TH:i') : '');
            $trip->setAttribute('formatted_end_time', $trip->end_time ? Carbon::parse($trip->end_time)->format('Y-m-d\TH:i') : '');
            return $trip;
        });

        // ===== AJAX: Return JSON rows =====
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $rowsHtml = '';
            if ($trips->isEmpty()) {
                $search = e($request->input('search', ''));
                $message = $search !== ''
                    ? 'No results for "<span class="font-semibold">' . $search . '</span>". Try a different code, purpose, driver, or vehicle.'
                    : 'Trip records will appear here when available.';

                $rowsHtml = '<tr><td colspan="7" class="py-16 text-center">'
                    . '<div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-50 via-white to-cyan-50 text-indigo-500 ring-1 ring-indigo-100 shadow-sm">'
                    . '<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>'
                    . '</div>'
                    . '<p class="mt-4 text-sm font-bold text-slate-700">No trips found</p>'
                    . '<p class="mt-1 text-xs text-slate-500">' . $message . '</p>'
                    . '</td></tr>';
            } else {
                $statusColors = ['pending' => 'bg-orange-50 text-orange-700 ring-orange-200', 'active' => 'bg-cyan-50 text-cyan-700 ring-cyan-200', 'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200'];
                $statusDots   = ['pending' => 'bg-orange-500', 'active' => 'bg-cyan-500', 'completed' => 'bg-emerald-500', 'cancelled' => 'bg-rose-500'];

                foreach ($trips as $trip) {
                    $statusColor = $statusColors[$trip->status] ?? '';
                    $statusDot   = $statusDots[$trip->status] ?? '';
                    $displayDate = $trip->start_time ? Carbon::parse($trip->start_time)->format('d M Y, H:i') : '-';
                    $tripJson    = json_encode($trip, JSON_HEX_APOS | JSON_HEX_QUOT);

                    $rowsHtml .= '<tr class="trix-row">'
                        . '<td class="px-6 py-4 font-semibold text-[#1C2A40]">' . e($trip->trip_code) . '</td>'
                        . '<td class="px-6 py-4 text-slate-600">' . e($trip->driver->name ?? '-') . '</td>'
                        . '<td class="px-6 py-4 text-slate-600">' . e($trip->vehicle->plate_number ?? '-') . '</td>'
                        . '<td class="px-6 py-4 text-slate-600">' . e($trip->stops->first()->destination ?? '-') . '</td>'
                        . '<td class="px-6 py-4 text-slate-500">' . $displayDate . '</td>'
                        . '<td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold ring-1 ' . $statusColor . '"><span class="h-1.5 w-1.5 rounded-full ' . $statusDot . '"></span>' . ucfirst($trip->status) . '</span></td>'
                        . '<td class="px-6 py-4 text-center"><div class="flex justify-center gap-1.5">'
                        . '<button type="button" onclick=\'viewTrip(' . $tripJson . ')\' class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100" title="View Details"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></button>';

                    if ($trip->status === 'pending') {
                        $rowsHtml .= '<form action="' . route('hr.trips.simulate', [$trip->id, 'start']) . '" method="POST" class="inline">' . csrf_field() . method_field('PATCH') . '<button type="submit" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200" title="Simulate: Driver Start"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></button></form>';
                    } elseif ($trip->status === 'active') {
                        $rowsHtml .= '<form action="' . route('hr.trips.simulate', [$trip->id, 'finish']) . '" method="POST" class="inline">' . csrf_field() . method_field('PATCH') . '<button type="submit" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200" title="Simulate: Driver Finish"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg></button></form>';
                    }

                    $rowsHtml .= '<button type="button" onclick=\'openEditModal(' . $tripJson . ')\' class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100" title="Edit Trip"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></button>';

                    if (in_array($trip->status, ['pending', 'active'])) {
                        $rowsHtml .= '<button type="button" onclick="openCancelModal(' . $trip->id . ', \'' . e($trip->trip_code) . '\')" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100" title="Cancel Trip"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></button>';
                    }

                    $rowsHtml .= '<form action="' . route('hr.trips.destroy', $trip->id) . '" method="POST" class="inline delete-form" data-name="' . e($trip->trip_code) . '">' . csrf_field() . method_field('DELETE') . '<button type="button" onclick="openDeleteModal(this)" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100" title="Delete Trip"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>'
                        . '</div></td></tr>';
                }
            }

            return response()->json([
                'html'  => $rowsHtml,
                'count' => $trips->total(),
            ]);
        }
        // ===== END AJAX =====

        $summary = [
            'total' => Trip::count(),
            'active' => Trip::where('status', 'active')->count(),
            'pending' => Trip::where('status', 'pending')->count(),
            'completed' => Trip::where('status', 'completed')->count(),
        ];

        // Hanya tampilkan driver aktif yang tidak sedang menjalankan trip.
        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->whereNotIn('id', function ($query) {
                $query->select('driver_id')
                    ->from('trips')
                    ->where('status', 'active')
                    ->whereNotNull('driver_id');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $vehicles = Vehicle::where('status', 'available')
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'brand', 'model']);

        return view('trips.index', compact(
            'trips',
            'summary',
            'drivers',
            'vehicles'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'driver')->where('is_active', true),
            ],
            'vehicle_id' => [
                'required',
                'integer',
                Rule::exists('vehicles', 'id')->where('status', 'available'),
            ],
            'planned_start_date' => ['required', 'date'],
            'planned_start_time' => ['required', 'date_format:H:i'],
            'purpose' => ['required', 'string', 'max:1000'],
            'origin' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*.name' => ['required', 'string', 'max:255'],
            'destinations.*.address' => ['nullable', 'string', 'max:500'],
        ]);

        $trip = DB::transaction(function () use ($validated, $request) {
            // Lock baris driver agar dua proses assignment tidak berjalan bersamaan.
            $driver = User::whereKey($validated['driver_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $driverHasActiveTrip = Trip::where('driver_id', $driver->id)
                ->where('status', 'active')
                ->exists();

            if ($driverHasActiveTrip) {
                throw ValidationException::withMessages([
                    'driver_id' => 'Driver ini sedang menjalankan perjalanan lain. Silakan pilih driver yang tersedia.',
                ]);
            }

            $vehicle = Vehicle::whereKey($validated['vehicle_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($vehicle->status !== 'available') {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'Kendaraan sudah tidak tersedia. Silakan pilih kendaraan lain.',
                ]);
            }

            $plannedStart = Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['planned_start_date'].' '.$validated['planned_start_time'],
                'Asia/Jakarta'
            );

            $trip = Trip::create([
                'trip_code' => $this->generateTripCode(),
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'created_by' => $request->user()->id,
                'purpose' => $validated['purpose'],
                'origin' => $validated['origin'],
                'planned_start' => $plannedStart,
                'status' => 'pending',
            ]);

            foreach (array_values($validated['destinations']) as $index => $destination) {
                $trip->stops()->create([
                    'sequence' => $index + 1,
                    'stop_type' => 'destination',
                    'destination' => $destination['name'],
                    'status' => 'pending',
                    'added_by' => $request->user()->id,
                    'added_at' => now(),
                ]);
            }

            return $trip;
        });

        // Beri tahu HR dan driver yang ditugaskan bahwa ada tugas perjalanan baru.
        TripNotificationService::notifyParticipants(
            $trip->fresh(),
            'Tugas Perjalanan Baru',
            "Anda mendapatkan tugas perjalanan {$trip->trip_code}. Silakan periksa detail perjalanan dan jadwal tugas.",
            'task_assigned'
        );

        return redirect()
            ->route('hr.trips.index')
            ->with('success', "Trip {$trip->trip_code} berhasil dibuat dan menunggu driver memulai perjalanan.");
    }

    public function update(Request $request, Trip $trip): RedirectResponse
    {
        if ($trip->status !== 'pending') {
            return back()->with('error', 'Trip hanya dapat diedit selama status masih Pending.');
        }

        $validated = $request->validate([
            'driver_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'driver')->where('is_active', true),
            ],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_time' => ['required', 'date_format:H:i'],
            'purpose' => ['required', 'string', 'max:1000'],
            'origin' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*.name' => ['required', 'string', 'max:255'],
            'destinations.*.address' => ['nullable', 'string', 'max:500'],
        ]);

        $previousDriverId = (int) $trip->driver_id;

        DB::transaction(function () use ($validated, $trip) {
            // Kunci driver yang dipilih agar assignment tidak bentrok.
            $driver = User::whereKey($validated['driver_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $driverHasActiveTrip = Trip::where('driver_id', $driver->id)
                ->where('status', 'active')
                ->where('id', '!=', $trip->id)
                ->exists();

            if ($driverHasActiveTrip) {
                throw ValidationException::withMessages([
                    'driver_id' => 'Driver ini sedang menjalankan perjalanan lain. Silakan pilih driver yang tersedia.',
                ]);
            }

            $vehicle = Vehicle::whereKey($validated['vehicle_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $vehicleAlreadyUsed = Trip::where('vehicle_id', $vehicle->id)
                ->where('status', 'active')
                ->exists();

            if ($vehicle->status !== 'available'
                && (int) $trip->vehicle_id !== (int) $vehicle->id) {
                abort(422, 'Kendaraan yang dipilih tidak tersedia.');
            }

            if ($vehicleAlreadyUsed && (int) $trip->vehicle_id !== (int) $vehicle->id) {
                abort(422, 'Kendaraan sedang digunakan oleh trip lain.');
            }

            $trip->update([
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'purpose' => $validated['purpose'],
                'origin' => $validated['origin'],
                'planned_start' => Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $validated['planned_start_date'].' '.$validated['planned_start_time'],
                    'Asia/Jakarta'
                ),
            ]);

            $trip->stops()->delete();

            foreach (array_values($validated['destinations']) as $index => $destination) {
                $trip->stops()->create([
                    'sequence' => $index + 1,
                    'stop_type' => 'destination',
                    'destination' => $destination['name'],
                    'status' => 'pending',
                ]);
            }
        });

        if ((int) $trip->fresh()->driver_id !== $previousDriverId) {
            TripNotificationService::notifyParticipants(
                $trip->fresh(),
                'Tugas Perjalanan Diperbarui',
                "Anda mendapatkan penugasan perjalanan {$trip->trip_code}. Silakan periksa detail perjalanan terbaru.",
                'task_assigned'
            );
        }

        return redirect()
            ->route('hr.trips.index')
            ->with('success', 'Assignment trip berhasil diperbarui.');
    }

    public function destroy(Trip $trip): RedirectResponse
    {
        if ($trip->status === 'active') {
            return back()->with('error', 'Trip yang sedang berjalan tidak dapat dihapus.');
        }

        // Soft delete: data trip dan seluruh detailnya tetap tersimpan selama 7 hari.
        $trip->delete();

        return redirect()
            ->route('hr.trips.index')
            ->with('success', 'Trip dipindahkan ke Recently Deleted pada Trip History. Data dapat dipulihkan selama 7 hari.');
    }

    public function cancel(Request $request, Trip $trip): RedirectResponse
    {
        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        if (!in_array($trip->status, ['pending', 'active'], true)) {
            return back()->with('error', 'Trip ini tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($trip, $validated) {
            $trip->update(['status' => 'cancelled']);

            if ($trip->vehicle && $trip->status === 'cancelled') {
                $vehicleHasOtherActiveTrip = Trip::where('vehicle_id', $trip->vehicle_id)
                    ->where('status', 'active')
                    ->where('id', '!=', $trip->id)
                    ->exists();

                if (!$vehicleHasOtherActiveTrip) {
                    $trip->vehicle->update(['status' => 'available']);
                }
            }
        });

        return redirect()
            ->route('hr.trips.index')
            ->with('success', 'Trip berhasil dibatalkan.');
    }

    public function simulateDriver(Trip $trip, string $action): RedirectResponse
    {
        if ($action === 'start') {
            if ($trip->status !== 'pending') {
                return back()->with('error', 'Hanya trip Pending yang dapat disimulasikan mulai.');
            }

            DB::transaction(function () use ($trip) {
                $driver = User::whereKey($trip->driver_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $driverHasOtherActiveTrip = Trip::where('driver_id', $driver->id)
                    ->where('status', 'active')
                    ->where('id', '!=', $trip->id)
                    ->exists();

                if ($driverHasOtherActiveTrip) {
                    throw ValidationException::withMessages([
                        'driver_id' => 'Driver masih memiliki perjalanan Active lainnya.',
                    ]);
                }

                $vehicle = Vehicle::whereKey($trip->vehicle_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($vehicle->status !== 'available') {
                    throw ValidationException::withMessages([
                        'vehicle_id' => 'Kendaraan tidak tersedia.',
                    ]);
                }

                $trip->update([
                    'status' => 'active',
                    'start_time' => now(),
                ]);

                $vehicle->update(['status' => 'in_use']);
            });

            TripNotificationService::notifyParticipants(
                $trip->fresh(),
                'Perjalanan Dimulai',
                "Perjalanan {$trip->trip_code} telah dimulai oleh driver.",
                'trip_started'
            );

            return back()->with('success', 'Simulasi Start Trip berhasil.');
        }

        if ($action === 'finish') {
            if ($trip->status !== 'active') {
                return back()->with('error', 'Hanya trip Active yang dapat diselesaikan.');
            }

            DB::transaction(function () use ($trip) {
                $trip->update([
                    'status' => 'completed',
                    'end_time' => now(),
                ]);

                if ($trip->vehicle) {
                    $trip->vehicle->update(['status' => 'available']);
                }
            });

            TripNotificationService::notifyParticipants(
                $trip->fresh(),
                'Perjalanan Selesai',
                "Perjalanan {$trip->trip_code} telah diselesaikan.",
                'trip_completed'
            );

            return back()->with('success', 'Simulasi Finish Trip berhasil.');
        }

        return back()->with('error', 'Aksi simulasi tidak dikenali.');
    }

    public function updateTimes(Request $request, Trip $trip): RedirectResponse
    {
        $validated = $request->validate([
            'actual_start_time' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'actual_end_time' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ]);

        $startTime = filled($validated['actual_start_time'] ?? null)
            ? Carbon::createFromFormat('Y-m-d\TH:i', $validated['actual_start_time'], 'Asia/Jakarta')
            : null;

        $endTime = filled($validated['actual_end_time'] ?? null)
            ? Carbon::createFromFormat('Y-m-d\TH:i', $validated['actual_end_time'], 'Asia/Jakarta')
            : null;

        if ($startTime && $endTime && $endTime->lt($startTime)) {
            return back()
                ->withInput()
                ->with('error', 'Actual End tidak boleh lebih awal dari Actual Start.');
        }

        DB::transaction(function () use ($trip, $startTime, $endTime, $request) {
            $trip = Trip::whereKey($trip->id)->lockForUpdate()->firstOrFail();

            $changes = [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];

            foreach ($changes as $column => $newValue) {
                $oldValue = $trip->{$column};

                $oldComparable = $oldValue
                    ? Carbon::parse($oldValue)->format('Y-m-d H:i:s')
                    : null;

                $newComparable = $newValue
                    ? $newValue->copy()->format('Y-m-d H:i:s')
                    : null;

                if ($oldComparable === $newComparable) {
                    continue;
                }

                $trip->{$column} = $newValue;

                if ($newValue !== null
                    && DB::getSchemaBuilder()->hasTable('trip_time_corrections')) {
                    DB::table('trip_time_corrections')->insert([
                        'trip_id' => $trip->id,
                        'field_name' => $column,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'reason' => 'Koreksi waktu aktual oleh HR melalui Trip Management.',
                        'changed_by' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $trip->save();
        });

        return redirect()
            ->route('hr.trips.index')
            ->with('success', 'Actual Start Time dan Actual End Time berhasil diperbarui.');
    }

    private function generateTripCode(): string
    {
        do {
            $code = 'TRX-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (Trip::where('trip_code', $code)->exists());

        return $code;
    }
}