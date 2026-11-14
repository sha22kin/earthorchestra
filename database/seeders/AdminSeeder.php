<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::exists()) {
            return;
        }

        $email = env('ADMIN_EMAIL', 'admin@earthorchestra.test');

        User::create([
            'name' => env('ADMIN_NAME', 'Site Administrator'),
            'email' => $email,
            'password' => env('ADMIN_PASSWORD', 'change-me-now'),
            'role' => 'admin',
        ]);

        $this->command?->warn("Admin created: {$email} - change the password after first sign-in.");
    }
}
