
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\VehicleController;
use App\Http\Controllers\Web\TripManagementController;
use App\Http\Controllers\Web\TripHistoryController;
use App\Http\Controllers\Web\DriverController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\LiveMonitoringController;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect halaman utama ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Login
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

// HR password reset via email
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('/forgot-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [\App\Http\Controllers\Web\PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'reset'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

// HR self-registration
Route::get('/register', [AuthController::class, 'showRegister'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('register.process');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| HR Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:hr'])
    ->prefix('hr')
    ->name('hr.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', function () {

            $activeVehicleIds = Vehicle::whereHas('trips', function ($query) {
                $query->where('status', 'active');
            })->pluck('id');

            $summary = [
                'drivers' => [
                    'total' => User::where('role', 'driver')->count(),
                    'active' => User::where('role', 'driver')
                        ->where('is_active', true)
                        ->count(),
                    'inactive' => User::where('role', 'driver')
                        ->where('is_active', false)
                        ->count(),
                ],

                'vehicles' => [
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
                ],

                'trips' => [
                    'active' => Trip::where('status', 'active')->count(),
                    'completed_today' => Trip::where('status', 'completed')
                        ->whereDate('end_time', today())
                        ->count(),
                ],
            ];

            return view('hr.dashboard', compact('summary'));

        })->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Live Monitoring
        |--------------------------------------------------------------------------
        */

        Route::get('/live-monitoring', [LiveMonitoringController::class, 'index'])
            ->name('live-monitoring');

        Route::get('/live-monitoring/data', [LiveMonitoringController::class, 'data'])
            ->name('live-monitoring.data');


        /*
        |--------------------------------------------------------------------------
        | Driver Management
        |--------------------------------------------------------------------------
        */

        // Menampilkan daftar driver
        Route::get('/drivers', function () {

            $drivers = User::where('role', 'driver')
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

        })->name('drivers.index');


        // Menambahkan driver
        Route::post('/drivers', function (Request $request) {

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'employee_id' => [
                    'required',
                    'string',
                    'max:100',
                    'unique:users,employee_id',
                ],
                'phone' => ['nullable', 'string', 'max:30'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
                'password_confirmation' => ['required', 'same:password'],
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
            ]);

            User::create([
                'name' => $validated['name'],
                'employee_id' => $validated['employee_id'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'driver',
                'is_active' => ($validated['status'] ?? 'active') === 'active',
            ]);

            return redirect()
                ->route('hr.drivers.index')
                ->with('success', 'Driver berhasil ditambahkan.');

        })->name('drivers.store');


        // Edit driver dan toggle status
        Route::put('/drivers/{driver}', function (Request $request, $driver) {

            $driver = User::where('role', 'driver')
                ->findOrFail($driver);

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'employee_id' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('users', 'employee_id')->ignore($driver->id),
                ],
                'phone' => ['nullable', 'string', 'max:30'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($driver->id),
                ],
                'status' => ['required', Rule::in(['active', 'inactive'])],
            ]);

            $driver->update([
                'name' => $validated['name'],
                'employee_id' => $validated['employee_id'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'is_active' => $validated['status'] === 'active',
            ]);

            if (!$driver->is_active) {
                $driver->tokens()->delete();
            }

            return redirect()
                ->route('hr.drivers.index')
                ->with('success', 'Data driver berhasil diperbarui.');

        })->name('drivers.update');


        // HR reset password driver
        Route::put('/drivers/{driver}/reset-password', function (Request $request, $driver) {

            $driver = User::where('role', 'driver')->findOrFail($driver);

            $validated = $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $driver->update([
                'password' => Hash::make($validated['password']),
            ]);

            $driver->tokens()->delete();

            return redirect()
                ->route('hr.drivers.index')
                ->with('success', 'Password driver berhasil direset. Silakan berikan password baru kepada driver.');

        })->name('drivers.reset-password');


        // Delete Driver
        Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])
            ->name('drivers.destroy');


        /*
        |--------------------------------------------------------------------------
        | Vehicle Management
        |--------------------------------------------------------------------------
        */

        Route::get('/vehicles', [VehicleController::class, 'index'])
            ->name('vehicles.index');

        Route::post('/vehicles', [VehicleController::class, 'store'])
            ->name('vehicles.store');

        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])
            ->name('vehicles.update');

        Route::patch('/vehicles/{vehicle}/status', [VehicleController::class, 'changeStatus'])
            ->name('vehicles.change-status');

        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])
            ->name('vehicles.destroy');


        /*
        |--------------------------------------------------------------------------
        | Trip Management
        |--------------------------------------------------------------------------
        */

        Route::get('/trips', [TripManagementController::class, 'index'])
            ->name('trips.index');

        Route::post('/trips', [TripManagementController::class, 'store'])
            ->name('trips.store');

        Route::put('/trips/{trip}', [TripManagementController::class, 'update'])
            ->name('trips.update');

        Route::patch('/trips/{trip}/update-times', [TripManagementController::class, 'updateTimes'])
            ->name('trips.update-times');

        Route::delete('/trips/{trip}', [TripManagementController::class, 'destroy'])
            ->name('trips.destroy');

        Route::patch('/trips/{trip}/cancel', [TripManagementController::class, 'cancel'])
            ->name('trips.cancel');

        Route::patch('/trips/{trip}/simulate/{action}', [TripManagementController::class, 'simulateDriver'])
            ->name('trips.simulate');


        /*
        |--------------------------------------------------------------------------
        | Trip History
        |--------------------------------------------------------------------------
        */

        Route::get('/trip-history', [TripHistoryController::class, 'index'])
            ->name('trip-history');

        Route::patch('/trip-history/{trip}/restore', [TripHistoryController::class, 'restore'])
            ->name('trip-history.restore');

        Route::delete('/trip-history/{trip}/permanent', [TripHistoryController::class, 'forceDelete'])
            ->name('trip-history.force-delete');


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications');

        Route::get('/notifications/data', [NotificationController::class, 'data'])
            ->name('notifications.data');

        // Tandai semua notifikasi sebagai sudah dibaca
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.read-all');

        // Tandai satu notifikasi sebagai sudah dibaca
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->whereNumber('id')
            ->name('notifications.read');

        // Hapus semua notifikasi
        Route::delete('/notifications/all', [NotificationController::class, 'destroyAll'])
            ->name('notifications.destroy-all');

        // Hapus satu notifikasi
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
            ->whereNumber('id')
            ->name('notifications.destroy');

    });
