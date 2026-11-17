<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('hr.dashboard');
        }

        return view('auth.login');
    }


    /** Show HR self-registration form. */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('hr.dashboard');
        }

        return view('auth.register');
    }

    /** Create a new HR account using the company registration code. */
    public function register(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('hr.dashboard');
        }

        $companyCode = (string) config('services.hr_registration_code');

        // Fail closed: registration is unavailable until the company configures its code.
        if ($companyCode === '') {
            return back()->withInput($request->except(['password', 'password_confirmation', 'registration_code']))
                ->withErrors(['registration_code' => 'Pendaftaran HR belum diaktifkan. Hubungi administrator perusahaan.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:100', 'unique:users,employee_id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'registration_code' => ['required', 'string', function ($attribute, $value, $fail) use ($companyCode) {
                if (!hash_equals($companyCode, (string) $value)) {
                    $fail('Kode registrasi perusahaan tidak valid.');
                }
            }],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'employee_id' => $validated['employee_id'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'hr',
            'is_active' => true,
            'must_change_password' => false,
            'created_by' => null,
        ]);

        return redirect()->route('login')
            ->with('success', 'Akun HR berhasil dibuat. Silakan login menggunakan Employee ID dan password.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credentials['is_active'] = true;

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'employee_id' => 'ID karyawan atau password salah, atau akun tidak aktif.',
            ]);
        }

        $request->session()->regenerate();

        if (Auth::user()->role !== 'hr') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'employee_id' => 'Halaman ini hanya dapat diakses oleh HR.',
            ]);
        }

        return redirect()->intended(route('hr.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
