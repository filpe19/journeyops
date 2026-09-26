<?php

namespace Tests;

use App\Journey\JourneyLog;
use App\Models\JourneyEvent;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(JourneyLog::class)->truncate();
    }

    /**
     * @return list<string>
     */
    protected function journeyEventNames(): array
    {
        return JourneyEvent::orderBy('id')->pluck('event_name')->all();
    }
}
