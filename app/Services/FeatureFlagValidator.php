<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class FeatureFlagValidator
{
    private const PROTECTED_FLAGS = ['admin'];

    public function validateFlag(string $flag): void
    {
        if (! array_key_exists($flag, $this->getDefinedFlags())) {
            throw new InvalidArgumentException("Unknown feature flag: {$flag}");
        }
    }

    public function validateNotProtected(string $flag): void
    {
        if (in_array($flag, self::PROTECTED_FLAGS, true)) {
            throw new RuntimeException("Cannot override protected flag: {$flag}");
        }
    }

    public function isProtected(string $flag): bool
    {
        return in_array($flag, self::PROTECTED_FLAGS, true);
    }

    public function getDefinedFlags(): array
    {
        $flags = [];

        foreach (config('features', []) as $key => $value) {
            if (is_array($value) && array_key_exists('enabled', $value)) {
                $flags[$key] = (bool) $value['enabled'];
            }
        }

        return $flags;
    }
}
