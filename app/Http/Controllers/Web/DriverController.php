<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    /**
     * Menampilkan daftar Driver.
     */
    public function index(Request $request)
    {
        $drivers = User::where('role', 'driver')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                $request->filled('status') && $request->status !== 'all',
                function ($query) use ($request) {
                    $query->where(
                        'is_active',
                        $request->status === 'active'
                    );
                }
            )
            ->latest()
            ->get();

        $summary = [
            'total' => User::where('role', 'driver')->count(),

            'active' => User::where('role', 'driver')
                ->where('is_active', true)
                ->count(),

            'inactive' => User::where('role', 'driver')
                ->where('is_active', false)
                ->count(),
        ];

        return view('drivers.index', compact('drivers', 'summary'));
    }

    /**
     * Menambahkan Driver baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'employee_id' => [
                'required',
                'string',
                'max:255',
                'unique:users,employee_id',
            ],

            'phone' => ['required', 'string', 'max:20'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        User::create([
            'name' => $data['name'],
            'employee_id' => $data['employee_id'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'driver',
            'is_active' => $data['status'] === 'active',
            'must_change_password' => true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('hr.drivers.index')
            ->with('success', 'Driver berhasil ditambahkan.');
    }

    /**
     * Memperbarui data Driver.
     */
    public function update(Request $request, User $driver)
    {
        abort_unless($driver->role === 'driver', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'employee_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'employee_id')
                    ->ignore($driver->id),
            ],

            'phone' => ['required', 'string', 'max:20'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($driver->id),
            ],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        $driver->update([
            'name' => $data['name'],
            'employee_id' => $data['employee_id'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'is_active' => $data['status'] === 'active',
        ]);

        // Cabut token login jika Driver dinonaktifkan.
        if (!$driver->is_active) {
            $driver->tokens()->delete();
        }

        return redirect()
            ->route('hr.drivers.index')
            ->with('success', 'Data driver berhasil diperbarui.');
    }

    /**
     * Menghapus Driver menggunakan Soft Delete.
     */
    public function destroy(User $driver)
    {
        abort_unless($driver->role === 'driver', 404);

        // Periksa apakah Driver masih memiliki perjalanan aktif.
        $hasActiveTrip = $driver->trips()
            ->where('status', 'active')
            ->exists();

        if ($hasActiveTrip) {
            return redirect()
                ->route('hr.drivers.index')
                ->with(
                    'error',
                    'Driver tidak dapat dihapus karena masih menjalankan perjalanan aktif.'
                );
        }

        // Hapus token autentikasi Driver.
        $driver->tokens()->delete();

        // Hapus token perangkat / FCM.
        $driver->deviceTokens()->delete();

        // Soft Delete: akun tidak tampil lagi di daftar Driver,
        // tetapi data akun tetap tersimpan untuk menjaga riwayat perjalanan.
        $driver->delete();

        return redirect()
            ->route('hr.drivers.index')
            ->with(
                'success',
                'Driver berhasil dihapus. Riwayat perjalanan tetap tersimpan.'
            );
    }
}
