<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Location;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    /**
     * Seed large test data for local/dev dashboards.
     */
    public function run(): void
    {
        // Clear existing test data to avoid conflicts
        DB::table('permits')->delete();
        DB::table('areas')->delete();
        DB::table('locations')->delete();
        
        // Reset auto-increment for clean start
        DB::statement('ALTER TABLE locations AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE areas AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE permits AUTO_INCREMENT = 1');
        
        // Only create regular users (user_type = 0), avoid admin users from DatabaseSeeder
        User::factory()->count(50)->create([
            'user_type' => '0',
        ]);

        Location::factory()->count(50)->create();

        // Create areas for each location
        Location::all()->each(function ($location) {
            Area::factory()->count(rand(3, 8))->create([
                'location_id' => $location->id,
            ]);
        });

        Permit::factory()->count(500)->create();
    }
}
