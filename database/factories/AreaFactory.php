<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locationId = Location::query()->inRandomOrder()->value('id');
        
        return [
            'location_id' => $locationId ?: 1,
            'name' => ucfirst($this->faker->words(2, true)),
            'is_disabled' => $this->faker->boolean(20), // 20% chance of being disabled
        ];
    }
}
