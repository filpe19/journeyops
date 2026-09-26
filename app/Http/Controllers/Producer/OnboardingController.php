<?php

namespace App\Http\Controllers\Producer;

use App\Http\Controllers\Controller;
use App\Journey\JourneyTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(Request $request, JourneyTracker $journey): View|RedirectResponse
    {
        if ($request->user()->hasCompletedProducerOnboarding()) {
            return redirect()->route('producer.dashboard');
        }

        $journey->record('producer_onboarding_viewed', [
            'has_producer_profile' => false,
        ]);

        return view('producer.onboarding', ['user' => $request->user()]);
    }

    public function store(Request $request, JourneyTracker $journey): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
        ]);

        $request->user()->producerProfile()->firstOrCreate([], [
            'display_name' => $validated['display_name'],
        ]);

        $journey->record('producer_onboarding_completed', [
            'target_route' => '/producer',
        ]);

        return redirect()->route('producer.dashboard')->with('status', 'Your organizer profile is ready.');
    }
}
