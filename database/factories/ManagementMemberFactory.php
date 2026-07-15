<?php

namespace Database\Factories;

use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManagementMember>
 */
class ManagementMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'management_period_id' => ManagementPeriod::factory(),
            'name' => fake()->name(),
            'position' => fake()->randomElement(['Ketua', 'Sekretaris', 'Anggota']),
            'division' => fake()->randomElement([null, 'Bidang Dialog dan Advokasi', 'Bidang Sosialisasi dan Edukasi']),
            'portrait_path' => null,
            'display_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
