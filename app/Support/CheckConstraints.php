<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Adds CHECK constraints in migrations. The schema builder has no API for them, and SQLite (the test
 * database) can't add one to an existing table, so they only exist on MariaDB/MySQL.
 */
final class CheckConstraints
{
    /**
     * @param  array<string, string>  $checks  constraint name => condition
     */
    public static function add(string $table, array $checks): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach ($checks as $name => $condition) {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$condition})");
        }
    }
}
