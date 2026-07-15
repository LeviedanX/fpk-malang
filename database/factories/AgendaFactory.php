<?php

namespace Database\Factories;

use App\Enums\AgendaStatus;
use App\Enums\PublicationStatus;
use App\Models\Agenda;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Agenda>
 */
class AgendaFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(5), '.');
        $startsAt = fake()->dateTimeBetween('now', '+2 months');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraph(4),
            'poster_path' => null,
            'location' => fake()->city(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+3 hours'),
            'event_status' => AgendaStatus::Scheduled,
            'publication_status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'publication_status' => PublicationStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function past(): static
    {
        return $this->state(function () {
            $startsAt = fake()->dateTimeBetween('-2 months', '-1 day');

            return [
                'starts_at' => $startsAt,
                'ends_at' => (clone $startsAt)->modify('+3 hours'),
                'event_status' => AgendaStatus::Completed,
            ];
        });
    }
}
