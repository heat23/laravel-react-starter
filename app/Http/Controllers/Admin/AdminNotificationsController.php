<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Enums\AuditEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSendNotificationRequest;
use App\Jobs\BroadcastAnnouncementJob;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationsController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function __invoke(): Response
    {
        $stats = Cache::remember(AdminCacheKey::NOTIFICATIONS_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            $agg = DB::table('notifications')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as read_count')
                ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as sent_last_7d', [now()->subDays(7)])
                ->first();

            $total = (int) $agg->total;
            $read = (int) $agg->read_count;

            return [
                'total_sent' => $total,
                'unread' => $total - $read,
                'read' => $read,
                'read_rate' => $total > 0 ? round(($read / $total) * 100, 1) : 0,
                'sent_last_7d' => (int) $agg->sent_last_7d,
                'by_type' => DB::table('notifications')
                    ->select('type', DB::raw('COUNT(*) as count'))
                    ->groupBy('type')
                    ->get()
                    ->map(fn ($row) => [
                        'type' => class_basename($row->type),
                        'count' => (int) $row->count,
                    ])
                    ->toArray(),
            ];
        });

        $volumeChart = Cache::remember(AdminCacheKey::NOTIFICATIONS_VOLUME->value, AdminCacheKey::CHART_TTL, function () {
            return DB::table('notifications')
                ->select(QueryHelper::dateExpression('created_at'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(14))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($row) => ['date' => $row->date, 'count' => (int) $row->count])
                ->toArray();
        });

        $recentNotifications = DB::table('notifications')
            ->leftJoin('users', 'users.id', '=', 'notifications.notifiable_id')
            ->select(
                'notifications.id',
                'notifications.type',
                'notifications.data',
                'notifications.read_at',
                'notifications.created_at',
                'users.name as user_name',
                'users.email as user_email',
            )
            ->where('notifications.notifiable_type', 'App\\Models\\User')
            ->orderBy('notifications.created_at', 'desc')
            ->limit(config('pagination.api.notifications', 20))
            ->get()
            ->map(function ($row) {
                $data = json_decode($row->data, true) ?? [];

                return [
                    'id' => $row->id,
                    'type' => class_basename($row->type),
                    'subject' => $data['subject'] ?? null,
                    'user_name' => $row->user_name,
                    'user_email' => $row->user_email,
                    'read_at' => $row->read_at,
                    'created_at' => $row->created_at,
                ];
            })
            ->toArray();

        return Inertia::render('Admin/Notifications/Dashboard', [
            'stats' => $stats,
            'volume_chart' => $volumeChart,
            'recent_notifications' => $recentNotifications,
        ]);
    }

    public function send(AdminSendNotificationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $query = User::query()->whereNull('deleted_at');

        if ($validated['recipient'] === 'admins') {
            $query->where('is_admin', true);
        }

        $count = $query->count();

        BroadcastAnnouncementJob::dispatch(
            recipient: $validated['recipient'],
            subject: $validated['subject'],
            body: $validated['body'],
            sentBy: $request->user()->name,
        );

        $this->cacheManager->invalidateNotifications();

        $this->auditService->log(AuditEvent::ADMIN_NOTIFICATION_SENT, [
            'subject' => $validated['subject'],
            'recipient' => $validated['recipient'],
            'count' => $count,
        ]);

        return back()->with('success', "Announcement queued for {$count} user(s).");
    }
}
