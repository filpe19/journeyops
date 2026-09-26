<?php

namespace Tests\Unit;

use App\Models\JourneyEvent;
use App\Models\JourneySession;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class JourneySessionOutcomeTest extends TestCase
{
    /**
     * @return array<string, array{list<string>, bool, string}>
     */
    public static function journeys(): array
    {
        return [
            'paid' => [['event_page_viewed', 'buy_clicked', 'checkout_started', 'order_created', 'payment_started', 'payment_completed'], true, 'completed'],
            'still browsing' => [['event_page_viewed'], false, 'active'],
            'left at checkout' => [['event_page_viewed', 'buy_clicked', 'checkout_started', 'journey_abandoned'], true, 'abandoned'],
            'only looked' => [['event_page_viewed'], true, 'no_checkout'],
        ];
    }

    /**
     * @param  list<string>  $names
     */
    #[DataProvider('journeys')]
    public function test_outcome_is_derived_from_events(array $names, bool $ended, string $expected): void
    {
        $journey = new JourneySession(['ended_at' => $ended ? Carbon::now() : null]);
        $journey->setRelation('events', collect($names)->map(fn ($n) => new JourneyEvent(['event_name' => $n])));

        $this->assertSame($expected, $journey->outcome());
    }
}
