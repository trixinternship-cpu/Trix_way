<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripHistoryController extends Controller
{
    /**
     * Driver menyembunyikan riwayat perjalanan miliknya.
     * Data perjalanan utama tidak dihapus.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        // Hanya driver yang boleh menghapus riwayat miliknya.
        abort_unless($user && $user->role === 'driver', 403, 'Akses ditolak.');

        // Pastikan perjalanan milik driver yang sedang login.
        $trip = Trip::where('id', $id)
            ->where('driver_id', $user->id)
            ->firstOrFail();

        // Hanya perjalanan selesai yang boleh disembunyikan.
        if ($trip->status !== 'completed') {
            return response()->json([
                'message' => 'Hanya perjalanan yang sudah selesai yang dapat dihapus dari riwayat.',
            ], 422);
        }

        // Simpan ke daftar riwayat tersembunyi milik driver.
        DB::table('driver_hidden_trips')->insertOrIgnore([
            'driver_id' => $user->id,
            'trip_id' => $trip->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat perjalanan berhasil dihapus dari daftar Driver.',
        ]);
    }
}
