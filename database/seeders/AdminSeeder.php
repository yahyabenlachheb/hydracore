<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * The first super admin. Set ADMIN_PASSWORD in .env to choose the password; otherwise a random
     * one is generated and printed once. Does nothing if the account already exists.
     */
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');

        if (User::where('username', $username)->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

        User::create([
            'name' => 'Super Admin',
            'username' => $username,
            'email' => env('ADMIN_EMAIL', 'admin@hydracore.test'),
            'password' => $password,
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Super admin created: {$username} / {$password} (shown only once)");
        }
    }
}
