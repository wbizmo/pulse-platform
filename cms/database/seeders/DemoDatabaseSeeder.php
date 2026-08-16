<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo data is forbidden in production.');
        }

        $this->call([DatabaseSeeder::class, PulseAdminSeeder::class]);
    }
}
