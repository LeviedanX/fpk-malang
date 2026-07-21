<?php

namespace Database\Factories;

use App\Models\ManagementPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManagementPeriod>
 */
class ManagementPeriodFactory extends Factory
{
    public function definition(): array
    {
        $startYear = fake()->numberBetween((int) now()->year + 1, (int) now()->year + 5);

        return [
            'name' => "Periode {$startYear}-".($startYear + 2),
            'start_year' => $startYear,
            'end_year' => $startYear + 2,
            'group_photo_path' => null,
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }
}
