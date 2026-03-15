<?php

return [
    'token' => env('HEALTH_CHECK_TOKEN'),
    'allow_query_token' => env('HEALTH_ALLOW_QUERY_TOKEN', false),
    'allowed_ips' => env('HEALTH_CHECK_IPS', '127.0.0.1'),
    'disk_warning_percent' => (int) env('DISK_USAGE_WARNING_PERCENT', 80),
    'disk_critical_percent' => (int) env('DISK_USAGE_CRITICAL_PERCENT', 95),
    'queue_warning_threshold' => (int) env('QUEUE_WARNING_THRESHOLD', 1000),
    'audit_retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 90),

    'alert_thresholds' => [
        'failed_jobs' => (int) env('HEALTH_ALERT_FAILED_JOBS', 10),
        'webhook_failure_rate' => (int) env('HEALTH_ALERT_WEBHOOK_FAILURE_RATE', 25),
    ],
];
