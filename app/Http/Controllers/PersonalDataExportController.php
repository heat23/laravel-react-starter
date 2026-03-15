<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SocialAccount;
use App\Models\UserSetting;
use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class PersonalDataExportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->loadMissing(['settings', 'socialAccounts', 'webhookEndpoints', 'tokens']);

        $settings = [];
        /** @var UserSetting $s */
        foreach ($user->settings as $s) {
            $settings[] = ['key' => $s->key, 'value' => $s->value];
        }

        $socialAccounts = [];
        /** @var SocialAccount $sa */
        foreach ($user->socialAccounts as $sa) {
            $socialAccounts[] = [
                'provider' => $sa->provider,
                'provider_id' => $sa->provider_id,
                'created_at' => $sa->created_at?->toISOString(),
            ];
        }

        $apiTokens = [];
        /** @var PersonalAccessToken $t */
        foreach ($user->tokens as $t) {
            $apiTokens[] = [
                'name' => $t->name,
                'abilities' => $t->abilities,
                'last_used_at' => $t->last_used_at?->toISOString(),
                'created_at' => $t->created_at?->toISOString(),
            ];
        }

        $webhookEndpoints = [];
        /** @var WebhookEndpoint $we */
        foreach ($user->webhookEndpoints as $we) {
            $webhookEndpoints[] = [
                'url' => $we->url,
                'events' => $we->events,
                'is_active' => $we->is_active,
                'created_at' => $we->created_at?->toISOString(),
            ];
        }

        $auditLogLimit = 1000;
        $auditLogTotalCount = AuditLog::byUser($user->id)->count();
        $auditLogs = [];
        /** @var AuditLog $al */
        foreach (
            AuditLog::byUser($user->id)
                ->select(['event', 'ip', 'user_agent', 'metadata', 'created_at'])
                ->orderByDesc('created_at')
                ->limit($auditLogLimit)
                ->get() as $al
        ) {
            $auditLogs[] = [
                'event' => $al->event,
                'ip' => $al->ip,
                'user_agent' => $al->user_agent,
                'metadata' => $al->metadata,
                'created_at' => $al->created_at?->toISOString(),
            ];
        }

        $data = [
            'exported_at' => now()->toISOString(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->getAttribute('email_verified_at')?->toISOString(),
                'created_at' => $user->created_at?->toISOString(),
                'last_login_at' => $user->getAttribute('last_login_at')?->toISOString(),
                'signup_source' => $user->signup_source,
            ],
            'settings' => $settings,
            'social_accounts' => $socialAccounts,
            'api_tokens' => $apiTokens,
            'webhook_endpoints' => $webhookEndpoints,
            'audit_logs' => $auditLogs,
            'audit_logs_total_count' => $auditLogTotalCount,
            'audit_logs_truncated' => $auditLogTotalCount > $auditLogLimit,
        ];

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="personal-data-export.json"');
    }
}
