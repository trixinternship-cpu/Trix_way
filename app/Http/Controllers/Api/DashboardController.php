<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        abort_unless($request->user()->role === 'hr', 403);

        return response()->json([
            'drivers' => [
                'total' => User::where('role', 'driver')->count(),
                'active' => User::where('role', 'driver')->where('is_active', true)->count(),
                'inactive' => User::where('role', 'driver')->where('is_active', false)->count(),
            ],
            'vehicles' => [
                'total' => Vehicle::count(),
                'available' => Vehicle::where('status', 'available')->count(),
                'in_use' => Vehicle::where('status', 'in_use')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
                'inactive' => Vehicle::where('status', 'inactive')->count(),
            ],
            'trips' => [
                'active' => Trip::where('status', 'active')->count(),
                'completed_today' => Trip::where('status', 'completed')
                    ->whereDate('end_time', today())->count(),
            ],
        ]);
    }
}
