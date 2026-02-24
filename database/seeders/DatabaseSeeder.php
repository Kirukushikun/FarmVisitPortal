<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(11)->create();

                User::factory()->create([
            'first_name' => 'Jeff',
            'last_name' => 'Montiano',
            'username' => 'JMontiano',
            'user_type' => '1',
        ]);

        User::factory()->create([
            'first_name' => 'Adam',
            'last_name' => 'Trinidad',
            'username' => 'ATrinidad',
            'user_type' => '1',
        ]);

        User::factory()->create([
            'first_name' => 'Iverson',
            'last_name' => 'Guno',
            'username' => 'IGuno',
            'user_type' => '1',
        ]);

        User::factory()->create([
            'first_name' => 'Raniel',
            'last_name' => 'Roque',
            'username' => 'RRoque',
            'user_type' => '1',
        ]);
        
        User::factory()->create([
            'first_name' => 'Jenny',
            'last_name' => 'Santos',
            'username' => 'JSantos',
            'user_type' => '1',
        ]);
    }
}
