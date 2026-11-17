<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LiveMonitoringController extends Controller
{
    /**
     * Menampilkan halaman Live Monitoring HR.
     */
    public function index(): View
    {
        return view('hr.live-monitoring');
    }

    /**
     * Mengambil data seluruh perjalanan aktif beserta GPS.
     */
    public function data(): JsonResponse
    {
        $trips = Trip::with([
            'driver:id,name,employee_id,phone',
            'vehicle:id,plate_number',
            'trackingSession',
            'stops',
        ])
            ->where('status', 'active')
            ->latest()
            ->get();

        return response()->json([
            'data' => $trips,
        ]);
    }
}
