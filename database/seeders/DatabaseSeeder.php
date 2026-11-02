<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * What a fresh install needs. Sample data is separate: php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
