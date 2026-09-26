<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['uuid', 'anonymous_actor_id', 'source', 'entry_route', 'event_id', 'user_id', 'started_at', 'last_activity_at', 'ended_at'])]
class JourneySession extends Model
{
    public const OUTCOME_COMPLETED = 'completed';

    public const OUTCOME_ABANDONED = 'abandoned';

    public const OUTCOME_NO_CHECKOUT = 'no_checkout';

    public const OUTCOME_ACTIVE = 'active';

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(JourneyEvent::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Short, human friendly identifier used by the ops tooling.
     */
    public function code(): string
    {
        return 'JRN-'.strtoupper(substr(str_replace('-', '', $this->uuid), 0, 8));
    }

    /**
     * @return list<string>
     */
    public function eventNames(): array
    {
        return $this->events->pluck('event_name')->all();
    }

    public function outcome(): string
    {
        $names = $this->eventNames();

        if (in_array('payment_completed', $names, true)) {
            return self::OUTCOME_COMPLETED;
        }

        if ($this->ended_at === null) {
            return self::OUTCOME_ACTIVE;
        }

        if (in_array('checkout_started', $names, true) || in_array('buy_clicked', $names, true)) {
            return self::OUTCOME_ABANDONED;
        }

        return self::OUTCOME_NO_CHECKOUT;
    }
}
