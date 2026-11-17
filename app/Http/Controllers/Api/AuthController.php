<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login Driver / HR menggunakan ID karyawan.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('employee_id', $credentials['employee_id'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'employee_id' => ['ID karyawan atau password salah.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Akun Anda sedang dinonaktifkan.',
            ], 403);
        }

        $token = $user->createToken('trix-way')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $user->only([
                'id',
                'name',
                'employee_id',
                'email',
                'phone',
                'role',
                'must_change_password',
            ]),
        ]);
    }

    /**
     * Logout dan hapus token yang sedang digunakan.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Mendapatkan profil pengguna yang sedang login.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->only([
                'id',
                'name',
                'employee_id',
                'email',
                'phone',
                'role',
                'must_change_password',
            ]),
        ]);
    }

    /**
     * Mengubah password pengguna.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
