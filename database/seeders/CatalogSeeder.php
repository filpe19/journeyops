<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Accounts and events that exist before any visitor traffic.
 * Every identity is synthetic and uses the reserved example.test domain.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $longAgo = now()->subDays(45);

        $this->user('Ops Admin', 'admin@example.test', UserRole::Admin, $longAgo);

        $nova = $this->user('Nova Stage', 'producer@example.test', UserRole::Producer, $longAgo);
        $nova->producerProfile()->create(['display_name' => 'NovaStage Events']);

        foreach (['one', 'two', 'four', 'five'] as $n) {
            $this->user('Buyer '.ucfirst($n), "buyer.{$n}@example.test", UserRole::Buyer, $longAgo);
        }

        $nova->events()->create([
            'title' => 'AI Builders Night 2026',
            'slug' => 'ai-builders-night-2026',
            'description' => "An evening of live demos from teams shipping AI products.\n\nLightning talks, a hands-on agent lab and plenty of time to meet other builders. Doors open at 18:30.",
            'venue' => 'Pier 9 Hall, Harbor District',
            'price' => 4900,
            'capacity' => 400,
            'starts_at' => '2026-11-14 19:00:00',
            'status' => EventStatus::Published,
        ]);

        $nova->events()->create([
            'title' => 'Edge Data Summit',
            'slug' => 'edge-data-summit',
            'description' => 'A one-day summit about streaming, observability and data at the edge.',
            'venue' => 'Riverside Convention Center',
            'price' => 12000,
            'capacity' => 250,
            'starts_at' => '2026-12-03 09:00:00',
            'status' => EventStatus::Published,
        ]);

        $nova->events()->create([
            'title' => 'Winter Synth Sessions',
            'slug' => 'winter-synth-sessions',
            'description' => 'Modular synth night. Line-up to be announced.',
            'venue' => 'The Warehouse',
            'price' => 2500,
            'capacity' => 150,
            'starts_at' => '2027-01-22 21:00:00',
            'status' => EventStatus::Draft,
        ]);
    }

    private function user(string $name, string $email, UserRole $role, $createdAt): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'role' => $role,
        ]);

        $user->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt, 'email_verified_at' => $createdAt])->save();

        return $user;
    }
}
