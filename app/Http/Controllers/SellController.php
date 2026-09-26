<?php

namespace App\Http\Controllers;

use App\Journey\JourneyTracker;
use Illuminate\View\View;

class SellController extends Controller
{
    public function __invoke(JourneyTracker $journey): View
    {
        $journey->record('landing_viewed', ['page' => 'sell']);

        return view('sell');
    }
}
