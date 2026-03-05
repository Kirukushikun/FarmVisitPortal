<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Location;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Seed large test data for local/dev dashboards.
     */
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        $targetRegularUsers = 50;
        $targetLocations = 50;
        $targetPermits = 500;

        // Only create regular users (user_type = 0)
        $existingRegularUsers = User::query()->where('user_type', '0')->count();
        if ($existingRegularUsers < $targetRegularUsers) {
            User::factory()->count($targetRegularUsers - $existingRegularUsers)->create([
                'user_type' => '0',
            ]);
        }

        // Use deterministic names + updateOrCreate so seeder can be run repeatedly.
        for ($i = 1; $i <= $targetLocations; $i++) {
            $location = Location::query()->updateOrCreate(
                ['name' => 'Test Farm ' . $i],
                ['is_disabled' => false]
            );

            // Seed a fixed set of areas per location (avoid random counts so runs are stable)
            for ($j = 1; $j <= 5; $j++) {
                Area::query()->updateOrCreate(
                    ['location_id' => $location->id, 'name' => 'Area ' . $j],
                    ['is_disabled' => false]
                );
            }
        }

        $existingPermits = Permit::query()->count();
        if ($existingPermits < $targetPermits) {
            Permit::factory()->count($targetPermits - $existingPermits)->create();
        }
    }
}
