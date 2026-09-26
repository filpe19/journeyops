<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'producer_id' => User::factory()->producer('Factory Productions'),
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'venue' => 'Test Hall',
            'price' => 4900,
            'capacity' => 100,
            'starts_at' => now()->addMonth(),
            'status' => EventStatus::Published,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Draft]);
    }
}
