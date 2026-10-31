<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Driver-aware raw SQL fragments (production runs MySQL, tests run SQLite).
 */
class Sql
{
    /**
     * Expression formatting a date/datetime column as "YYYY-MM".
     */
    public static function yearMonth(string $column): string
    {
        return match (DB::getDriverName()) {
            'mysql', 'mariadb' => "DATE_FORMAT({$column}, '%Y-%m')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            default => throw new RuntimeException('Sql::yearMonth does not support driver ['.DB::getDriverName().'].'),
        };
    }
}
