<?php

return [
    'churn_rate' => ['warning' => 10, 'critical' => 20],
    'mrr_drop_percent' => ['warning' => 10, 'critical' => 25],
    'trial_conversion' => ['warning_below' => 20, 'critical_below' => 10],
    // % of trial/new users who complete a meaningful activation event (e.g. first key action)
    'activation_rate' => ['warning_below' => 40, 'critical_below' => 20],
    // Net Revenue Retention (%): 100 = neutral, >100 = expansion, <100 = net churn
    'net_revenue_retention' => ['warning_below' => 100, 'critical_below' => 90],
];
