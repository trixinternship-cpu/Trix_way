<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TripNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Halaman Notifications HR.
     */
    public function index(): View
    {
        return view('notifications.index');
    }

    /**
     * Mengambil data notifikasi milik HR yang login.
     */
    public function data(Request $request): JsonResponse
    {
        $notifications = TripNotification::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'trip.driver',
                'trip.vehicle',
                'trip.trackingSession',
            ])
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($notification) {
                $trip = $notification->trip;
                $tracking = $trip?->trackingSession;

                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'category' => $notification->category ?? 'system',
                    'is_read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at,
                    'created_at_label' => $notification->created_at
                        ? $notification->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB'
                        : null,
                    'read_at' => $notification->read_at,
                    'trip_code' => $trip?->trip_code,
                    'driver_name' => $trip?->driver?->name,
                    'vehicle_plate' => $trip?->vehicle?->plate_number,
                    'last_latitude' => $tracking?->latitude,
                    'last_longitude' => $tracking?->longitude,
                ];
            });

        $total = $notifications->count();
        $unread = $notifications->where('is_read', false)->count();

        return response()->json([
            'notifications' => $notifications->values(),
            'statistics' => [
                'total' => $total,
                'unread' => $unread,
                'read' => $total - $unread,
            ],
        ]);
    }

    /**
     * Tandai satu notifikasi sudah dibaca.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = TripNotification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        if (!$notification->read_at) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Tandai seluruh notifikasi sudah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = TripNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Hapus satu notifikasi milik user yang sedang login.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = TripNotification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }

    /**
     * Hapus seluruh notifikasi milik user yang sedang login.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $deleted = TripNotification::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => 'All notifications deleted successfully.',
        ]);
    }
}
