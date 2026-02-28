<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @group Notifications
 *
 * Manage in-app notifications.
 */
class NotificationController extends Controller
{
    public function __construct()
    {
        abort_unless(feature_enabled('notifications', auth()->user()), 404);
    }

    /**
     * List notifications
     *
     * @authenticated
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(config('pagination.api.notifications', 20));

        return response()->json($notifications);
    }

    /**
     * Mark as read
     *
     * @authenticated
     *
     * @urlParam id string required The notification ID. Example: 550e8400-e29b-41d4-a716-446655440000
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        Cache::forget("user:{$request->user()->id}:unread_notif_count");

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all as read
     *
     * @authenticated
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        Cache::forget("user:{$request->user()->id}:unread_notif_count");

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete notification
     *
     * @authenticated
     *
     * @urlParam id string required The notification ID. Example: 550e8400-e29b-41d4-a716-446655440000
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        Cache::forget("user:{$request->user()->id}:unread_notif_count");

        return response()->json(['message' => 'Notification deleted']);
    }
}
