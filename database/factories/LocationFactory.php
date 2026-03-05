<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        
        return [
            'name' => "Location {$counter}",
            'is_disabled' => false,
        ];
    }
}
