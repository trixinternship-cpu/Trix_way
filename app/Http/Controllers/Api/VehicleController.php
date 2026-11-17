<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::with('vehicleType')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->toString();
                $q->where('plate_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return response()->json($vehicles);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_type_id' => ['required', 'exists:vehicle_types,id'],
            'plate_number' => ['required', 'string', 'max:30', 'unique:vehicles,plate_number'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => ['sometimes', Rule::in(['available', 'in_use', 'maintenance', 'inactive'])],
        ]);

        $vehicle = Vehicle::create($data);

        VehicleStatusHistory::create([
            'vehicle_id' => $vehicle->id,
            'changed_by' => $request->user()->id,
            'old_status' => null,
            'new_status' => $vehicle->status,
            'reason' => 'Kendaraan ditambahkan',
        ]);

        return response()->json(['message' => 'Kendaraan berhasil ditambahkan.', 'data' => $vehicle], 201);
    }

    public function show(string $id)
    {
        return response()->json([
            'data' => Vehicle::with(['vehicleType', 'statusHistories.changedBy'])->findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $data = $request->validate([
            'vehicle_type_id' => ['sometimes', 'required', 'exists:vehicle_types,id'],
            'plate_number' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('vehicles', 'plate_number')->ignore($vehicle->id)],
            'brand' => ['sometimes', 'nullable', 'string', 'max:100'],
            'model' => ['sometimes', 'nullable', 'string', 'max:100'],
            'color' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $vehicle->update($data);

        return response()->json(['message' => 'Data kendaraan diperbarui.', 'data' => $vehicle]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['available', 'in_use', 'maintenance', 'inactive'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($request, $id, $data) {
            $vehicle = Vehicle::lockForUpdate()->findOrFail($id);

            if ($vehicle->status === 'in_use' && $data['status'] !== 'in_use') {
                return response()->json(['message' => 'Status kendaraan yang sedang digunakan tidak dapat diubah manual.'], 422);
            }

            $oldStatus = $vehicle->status;
            $vehicle->update(['status' => $data['status']]);

            if ($oldStatus !== $vehicle->status) {
                VehicleStatusHistory::create([
                    'vehicle_id' => $vehicle->id,
                    'changed_by' => $request->user()->id,
                    'old_status' => $oldStatus,
                    'new_status' => $vehicle->status,
                    'reason' => $data['reason'] ?? null,
                ]);
            }

            return response()->json(['message' => 'Status kendaraan diperbarui.', 'data' => $vehicle]);
        });
    }
}
