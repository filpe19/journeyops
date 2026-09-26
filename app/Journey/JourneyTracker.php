<?php

namespace App\Journey;

use App\Models\Event;
use App\Models\JourneyEvent;
use App\Models\JourneySession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Request-scoped entry point for journey telemetry.
 *
 * A journey is a logical browsing session. Its identifier lives in the HTTP
 * session so that it survives authentication (the session is regenerated but
 * its data is kept).
 */
class JourneyTracker
{
    public const SESSION_JOURNEY = 'journey.uuid';

    public const SESSION_ACTOR = 'journey.actor';

    public const SESSION_LAST_ROUTE = 'journey.last_route';

    private ?JourneySession $current = null;

    private ?int $currentRequestId = null;

    public function __construct(private readonly JourneyRecorder $recorder) {}

    /**
     * Record a journey event for the current request, starting a journey when needed.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function record(string $name, array $metadata = [], ?Event $event = null): JourneyEvent
    {
        $session = $this->resolve($event);
        $user = $this->request()->user();

        if ($user !== null && $session->user_id !== $user->id) {
            $session->user_id = $user->id;
        }

        if ($event !== null && $session->event_id === null) {
            $session->event_id = $event->id;
        }

        $session->save();

        $route = '/'.ltrim($this->request()->path(), '/');

        $recorded = $this->recorder->write(
            $session,
            $name,
            $user,
            $route,
            array_merge([
                'event_slug' => $event?->slug,
                'previous_route' => $this->request()->session()->get(self::SESSION_LAST_ROUTE),
                'method' => $this->request()->method(),
            ], $metadata),
        );

        $this->request()->session()->put(self::SESSION_LAST_ROUTE, $route);

        return $recorded;
    }

    /**
     * Mark the current journey as finished (e.g. after a successful purchase).
     */
    public function end(): void
    {
        $session = $this->current();

        if ($session !== null && $session->ended_at === null) {
            $session->forceFill(['ended_at' => now()])->save();
            $this->request()->attributes->set('journey.ended_uuid', $session->uuid);
        }

        $this->request()->session()->forget([self::SESSION_JOURNEY, self::SESSION_LAST_ROUTE]);
        $this->current = null;
    }

    public function current(): ?JourneySession
    {
        if ($this->currentRequestId !== spl_object_id($this->request())) {
            $this->current = null;
            $this->currentRequestId = spl_object_id($this->request());
        }

        if ($this->current !== null) {
            return $this->current;
        }

        $uuid = $this->request()->session()->get(self::SESSION_JOURNEY);

        if ($uuid === null) {
            return null;
        }

        return $this->current = JourneySession::where('uuid', $uuid)->first();
    }

    public function sourceForRequest(): string
    {
        $ref = $this->request()->query('ref');
        $sources = config('journey.ref_sources', []);

        if (is_string($ref) && isset($sources[$ref])) {
            return $sources[$ref];
        }

        return match (true) {
            $this->request()->routeIs('home') => 'homepage',
            $this->request()->routeIs('sell') => 'producer_landing',
            default => 'direct',
        };
    }

    private function request(): Request
    {
        return request();
    }

    private function resolve(?Event $event): JourneySession
    {
        $session = $this->current();

        if ($session !== null && ! $this->shouldStartNew($session)) {
            return $session;
        }

        return $this->start($event);
    }

    private function shouldStartNew(JourneySession $session): bool
    {
        if ($session->ended_at !== null) {
            return true;
        }

        if ($session->last_activity_at->lt(now()->subMinutes(config('journey.idle_minutes')))) {
            return true;
        }

        // A tracked link (?ref=...) opened again starts a fresh attribution window.
        $ref = $this->request()->query('ref');

        return is_string($ref)
            && isset(config('journey.ref_sources', [])[$ref])
            && $this->sourceForRequest() !== $session->source;
    }

    private function start(?Event $event): JourneySession
    {
        $store = $this->request()->session();

        if (! $store->has(self::SESSION_ACTOR)) {
            $store->put(self::SESSION_ACTOR, (string) Str::uuid());
        }

        $session = JourneySession::create([
            'uuid' => (string) Str::uuid(),
            'anonymous_actor_id' => $store->get(self::SESSION_ACTOR),
            'source' => $this->sourceForRequest(),
            'entry_route' => '/'.ltrim($this->request()->path(), '/'),
            'event_id' => $event?->id,
            'user_id' => $this->request()->user()?->id,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        $store->put(self::SESSION_JOURNEY, $session->uuid);
        $store->forget(self::SESSION_LAST_ROUTE);

        return $this->current = $session;
    }
}
