<?php

namespace Database\Seeders;

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
        User::factory()->count(50)->create([
            'user_type' => '0',
        ]);

        Location::factory()->count(50)->create();

        Permit::factory()->count(500)->create();
    }
}
