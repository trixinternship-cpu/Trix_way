<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripHistoryController extends Controller
{
    /**
     * Menampilkan trip Completed dan Cancelled dari database.
     */
    public function index(): View
    {
        $trips = Trip::query()
            ->with([
                'driver:id,name,employee_id',
                'vehicle:id,plate_number,brand,model',
                'stops:id,trip_id,sequence,stop_type,destination,arrival_time,departure_time,status',
                'locations:id,trip_id,latitude,longitude,recorded_at',
            ])
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderByDesc('end_time')
            ->orderByDesc('updated_at')
            ->get();

        // Jarak dihitung dari rangkaian titik GPS yang benar-benar tersimpan.
        $trips->each(function (Trip $trip): void {
            $locations = $trip->locations
                ->sortBy('recorded_at')
                ->values();

            $distanceKm = 0.0;

            for ($i = 1; $i < $locations->count(); $i++) {
                $previous = $locations[$i - 1];
                $current = $locations[$i];

                $distanceKm += $this->distanceBetweenCoordinates(
                    (float) $previous->latitude,
                    (float) $previous->longitude,
                    (float) $current->latitude,
                    (float) $current->longitude
                );
            }

            $trip->setAttribute(
                'distance_km',
                $distanceKm > 0 ? round($distanceKm, 2) : null
            );
        });

        $deletedTrips = Trip::onlyTrashed()
            ->with(['driver:id,name,employee_id', 'vehicle:id,plate_number,brand,model'])
            ->whereIn('status', ['completed', 'cancelled', 'pending'])
            ->orderByDesc('deleted_at')
            ->get();

        return view('trips.trip-history', compact('trips', 'deletedTrips'));
    }


    /** Restore trip dari Recently Deleted. */
    public function restore(int $trip): RedirectResponse
    {
        $deletedTrip = Trip::onlyTrashed()->findOrFail($trip);

        if ($deletedTrip->deleted_at->lt(now()->subDays(7))) {
            return back()->with('error', 'Masa pemulihan 7 hari sudah berakhir.');
        }

        $deletedTrip->restore();

        return redirect()->route('hr.trip-history')
            ->with('success', "Trip {$deletedTrip->trip_code} berhasil dipulihkan.");
    }

    /** Hapus permanen hanya dari daftar Recently Deleted. */
    public function forceDelete(int $trip): RedirectResponse
    {
        $deletedTrip = Trip::onlyTrashed()->findOrFail($trip);
        $code = $deletedTrip->trip_code;
        $deletedTrip->forceDelete(); // FK cascade menghapus detail trip terkait.

        return redirect()->route('hr.trip-history')
            ->with('success', "Trip {$code} dihapus permanen.");
    }

    /**
     * Menghitung jarak antar titik GPS menggunakan rumus Haversine.
     */
    private function distanceBetweenCoordinates(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2
    ): float {
        $earthRadiusKm = 6371.0;

        $latFrom = deg2rad($latitude1);
        $latTo = deg2rad($latitude2);
        $latDelta = deg2rad($latitude2 - $latitude1);
        $lonDelta = deg2rad($longitude2 - $longitude1);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(max(0, 1 - $a)));

        return $earthRadiusKm * $c;
    }
}
