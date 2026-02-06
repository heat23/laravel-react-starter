<?php

return [
    'token' => env('HEALTH_CHECK_TOKEN'),
    'allowed_ips' => env('HEALTH_CHECK_IPS', '127.0.0.1'),
    'disk_warning_percent' => (int) env('DISK_USAGE_WARNING_PERCENT', 80),
    'disk_critical_percent' => (int) env('DISK_USAGE_CRITICAL_PERCENT', 95),
    'queue_warning_threshold' => (int) env('QUEUE_WARNING_THRESHOLD', 1000),
    'audit_retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 90),
];
