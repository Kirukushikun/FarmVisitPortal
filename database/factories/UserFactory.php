<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        $firstLetter = strtoupper(substr($firstName, 0, 1));
        $lastNameWords = preg_split('/\s+/', trim($lastName));
        $firstWordOfLastName = $lastNameWords[0] ?? '';
        $baseUsername = $firstLetter . $firstWordOfLastName;

        $username = $baseUsername;
        $counter = 0;
        while (User::where('username', $username)->exists()) {
            $counter++;
            $username = $baseUsername . $counter;
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'user_type' => 0,
            'password' => static::$password ??= Hash::make('brookside25'),
        ];
    }

}
