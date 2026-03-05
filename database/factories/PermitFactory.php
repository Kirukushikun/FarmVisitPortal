<?php

namespace Database\Factories;

use App\Models\Area;
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

        $dateOfVisit = $this->faker->dateTimeBetween(now()->subMonths(1)->startOfDay(), now()->addMonths(3)->endOfDay());

        $farmId = $this->randomLocationId();
        $areaId = $this->randomAreaId($farmId);
        $previousFarmId = $this->faker->boolean(40) ? $this->randomLocationId(exceptId: $farmId) : null;

        $createdBy = $this->randomUserId(1);
        $receivedBy = ($status === 2) ? $this->randomUserId(0) : null;

        $previousFarmDate = null;
        if ($previousFarmId !== null) {
            $previousFarmDate = $this->faker->dateTimeBetween('-6 months', 'yesterday');
        }

        return [
            'permit_id' => null,
            'area_id' => $areaId,
            'farm_location_id' => $farmId,
            'names' => $this->faker->name() . "\n" . $this->faker->name(),
            'date_of_visit' => $dateOfVisit,
            'expected_duration_hours' => $this->faker->numberBetween(1, 6),
            'previous_farm_location_id' => $previousFarmId,
            'date_of_visit_previous_farm' => $previousFarmDate,
            'purpose' => $this->faker->boolean(70) ? $this->faker->sentence(8) : null,
            'status' => $status,
            'created_by' => $createdBy,
            'received_by' => $receivedBy,
            'completed_at' => null,
        ];
    }

    private function randomAreaId(int $locationId): int
    {
        $id = Area::query()
            ->where('location_id', $locationId)
            ->where('is_disabled', false)
            ->inRandomOrder()
            ->value('id');
        
        if (!$id) {
            // Create a random area for this location if none exist
            $area = Area::create([
                'location_id' => $locationId,
                'name' => ucfirst($this->faker->words(2, true)),
                'is_disabled' => false,
            ]);
            return $area->id;
        }

        return (int) $id;
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
