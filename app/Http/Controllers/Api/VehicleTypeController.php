<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => VehicleType::withCount('vehicles')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:vehicle_types,name'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json([
            'message' => 'Jenis kendaraan berhasil ditambahkan.',
            'data' => VehicleType::create($data),
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $type = VehicleType::findOrFail($id);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100', 'unique:vehicle_types,name,' . $type->id],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);
        $type->update($data);

        return response()->json(['message' => 'Jenis kendaraan diperbarui.', 'data' => $type]);
    }

    public function destroy(string $id)
    {
        $type = VehicleType::findOrFail($id);

        if ($type->vehicles()->exists()) {
            return response()->json(['message' => 'Jenis kendaraan masih digunakan.'], 422);
        }

        $type->delete();

        return response()->json(['message' => 'Jenis kendaraan dihapus.']);
    }
}
