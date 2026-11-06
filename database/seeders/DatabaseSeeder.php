<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemConfiguration;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            ClassesSeeder::class,
            RoomsSeeder::class,
            UsersSeeder::class,
        ]);

        // Inisialisasi System Configuration untuk fitur Lock Schedule Waka Kurikulum
        SystemConfiguration::firstOrCreate([], [
            'active_cycle' => 'Putaran 1',
            'is_schedule_locked' => false,
            'updated_by' => null,
        ]);
    }
}