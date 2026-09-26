<?php

namespace App\Journey;

use App\Models\JourneyEvent;
use App\Models\JourneySession;
use Illuminate\Support\Facades\File;

/**
 * Mirrors journey events to a JSON Lines file (one JSON object per line).
 */
class JourneyLog
{
    public function __construct(private readonly string $path) {}

    public function path(): string
    {
        return $this->path;
    }

    public function append(JourneySession $session, JourneyEvent $event): void
    {
        $line = [
            'timestamp' => $event->occurred_at->toIso8601String(),
            'journey_id' => $session->uuid,
            'journey_code' => $session->code(),
            'source' => $session->source,
            'event' => $event->event_name,
            'route' => $event->route,
            'user_id' => $event->user_id,
            'user_role' => $event->metadata['user_role'] ?? null,
            'metadata' => $event->metadata ?? (object) [],
        ];

        File::ensureDirectoryExists(dirname($this->path));
        File::append($this->path, json_encode($line, JSON_UNESCAPED_SLASHES).PHP_EOL, true);
    }

    public function truncate(): void
    {
        File::ensureDirectoryExists(dirname($this->path));
        File::put($this->path, '');
    }
}
