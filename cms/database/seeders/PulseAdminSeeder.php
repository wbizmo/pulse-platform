<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PulseAdminSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Pulse Super Admin',
                'email' => 'admin@pulse.test',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Pulse Manager',
                'email' => 'manager@pulse.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Pulse Editor',
                'email' => 'editor@pulse.test',
                'password' => Hash::make('password'),
                'role' => 'editor',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Pulse Author',
                'email' => 'author@pulse.test',
                'password' => Hash::make('password'),
                'role' => 'author',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
