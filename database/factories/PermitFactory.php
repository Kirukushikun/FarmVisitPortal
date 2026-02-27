<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Permit;
use App\Models\User;
use RuntimeException;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permit>
 */
class PermitFactory extends Factory
{
    protected $model = Permit::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            0, 0, 0,
            1, 1, 1, 1,
            2, 2,
            3,
        ]);

        $dateOfVisit = $this->faker->dateTimeBetween(now()->subMonths(3)->startOfDay(), now()->endOfDay());

        $destinationId = $this->randomLocationId();
        $farmId = $this->randomLocationId(exceptId: $destinationId);
        $previousFarmId = $this->faker->boolean(40) ? $this->randomLocationId(exceptId: $farmId) : null;

        $createdBy = $this->randomUserId(1);
        $receivedBy = ($status === 2) ? $this->randomUserId(0) : null;

        $previousFarmDate = null;
        if ($previousFarmId !== null) {
            $previousFarmDate = $this->faker->dateTimeBetween('-6 months', 'yesterday');
        }

        return [
            'permit_id' => null,
            'area' => ucfirst($this->faker->words(2, true)),
            'farm_location_id' => $farmId,
            'names' => $this->faker->name() . "\n" . $this->faker->name(),
            'area_to_visit' => ucfirst($this->faker->words(4, true)),
            'destination_location_id' => $destinationId,
            'date_of_visit' => $dateOfVisit,
            'expected_duration_seconds' => $this->faker->numberBetween(15 * 60, 6 * 3600),
            'previous_farm_location_id' => $previousFarmId,
            'date_of_visit_previous_farm' => $previousFarmDate,
            'purpose' => $this->faker->boolean(70) ? $this->faker->sentence(8) : null,
            'status' => $status,
            'created_by' => $createdBy,
            'received_by' => $receivedBy,
            'completed_at' => null,
        ];
    }

    private function randomLocationId(?int $exceptId = null): int
    {
        $query = Location::query();
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $id = $query->inRandomOrder()->value('id');
        if (!$id) {
            throw new RuntimeException('No locations exist. Seed locations before seeding permits.');
        }

        return (int) $id;
    }

    private function randomUserId(int $userType): int
    {
        $id = User::query()
            ->where('user_type', $userType)
            ->inRandomOrder()
            ->value('id');
        if (!$id) {
            throw new RuntimeException('No users exist for user_type=' . $userType . '. Seed users before seeding permits.');
        }

        return (int) $id;
    }
}
