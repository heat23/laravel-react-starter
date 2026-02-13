<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class QueryHelper
{
    /**
     * Get a database-aware DATE() expression for grouping by day.
     * Works with both MySQL (DATE()) and SQLite (date()).
     *
     * @param  string  $column  Must be a valid column identifier (letters, digits, underscores, dots only).
     */
    public static function dateExpression(string $column): \Illuminate\Database\Query\Expression
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: {$column}");
        }

        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => DB::raw("date({$column}) as date"),
            default => DB::raw("DATE({$column}) as date"),
        };
    }
}
