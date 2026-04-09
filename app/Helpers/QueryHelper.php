<?php

namespace App\Helpers;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class QueryHelper
{
    /**
     * Escape LIKE wildcards (% and _) in a search string.
     * Uses pipe as escape character to avoid backslash interpretation differences between MySQL and SQLite.
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['|', '%', '_'], ['||', '|%', '|_'], $value);
    }

    /**
     * Apply a LIKE condition with properly escaped wildcards.
     * Works with both MySQL and SQLite by using pipe as the ESCAPE character.
     */
    public static function whereLike(Builder|\Illuminate\Database\Eloquent\Builder $query, string $column, string $value, string $boolean = 'and'): void
    {
        $escaped = self::escapeLike($value);
        $query->whereRaw("{$column} LIKE ? ESCAPE '|'", ["%{$escaped}%"], $boolean);
    }

    /**
     * Build a COALESCE(<column>, '<fallback>') expression for use in a query select.
     * COALESCE is ANSI SQL — compatible with MySQL and SQLite.
     *
     * @param  string  $column  Column reference, e.g. 'users.name' or 'name'.
     * @param  string  $fallback  Literal fallback string (single quotes will be SQL-escaped).
     * @param  string|null  $alias  Optional AS alias appended to the expression.
     *
     * @throws \InvalidArgumentException When $column contains characters outside [A-Za-z0-9_.].
     */
    public static function coalesceExpr(string $column, string $fallback, ?string $alias = null): Expression
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: {$column}");
        }

        if ($alias !== null && ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $alias)) {
            throw new \InvalidArgumentException("Invalid alias: {$alias}");
        }

        $escaped = str_replace("'", "''", $fallback);
        $sql = "COALESCE({$column}, '{$escaped}')";

        if ($alias !== null) {
            $sql .= " as {$alias}";
        }

        return DB::raw($sql);
    }

    /**
     * Get a database-aware DATE() expression for grouping by day.
     * Works with both MySQL (DATE()) and SQLite (date()).
     *
     * @param  string  $column  Must be a valid column identifier (letters, digits, underscores, dots only).
     */
    public static function dateExpression(string $column): Expression
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: {$column}");
        }

        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => DB::raw("date({$column}) as date"),
            default => DB::raw("DATE({$column}) as date"),
        };
    }
}
