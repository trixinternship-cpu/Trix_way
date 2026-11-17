<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyLocation;
use Illuminate\Http\Request;

class CompanyLocationController extends Controller
{
    public function index()
    {
        return response()->json(['data' => CompanyLocation::orderBy('company_name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['sometimes', 'integer', 'min:10', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return response()->json([
            'message' => 'Lokasi perusahaan berhasil ditambahkan.',
            'data' => CompanyLocation::create($data),
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $location = CompanyLocation::findOrFail($id);
        $data = $request->validate([
            'company_name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'latitude' => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['sometimes', 'integer', 'min:10', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $location->update($data);

        return response()->json(['message' => 'Lokasi perusahaan diperbarui.', 'data' => $location]);
    }
}
