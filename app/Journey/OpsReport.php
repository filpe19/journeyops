<?php

namespace App\Journey;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\JourneySession;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Read model shared by the /ops page and the ops:* commands.
 */
class OpsReport
{
    /**
     * @return array<string, mixed>
     */
    public function summary(int $days = 1): array
    {
        $since = now()->subDays($days);

        $journeys = JourneySession::with('events')->where('started_at', '>=', $since)->get();
        $outcomes = $journeys->groupBy(fn (JourneySession $j) => $j->outcome())->map->count();

        return [
            'window_days' => $days,
            'orders_today' => Order::where('status', OrderStatus::Paid)->where('paid_at', '>=', now()->startOfDay())->count(),
            'orders_in_window' => Order::where('status', OrderStatus::Paid)->where('paid_at', '>=', $since)->count(),
            'revenue_in_window' => (int) Order::where('status', OrderStatus::Paid)->where('paid_at', '>=', $since)->sum('total'),
            'orders_total' => Order::count(),
            'journeys_in_window' => $journeys->count(),
            'journeys_completed' => $outcomes[JourneySession::OUTCOME_COMPLETED] ?? 0,
            'journeys_abandoned' => $outcomes[JourneySession::OUTCOME_ABANDONED] ?? 0,
            'journeys_no_checkout' => $outcomes[JourneySession::OUTCOME_NO_CHECKOUT] ?? 0,
            'journeys_active' => $outcomes[JourneySession::OUTCOME_ACTIVE] ?? 0,
            'new_buyers' => User::where('role', UserRole::Buyer)->where('created_at', '>=', $since)->count(),
            'new_producers' => User::where('role', UserRole::Producer)->where('created_at', '>=', $since)->count(),
            'journeys_by_source' => $journeys->groupBy('source')->map->count()->sortDesc()->all(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function journeys(int $limit = 25, ?string $source = null): Collection
    {
        return JourneySession::query()
            ->with(['events', 'user', 'event'])
            ->when($source, fn ($q) => $q->where('source', $source))
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (JourneySession $j) => $this->row($j));
    }

    /**
     * @return array<string, mixed>
     */
    public function row(JourneySession $journey): array
    {
        $last = $journey->events->last();

        return [
            'uuid' => $journey->uuid,
            'code' => $journey->code(),
            'source' => $journey->source,
            'event' => $journey->event?->slug,
            'user' => $journey->user?->email,
            'role' => $journey->user?->role?->value,
            'started_at' => $journey->started_at,
            'last_event' => $last?->event_name,
            'last_route' => $last?->route,
            'events_count' => $journey->events->count(),
            'outcome' => $journey->outcome(),
            'sequence' => $this->sequence($journey),
        ];
    }

    public function sequence(JourneySession $journey): string
    {
        return $journey->events->pluck('event_name')->implode(' > ');
    }

    public function find(string $identifier): ?JourneySession
    {
        $identifier = trim($identifier);

        if (str_starts_with(strtoupper($identifier), 'JRN-')) {
            $prefix = strtolower(substr($identifier, 4));
            $candidates = JourneySession::whereRaw("replace(uuid, '-', '') like ?", [$prefix.'%'])->limit(2)->get();

            return $candidates->count() === 1 ? $candidates->first() : null;
        }

        return JourneySession::where('uuid', $identifier)->first();
    }

    /**
     * @return Collection<int, Order>
     */
    public function recentOrders(int $limit = 10): Collection
    {
        return Order::with(['event', 'user'])->latest()->limit($limit)->get();
    }
}
