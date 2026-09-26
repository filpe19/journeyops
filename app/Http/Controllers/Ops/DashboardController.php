<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use App\Journey\OpsReport;
use App\Models\JourneySession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, OpsReport $report): View
    {
        $source = $request->query('source');

        return view('ops.dashboard', [
            'summary' => $report->summary(7),
            'orders' => $report->recentOrders(),
            'journeys' => $report->journeys(40, is_string($source) && $source !== '' ? $source : null),
            'source' => $source,
        ]);
    }

    public function show(string $uuid, OpsReport $report): View
    {
        $journey = JourneySession::where('uuid', $uuid)->with(['events.user', 'user.producerProfile', 'event'])->firstOrFail();

        return view('ops.journey', [
            'journey' => $journey,
            'row' => $report->row($journey),
        ]);
    }
}
