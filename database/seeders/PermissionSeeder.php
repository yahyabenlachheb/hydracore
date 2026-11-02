<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\SystemPermissions;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Creates every system permission the app checks. Safe to run repeatedly.
     */
    public function run(): void
    {
        foreach (SystemPermissions::ALL as $slug => $name) {
            Permission::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
