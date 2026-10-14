<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    // Creates the first Super Admin so you can log in.
    // Set ADMIN_EMAIL and ADMIN_PASSWORD in .env, then: php artisan db:seed --class=AdminUserSeeder
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command->error('Set ADMIN_EMAIL and ADMIN_PASSWORD in .env first.');
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make($password),
                'type'     => 'Super Admin',
                'active'   => true,
            ]
        );

        $this->command->info("Super Admin ready: {$email}");
    }
}
