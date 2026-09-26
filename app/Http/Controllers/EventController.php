<?php

namespace App\Http\Controllers;

use App\Journey\JourneyTracker;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(JourneyTracker $journey): View
    {
        $journey->record('landing_viewed', ['page' => 'home']);

        return view('events.index', [
            'events' => Event::published()->with('producer.producerProfile')->orderBy('starts_at')->get(),
        ]);
    }

    public function show(Event $event, JourneyTracker $journey): View
    {
        abort_unless($event->isPublished(), 404);

        $journey->record('event_page_viewed', [
            'event_id' => $event->id,
            'ref' => request()->query('ref'),
        ], $event);

        return view('events.show', [
            'event' => $event->load('producer.producerProfile'),
        ]);
    }
}
