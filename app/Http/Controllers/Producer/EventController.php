<?php

namespace App\Http\Controllers\Producer;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function create(): View
    {
        return view('producer.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'venue' => ['nullable', 'string', 'max:160'],
            'price' => ['required', 'numeric', 'min:0', 'max:10000'],
            'starts_at' => ['required', 'date', 'after:now'],
        ]);

        $slug = Str::slug($validated['title']);
        if (Event::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        $request->user()->events()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'venue' => $validated['venue'] ?? null,
            'price' => (int) round($validated['price'] * 100),
            'starts_at' => $validated['starts_at'],
            'status' => EventStatus::Draft,
        ]);

        return redirect()->route('producer.dashboard')->with('status', 'Event created as draft.');
    }

    public function publish(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->producer_id === $request->user()->id, 403);

        $event->update(['status' => EventStatus::Published]);

        return redirect()->route('producer.dashboard')->with('status', 'Event published. Share link is live.');
    }
}
