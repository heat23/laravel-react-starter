<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyticsGateway
{
    private const COLLECT_URL = 'https://www.google-analytics.com/mp/collect';

    /**
     * Blocklist of metadata keys that must never reach GA4.
     * AuditService callers occasionally include these for internal logging;
     * strip them here as a defence-in-depth layer (GDPR/CCPA).
     * Covers: auth identifiers, PII fields, financial data, location, device.
     */
    private const PII_KEYS = [
        // Auth / credentials
        'email', 'phone', 'password', 'token', 'secret', 'api_key',
        // Network / device identifiers
        'ip', 'user_agent', 'device_id', 'session_id',
        // Personal identifiers
        'name', 'address', 'ssn', 'dob', 'zip_code', 'postal_code',
        // Financial
        'credit_card', 'cvv', 'account_number', 'transaction_id',
        // Location
        'latitude', 'longitude',
        // Internal references that shouldn't leave the system
        'customer_id',
    ];

    /**
     * Send an event to GA4 via the Measurement Protocol.
     * Fire-and-forget — never throws; failures are logged as warnings.
     * No-op when GA4 is not configured (local / test environments).
     */
    public function send(string $eventName, array $params, int $userId): void
    {
        if (! config('services.ga4.enabled') || ! config('services.ga4.measurement_id')) {
            return;
        }

        $payload = [
            'client_id' => "server_{$userId}",
            'user_id' => (string) $userId,
            'events' => [
                [
                    // GA4 event names must use underscores — dots are not valid
                    'name' => str_replace('.', '_', $eventName),
                    'params' => array_merge($this->sanitizeParams($params), [
                        // Required by GA4 Measurement Protocol for engagement metrics
                        'engagement_time_msec' => 1,
                    ]),
                ],
            ],
        ];

        try {
            // GA4 Measurement Protocol requires measurement_id and api_secret as
            // URL query parameters — this is the documented API design, not a choice.
            // The connection is HTTPS so the secret is encrypted in transit.
            // ref: https://developers.google.com/analytics/devguides/collection/protocol/ga4/sending-events
            Http::timeout(5)->post(
                self::COLLECT_URL.'?'.http_build_query([
                    'measurement_id' => config('services.ga4.measurement_id'),
                    'api_secret' => config('services.ga4.api_secret'),
                ]),
                $payload
            );
        } catch (\Exception $e) {
            Log::warning('AnalyticsGateway: failed to send event', [
                'event' => $eventName,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Strip PII-adjacent keys from event params before sending to GA4.
     * Defence-in-depth: AuditService callers may include these for internal
     * logging but they must never reach a third-party analytics endpoint.
     */
    private function sanitizeParams(array $params): array
    {
        return array_filter(
            $params,
            fn (string $key) => ! in_array(strtolower($key), self::PII_KEYS, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
