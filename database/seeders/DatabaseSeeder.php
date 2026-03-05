<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Location;
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

        User::query()->updateOrCreate(
            ['username' => 'JMontiano'],
            ['first_name' => 'Jeff', 'last_name' => 'Montiano', 'user_type' => '1']
        );

        User::query()->updateOrCreate(
            ['username' => 'ATrinidad'],
            ['first_name' => 'Adam', 'last_name' => 'Trinidad', 'user_type' => '1']
        );

        User::query()->updateOrCreate(
            ['username' => 'IGuno'],
            ['first_name' => 'Iverson', 'last_name' => 'Guno', 'user_type' => '1']
        );

        User::query()->updateOrCreate(
            ['username' => 'RRoque'],
            ['first_name' => 'Raniel', 'last_name' => 'Roque', 'user_type' => '1']
        );

        User::query()->updateOrCreate(
            ['username' => 'JSantos'],
            ['first_name' => 'Jenny', 'last_name' => 'Santos', 'user_type' => '1']
        );
    }
}
