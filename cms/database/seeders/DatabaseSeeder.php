<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PulseSystemSeeder::class,
            PulseThemeSeeder::class,
            PulsePluginSeeder::class,
        ]);
    }
}
