<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class QueryHelper
{
    /**
     * Escape LIKE wildcards (% and _) in a search string.
     * Uses backslash as escape character, compatible with MySQL (default) and SQLite (via ESCAPE clause).
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * Apply a LIKE condition with properly escaped wildcards.
     * Works with both MySQL and SQLite by adding an explicit ESCAPE clause.
     */
    public static function whereLike(\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query, string $column, string $value, string $boolean = 'and'): void
    {
        $escaped = self::escapeLike($value);
        $query->whereRaw("{$column} LIKE ? ESCAPE '\\'", ["%{$escaped}%"], $boolean);
    }

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
