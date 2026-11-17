<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    private const MANUAL_STATUSES = [
        'available',
        'maintenance',
        'inactive',
    ];

    public function index(Request $request): View|JsonResponse
    {
        $base = Vehicle::query()
            ->with('vehicleType')
            ->withExists([
                'trips as has_active_trip' => fn ($query) =>
                    $query->where('status', 'active'),
            ]);

        $vehicles = (clone $base)
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = trim((string) $request->input('search'));

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('plate_number', 'like', "%{$term}%")
                        ->orWhere('brand', 'like', "%{$term}%")
                        ->orWhere('model', 'like', "%{$term}%");
                });
            })
            ->when(
                $request->filled('status') && $request->input('status') !== 'all',
                function ($query) use ($request) {
                    $status = $request->input('status');

                    if ($status === 'in_use') {
                        $query->whereHas('trips', function ($tripQuery) {
                            $tripQuery->where('status', 'active');
                        });
                    } elseif (in_array($status, self::MANUAL_STATUSES, true)) {
                        $query->whereDoesntHave('trips', function ($tripQuery) {
                            $tripQuery->where('status', 'active');
                        })->where('status', $status);
                    }
                }
            )
            ->latest()
            ->get();

        // AJAX response: JSON row HTML + count
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'html'  => $this->renderRowsHtml(
                    $vehicles,
                    (string) $request->input('search', '')
                ),
                'count' => $vehicles->count(),
            ]);
        }

        // Normal page load
        $activeVehicleIds = Vehicle::whereHas('trips', function ($query) {
            $query->where('status', 'active');
        })->pluck('id');

        $summary = [
            'total' => Vehicle::count(),

            'available' => Vehicle::whereNotIn('id', $activeVehicleIds)
                ->where('status', 'available')
                ->count(),

            'in_use' => $activeVehicleIds->count(),

            'maintenance' => Vehicle::whereNotIn('id', $activeVehicleIds)
                ->where('status', 'maintenance')
                ->count(),

            'inactive' => Vehicle::whereNotIn('id', $activeVehicleIds)
                ->where('status', 'inactive')
                ->count(),

            'active' => Trip::where('status', 'active')->count(),

            'pending' => Trip::where('status', 'pending')->count(),

            'completed' => Trip::where('status', 'completed')->count(),
        ];

        $trips = Trip::with([
            'driver',
            'vehicle',
            'stops',
        ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'employee_id',
            ]);

        return view('vehicles.index', compact(
            'vehicles',
            'summary',
            'trips',
            'drivers'
        ));
    }

    /**
     * Render baris tabel kendaraan sebagai HTML untuk AJAX response.
     */
    private function renderRowsHtml($vehicles, string $searchTerm): string
    {
        if ($vehicles->isEmpty()) {
            $search = e($searchTerm);

            $message = $search !== ''
                ? 'No results for "<span class="font-semibold">' . $search . '</span>". Try a different plate number, brand, or model.'
                : 'Vehicle records will appear here when available.';

            return '<tr><td colspan="6" class="py-16 text-center">'
                . '<div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-50 via-white to-cyan-50 text-indigo-500 ring-1 ring-indigo-100 shadow-sm">'
                . '<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/></svg>'
                . '</div>'
                . '<p class="mt-4 text-sm font-bold text-slate-700">No vehicles found</p>'
                . '<p class="mt-1 text-xs text-slate-500">' . $message . '</p>'
                . '</td></tr>';
        }

        $statusStyles = [
            'available' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'in_use' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
            'maintenance' => 'bg-orange-50 text-orange-700 ring-orange-200',
            'inactive' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ];

        $statusLabels = [
            'available' => 'Available',
            'in_use' => 'In Use',
            'maintenance' => 'Maintenance',
            'inactive' => 'Inactive',
        ];

        $html = '';

        foreach ($vehicles as $vehicle) {
            $isInUse = (bool) ($vehicle->has_active_trip ?? false);
            $status = $isInUse ? 'in_use' : $vehicle->status;

            $statusClass = $statusStyles[$status] ?? $statusStyles['inactive'];

            $dotClass = $status === 'available'
                ? 'bg-emerald-500'
                : ($status === 'in_use'
                    ? 'bg-cyan-500'
                    : ($status === 'maintenance'
                        ? 'bg-orange-500'
                        : 'bg-slate-500'));

            $statusLabel = $statusLabels[$status] ?? ucfirst($status);

            $brand = e($vehicle->brand ?: '-');
            $model = e($vehicle->model ?: '');
            $year = e((string) ($vehicle->year ?: '-'));
            $plate = e($vehicle->plate_number);
            $vid = (int) $vehicle->id;
            $deleteUrl = e(route('hr.vehicles.destroy', $vehicle));

            $disabledAttr = $isInUse ? 'disabled' : '';

            $html .= '<tr class="trix-row">'
                . '<td class="px-6 py-4">'
                . '<div class="flex items-center gap-3">'
                . '<div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-cyan-50 text-indigo-600 ring-1 ring-indigo-100">'
                . '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/></svg>'
                . '</div>'
                . '<div>'
                . '<p class="font-semibold text-[#1C2A40]">' . $brand . ' ' . $model . '</p>'
                . '<p class="mt-0.5 text-[10px] text-slate-500">Vehicle ID #' . $vid . '</p>'
                . '</div>'
                . '</div>'
                . '</td>'
                . '<td class="px-6 py-4 font-semibold text-[#1C2A40]">' . $plate . '</td>'
                . '<td class="px-6 py-4 text-slate-600">' . $brand . ' / ' . $model . '</td>'
                . '<td class="px-6 py-4 text-slate-600">' . $year . '</td>'
                . '<td class="px-6 py-4">'
                . '<span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold ring-1 ' . $statusClass . '">'
                . '<span class="h-1.5 w-1.5 rounded-full ' . $dotClass . '"></span>'
                . $statusLabel
                . '</span>'
                . '</td>'
                . '<td class="px-6 py-4">'
                . '<div class="flex justify-center gap-1.5">'
                . '<button type="button" onclick="viewVehicle(' . $vid . ')" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100" title="View Details">'
                . '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>'
                . '</button>'
                . '<button type="button" onclick="openEditModal(' . $vid . ')" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100" title="Edit Vehicle">'
                . '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>'
                . '</button>'
                . '<button type="button" onclick="openStatusModal(' . $vid . ', \'' . $status . '\')" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200" title="Change Status" ' . $disabledAttr . '>'
                . '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>'
                . '</button>'
                . '<button type="button" onclick="openDeleteModal(\'' . $deleteUrl . '\', \'' . $plate . '\')" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100" title="Delete Vehicle">'
                . '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>'
                . '</button>'
                . '</div>'
                . '</td>'
                . '</tr>';
        }

        return $html;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:30',
                'unique:vehicles,plate_number',
            ],
            'brand' => [
                'required',
                'string',
                'max:100',
            ],
            'model' => [
                'required',
                'string',
                'max:100',
            ],
            'year' => [
                'nullable',
                'integer',
                'between:1900,2099',
            ],
            'status' => [
                'required',
                Rule::in(self::MANUAL_STATUSES),
            ],
        ]);

        DB::transaction(function () use ($request, $data) {
            $vehicle = Vehicle::create($data + [
                'vehicle_type_id' => null,
            ]);

            VehicleStatusHistory::create([
                'vehicle_id' => $vehicle->id,
                'changed_by' => $request->user()?->id,
                'old_status' => null,
                'new_status' => $vehicle->status,
                'reason' => 'Kendaraan ditambahkan',
            ]);
        });

        return redirect()
            ->route('hr.vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        Vehicle $vehicle
    ): RedirectResponse {
        $data = $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('vehicles', 'plate_number')
                    ->ignore($vehicle->id),
            ],
            'brand' => [
                'required',
                'string',
                'max:100',
            ],
            'model' => [
                'required',
                'string',
                'max:100',
            ],
            'year' => [
                'nullable',
                'integer',
                'between:1900,2099',
            ],
            'status' => [
                'required',
                Rule::in(self::MANUAL_STATUSES),
            ],
        ]);

        return DB::transaction(function () use ($request, $vehicle, $data) {
            $locked = Vehicle::query()
                ->lockForUpdate()
                ->findOrFail($vehicle->id);

            if ($locked->trips()->where('status', 'active')->exists()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'status' => 'Kendaraan sedang digunakan dalam trip aktif. Data/status tidak dapat diubah.',
                    ]);
            }

            $oldStatus = $locked->status;

            $locked->update($data);

            if ($oldStatus !== $locked->status) {
                $this->recordStatus(
                    $request,
                    $locked,
                    $oldStatus,
                    $locked->status,
                    'Status diubah melalui Vehicle Management'
                );
            }

            return redirect()
                ->route('hr.vehicles.index')
                ->with('success', 'Data kendaraan berhasil diperbarui.');
        });
    }

    public function changeStatus(
        Request $request,
        Vehicle $vehicle
    ): RedirectResponse {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(self::MANUAL_STATUSES),
            ],
            'reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        return DB::transaction(function () use ($request, $vehicle, $data) {
            $locked = Vehicle::query()
                ->lockForUpdate()
                ->findOrFail($vehicle->id);

            if ($locked->trips()->where('status', 'active')->exists()) {
                return back()->withErrors([
                    'status' => 'Kendaraan sedang digunakan dalam trip aktif dan statusnya tidak dapat diubah.',
                ]);
            }

            $oldStatus = $locked->status;

            if ($oldStatus !== $data['status']) {
                $locked->update([
                    'status' => $data['status'],
                ]);

                $this->recordStatus(
                    $request,
                    $locked,
                    $oldStatus,
                    $locked->status,
                    $data['reason'] ?? null
                );
            }

            return back()->with(
                'success',
                'Status kendaraan berhasil diperbarui.'
            );
        });
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        return DB::transaction(function () use ($vehicle) {
            $locked = Vehicle::query()
                ->lockForUpdate()
                ->findOrFail($vehicle->id);

            // Kendaraan yang masih digunakan dalam trip aktif
            // tidak boleh dihapus.
            if ($locked->trips()->where('status', 'active')->exists()) {
                return back()->withErrors([
                    'vehicle' => 'Kendaraan tidak dapat dihapus karena sedang digunakan dalam perjalanan aktif.',
                ]);
            }

            // Soft delete: riwayat trip dan perubahan status tetap tersimpan.
            $locked->delete();

            return redirect()
                ->route('hr.vehicles.index')
                ->with(
                    'success',
                    'Kendaraan berhasil dihapus. Riwayat kendaraan tetap tersimpan.'
                );
        });
    }

    private function recordStatus(
        Request $request,
        Vehicle $vehicle,
        ?string $oldStatus,
        string $newStatus,
        ?string $reason
    ): void {
        VehicleStatusHistory::create([
            'vehicle_id' => $vehicle->id,
            'changed_by' => $request->user()?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);
    }
}