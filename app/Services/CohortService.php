<?php

namespace App\Services;

class CohortService
{
    /**
     * Determine whether a seed falls in the variant cohort.
     *
     * Cohort assignment is deterministic: the same seed always yields the same result.
     * Odd CRC32 values (mod 2 === 1) are cohort 1 (variant); even values are cohort 0 (control).
     */
    public function isVariantCohort(string $seed): bool
    {
        return (abs(crc32($seed)) % 2) === 1;
    }
}
