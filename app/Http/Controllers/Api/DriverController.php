<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $drivers = User::where('role', 'driver')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15);

        return response()->json($drivers);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:255', 'unique:users,employee_id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $driver = User::create([
            ...$data,
            'role' => 'driver',
            'is_active' => true,
            'must_change_password' => true,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Akun Driver berhasil dibuat.',
            'data' => $driver->only(['id', 'name', 'employee_id', 'email', 'phone', 'role', 'is_active']),
        ], 201);
    }

    public function show(string $id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);

        return response()->json([
            'data' => $driver->only([
                'id', 'name', 'employee_id', 'email', 'phone',
                'role', 'is_active', 'created_at',
            ]),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'employee_id' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'employee_id')->ignore($driver->id)],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($driver->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);

        $driver->update($data);

        return response()->json(['message' => 'Data Driver diperbarui.', 'data' => $driver]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $driver = User::where('role', 'driver')->findOrFail($id);

        $driver->update(['is_active' => $data['is_active']]);

        if (!$driver->is_active) {
            $driver->tokens()->delete();
        }

        return response()->json([
            'message' => 'Status Driver diperbarui.',
            'data' => ['id' => $driver->id, 'is_active' => $driver->is_active],
        ]);
    }
}
