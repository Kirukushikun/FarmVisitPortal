<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(11)->create();

        $adminDefaults = [
            'password' => Hash::make('brookside25'),
            'user_type' => '1',
        ];

        $admins = [
            ['username' => 'JMontiano', 'first_name' => 'Jeff', 'last_name' => 'Montiano'],
            ['username' => 'ATrinidad', 'first_name' => 'Adam', 'last_name' => 'Trinidad'],
            ['username' => 'IGuno', 'first_name' => 'Iverson', 'last_name' => 'Guno'],
            ['username' => 'RRoque', 'first_name' => 'Raniel', 'last_name' => 'Roque'],
            ['username' => 'JSantos', 'first_name' => 'Jenny', 'last_name' => 'Santos'],
        ];

        foreach ($admins as $admin) {
            $user = User::query()->where('username', $admin['username'])->first();

            if (! $user) {
                User::query()->create($admin + $adminDefaults);
                continue;
            }

            $user->update([
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'user_type' => '1',
            ]);
        }
    }
}
