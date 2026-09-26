<?php

namespace App\Journey;

use App\Models\JourneyEvent;
use App\Models\JourneySession;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Persists journey events (database + JSONL mirror).
 */
class JourneyRecorder
{
    public function __construct(private readonly JourneyLog $log) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function write(
        JourneySession $session,
        string $name,
        ?User $user = null,
        ?string $route = null,
        array $metadata = [],
        ?Carbon $at = null,
    ): JourneyEvent {
        $at ??= now();

        $metadata = array_merge([
            'journey_uuid' => $session->uuid,
            'source' => $session->source,
            'user_role' => $user?->role?->value ?? 'guest',
        ], $this->sanitize($metadata));

        $event = $session->events()->create([
            'user_id' => $user?->id,
            'event_name' => $name,
            'route' => $route,
            'metadata' => $metadata,
            'occurred_at' => $at,
        ]);

        $session->forceFill(['last_activity_at' => $at])->save();

        $this->log->append($session, $event);

        return $event;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function sanitize(array $metadata): array
    {
        $metadata = Arr::except($metadata, config('journey.redacted_keys', []));

        return array_filter($metadata, fn ($value) => $value !== null);
    }
}
