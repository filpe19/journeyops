<?php

namespace App\Journey;

use App\Models\JourneySession;
use Illuminate\Support\Carbon;

/**
 * Ends journeys that have been idle longer than the configured window.
 *
 * Journeys that reached the purchase funnel without a completed payment are
 * closed with a `journey_abandoned` event.
 */
class IdleJourneyCloser
{
    public function __construct(private readonly JourneyRecorder $recorder) {}

    /**
     * @return array{closed: int, abandoned: int}
     */
    public function close(int $idleMinutes, ?string $journeyUuid = null): array
    {
        $cutoff = now()->subMinutes($idleMinutes);
        $closed = 0;
        $abandoned = 0;

        $query = JourneySession::query()
            ->whereNull('ended_at')
            ->where('last_activity_at', '<=', $cutoff)
            ->with(['events', 'user']);

        if ($journeyUuid !== null) {
            $query->where('uuid', $journeyUuid);
        }

        $query->each(function (JourneySession $session) use (&$closed, &$abandoned) {
            $names = $session->eventNames();
            $lastEvent = $session->events->last();
            $endedAt = Carbon::parse($session->last_activity_at);

            $enteredFunnel = in_array('buy_clicked', $names, true) || in_array('checkout_started', $names, true);

            if ($enteredFunnel && ! in_array('payment_completed', $names, true)) {
                $this->recorder->write(
                    $session,
                    'journey_abandoned',
                    $session->user,
                    $lastEvent?->route,
                    [
                        'last_event' => $lastEvent?->event_name,
                        'idle_minutes' => (int) $endedAt->diffInMinutes(now()),
                    ],
                    $endedAt,
                );
                $abandoned++;
            }

            $session->forceFill(['ended_at' => $endedAt])->save();
            $closed++;
        });

        return ['closed' => $closed, 'abandoned' => $abandoned];
    }
}
