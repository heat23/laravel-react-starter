<?php

namespace App\Enums;

enum LifecycleStage: string
{
    case VISITOR = 'visitor';
    case TRIAL = 'trial';
    case ACTIVATED = 'activated';
    case PAYING = 'paying';
    case AT_RISK = 'at_risk';
    case CHURNED = 'churned';
    case EXPANSION = 'expansion';

    public function label(): string
    {
        return match ($this) {
            self::VISITOR => 'Visitor',
            self::TRIAL => 'Trial',
            self::ACTIVATED => 'Activated',
            self::PAYING => 'Paying',
            self::AT_RISK => 'At Risk',
            self::CHURNED => 'Churned',
            self::EXPANSION => 'Expansion',
        };
    }

    /** Ordered stages for funnel display */
    public static function funnelOrder(): array
    {
        return [
            self::VISITOR,
            self::TRIAL,
            self::ACTIVATED,
            self::PAYING,
            self::EXPANSION,
            self::AT_RISK,
            self::CHURNED,
        ];
    }
}
